<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Auth;
use App\Core\Response;
use App\Core\Config;
use App\Models\Product;
use App\Models\Voucher;
use App\Models\Sale;
use App\Models\Store;

class SaleController {
    public function create(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        if (!(Auth::hasRole('cashier') || Auth::hasRole('owner') || Auth::hasRole('admin'))) { Response::redirect('/dashboard'); }
        $p = new Product();
        $products = $p->all(Auth::user()['store_id'] ?? null);
        // Filter POS products to valid or returned only and exclude zero stock
        $products = array_values(array_filter($products, function ($pr) {
            $status = strtolower($pr['status'] ?? 'valid');
            $stock = (int)($pr['stock'] ?? 0);
            return in_array($status, ['valid','returned'], true) && $stock > 0;
        }));
        $store = null;
        try {
            $sid = Auth::user()['store_id'] ?? null;
            if ($sid) { $store = (new Store())->find((int)$sid); }
        } catch (\Throwable $e) { $store = null; }
        // Determine low-stock list for alert
        $threshold = (int)(Config::get('defaults')['low_stock_threshold'] ?? 5);
        $lowStock = array_values(array_filter($products, function ($pr) use ($threshold) {
            return (int)($pr['stock'] ?? 0) <= $threshold;
        }));
        view('pos/create', ['products' => $products, 'store' => $store, 'lowStock' => $lowStock, 'lowThreshold' => $threshold]);
    }

    public function checkout(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        if (!(Auth::hasRole('cashier') || Auth::hasRole('owner') || Auth::hasRole('admin'))) { Response::redirect('/dashboard'); }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { Response::redirect('/pos'); return; }
        $storeId = Auth::user()['store_id'] ?? null;
        $items = $req->body['items'] ?? [];
        $payments = $req->body['payments'] ?? [];
        $voucherCode = trim($req->body['voucher_code'] ?? '');
        $store = $storeId ? (new Store())->find((int)$storeId) : null;
        $currency = $store['currency_code'] ?? (Config::get('defaults')['currency_code'] ?? 'NGN');
        $taxRateDefault = (float)($store['tax_rate'] ?? (Config::get('defaults')['tax_rate'] ?? 0));

        // Calculate totals
        $subtotal = 0.0; $taxTotal = 0.0; $saleItems = [];
        $pModel = new Product();
        foreach ($items as $it) {
            $productId = (int)($it['product_id'] ?? 0);
            $qty = max(1, (int)($it['qty'] ?? 1));
            if ($productId <= 0) continue;
            // Fetch product
            $dbp = $pModel->all($storeId);
            $find = null;
            foreach ($dbp as $pp) { if ((int)$pp['id'] === $productId) { $find = $pp; break; } }
            if (!$find) continue;
            $price = (float)$find['price'];
            $taxRate = $taxRateDefault;
            $lineSub = $price * $qty;
            $lineTax = $lineSub * $taxRate;
            $subtotal += $lineSub; $taxTotal += $lineTax;
            $saleItems[] = [ 'product_id' => $productId, 'name' => $find['name'], 'qty' => $qty, 'price' => $price, 'tax' => $lineTax ];
        }
        $total = $subtotal + $taxTotal;

        // Voucher redemption
        $voucherApplied = 0.0; $voucherId = null;
        if ($voucherCode !== '') {
            $vModel = new Voucher();
            $voucher = $vModel->findByCode($voucherCode, $storeId);
            if ($voucher) {
                // Check expiry
                if (strtotime($voucher['expiry_date']) >= strtotime(date('Y-m-d'))) {
                    $voucherApplied = min((float)$voucher['value'], $total);
                    $voucherId = (int)$voucher['id'];
                }
            }
        }

        // Payments: auto-fill cash to cover remainder, relax strict equality
        $payCard = (float)($payments['card'] ?? 0);
        $payBank = (float)($payments['bank_transfer'] ?? 0);
        $payVoucher = $voucherApplied;
        $nonCash = $payCard + $payBank + $payVoucher;
        if ($nonCash >= $total) {
            // Clamp non-cash to not exceed total
            $excess = $nonCash - $total;
            if ($excess > 0) {
                // Reduce bank first, then card
                if ($payBank >= $excess) {
                    $payBank -= $excess;
                } else {
                    $excess -= $payBank; $payBank = 0.0;
                    $payCard = max(0.0, $payCard - $excess);
                }
                $nonCash = $payCard + $payBank + $payVoucher;
            }
            $payCash = 0.0;
        } else {
            $payCash = $total - $nonCash;
        }
        $paid = $payCash + $payCard + $payBank + $payVoucher; // should equal $total now

        // Persist sale
        $saleModel = new Sale();
        $saleId = $this->saveSale($storeId, $subtotal, $taxTotal, $total, $currency, $saleItems, [
            'cash' => $payCash, 'card' => $payCard, 'bank_transfer' => $payBank, 'voucher' => $payVoucher, 'voucher_id' => $voucherId
        ]);

        if ($voucherId) { (new Voucher())->markUsed($voucherId); }

        // Redirect to receipt
        $_SESSION['last_sale_id'] = $saleId;
        Response::redirect('/sales/receipt');
    }

    private function saveSale(?int $storeId, float $subtotal, float $tax, float $total, string $currency, array $items, array $payments): int {
        $pdo = \App\Core\DB::conn();
        // Insert sale
        $st = $pdo->prepare('INSERT INTO sales(store_id, subtotal, tax_total, total_amount, currency_code, created_at) VALUES(?,?,?,?,?,NOW())');
        $st->execute([$storeId, $subtotal, $tax, $total, $currency]);
        $saleId = (int)$pdo->lastInsertId();
        // Items
        $sti = $pdo->prepare('INSERT INTO sale_items(sale_id, product_id, name, qty, price, tax) VALUES(?,?,?,?,?,?)');
        foreach ($items as $it) {
            $sti->execute([$saleId, $it['product_id'], $it['name'], $it['qty'], $it['price'], $it['tax']]);
        }
        // Payments
        $stp = $pdo->prepare('INSERT INTO payments(sale_id, method, amount, voucher_id) VALUES(?,?,?,?)');
        foreach (['cash','card','bank_transfer','voucher'] as $m) {
            $amt = (float)($payments[$m] ?? 0);
            if ($amt > 0) {
                $stp->execute([$saleId, $m, $amt, $m === 'voucher' ? ($payments['voucher_id'] ?? null) : null]);
            }
        }
        return $saleId;
    }

    public function receipt(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        if (!(Auth::hasRole('cashier') || Auth::hasRole('owner') || Auth::hasRole('admin'))) { Response::redirect('/dashboard'); }
        $saleId = (int)($_SESSION['last_sale_id'] ?? 0);
        if (!$saleId) { Response::redirect('/dashboard'); return; }
        $pdo = \App\Core\DB::conn();
        $sale = $pdo->prepare('SELECT * FROM sales WHERE id = ?'); $sale->execute([$saleId]); $saleRow = $sale->fetch();
        $items = $pdo->prepare('SELECT * FROM sale_items WHERE sale_id = ?'); $items->execute([$saleId]); $saleItems = $items->fetchAll();
        $payments = $pdo->prepare('SELECT * FROM payments WHERE sale_id = ?'); $payments->execute([$saleId]); $salePayments = $payments->fetchAll();
        $storeRow = null;
        try {
            $sid = (int)($saleRow['store_id'] ?? 0);
            if ($sid > 0) { $storeRow = (new \App\Models\Store())->find($sid); }
        } catch (\Throwable $e) { $storeRow = null; }
        view('sales/receipt', ['sale' => $saleRow, 'items' => $saleItems, 'payments' => $salePayments, 'store' => $storeRow]);
    }

    public function invoice(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        if (!(Auth::hasRole('cashier') || Auth::hasRole('owner') || Auth::hasRole('admin'))) { Response::redirect('/dashboard'); }
        $idParam = (int)($req->query['id'] ?? 0);
        $saleId = $idParam > 0 ? $idParam : (int)($_SESSION['last_sale_id'] ?? 0);
        if ($saleId <= 0) { Response::redirect('/dashboard'); return; }
        $pdo = \App\Core\DB::conn();
        $sale = $pdo->prepare('SELECT * FROM sales WHERE id = ?'); $sale->execute([$saleId]); $saleRow = $sale->fetch();
        if (!$saleRow) { Response::redirect('/dashboard'); return; }
        $items = $pdo->prepare('SELECT * FROM sale_items WHERE sale_id = ?'); $items->execute([$saleId]); $saleItems = $items->fetchAll();
        $payments = $pdo->prepare('SELECT * FROM payments WHERE sale_id = ?'); $payments->execute([$saleId]); $salePayments = $payments->fetchAll();
        $storeRow = null;
        try {
            $sid = (int)($saleRow['store_id'] ?? 0);
            if ($sid > 0) { $storeRow = (new \App\Models\Store())->find($sid); }
        } catch (\Throwable $e) { $storeRow = null; }
        view('sales/invoice', ['sale' => $saleRow, 'items' => $saleItems, 'payments' => $salePayments, 'store' => $storeRow]);
    }
}
