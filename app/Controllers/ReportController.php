<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\DB;
use App\Models\Expense;

class ReportController {
    private function ensureOwnerOrAdmin(): void {
        if (!Auth::check() || !(Auth::hasRole('admin') || Auth::hasRole('owner'))) { Response::redirect('/'); }
    }
    private function ensureOwnerAdminOrAccountant(): void {
        if (!Auth::check() || !(Auth::hasRole('admin') || Auth::hasRole('owner') || Auth::hasRole('accountant'))) { Response::redirect('/'); }
    }
    public function sales(Request $req): void {
        $this->ensureOwnerAdminOrAccountant();
        $storeId = Auth::effectiveStoreId() ?? null;
        $pdo = DB::conn();
        // Helper to run aggregate
        $fn = function (string $where, array $params = []) use ($pdo, $storeId) {
            $sql = 'SELECT COALESCE(COUNT(*),0) AS sales_count, COALESCE(SUM(total_amount),0) AS total_amount, COALESCE(SUM(subtotal),0) AS subtotal, COALESCE(SUM(tax_total),0) AS tax_total FROM sales';
            $conds = [];
            if ($storeId) { $conds[] = 'store_id = :sid'; $params['sid'] = $storeId; }
            if ($where !== '') { $conds[] = $where; }
            if ($conds) { $sql .= ' WHERE ' . implode(' AND ', $conds); }
            $st = $pdo->prepare($sql);
            $st->execute($params);
            return $st->fetch() ?: ['sales_count'=>0,'total_amount'=>0,'subtotal'=>0,'tax_total'=>0];
        };
        $today = $fn('DATE(created_at) = CURDATE()');
        $week = $fn('YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)');
        $month = $fn('YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())');
        $year = $fn('YEAR(created_at) = YEAR(CURDATE())');

        // Expenses summary for matching periods
        $exp = new Expense();
        $expSum = $exp->summary($storeId);
        $expenses = [
            'today' => (float)($expSum['today'] ?? 0),
            'week' => (float)($expSum['week'] ?? 0),
            'month' => (float)($expSum['month'] ?? 0),
            'year' => (float)($expSum['year'] ?? 0),
        ];
        // Gross income = revenue - expenses
        $gross = [
            'today' => (float)$today['total_amount'] - $expenses['today'],
            'week' => (float)$week['total_amount'] - $expenses['week'],
            'month' => (float)$month['total_amount'] - $expenses['month'],
            'year' => (float)$year['total_amount'] - $expenses['year'],
        ];
        view('reports/sales', ['today' => $today, 'week' => $week, 'month' => $month, 'year' => $year, 'expenses' => $expenses, 'gross' => $gross]);
    }

    public function exportCsv(Request $req): void {
        $this->ensureOwnerAdminOrAccountant();
        $storeId = Auth::effectiveStoreId() ?? null;
        $pdo = DB::conn();
        $fn = function (string $where, array $params = []) use ($pdo, $storeId) {
            $sql = 'SELECT COALESCE(COUNT(*),0) AS sales_count, COALESCE(SUM(total_amount),0) AS total_amount, COALESCE(SUM(subtotal),0) AS subtotal, COALESCE(SUM(tax_total),0) AS tax_total FROM sales';
            $conds = [];
            if ($storeId) { $conds[] = 'store_id = :sid'; $params['sid'] = $storeId; }
            if ($where !== '') { $conds[] = $where; }
            if ($conds) { $sql .= ' WHERE ' . implode(' AND ', $conds); }
            $st = $pdo->prepare($sql);
            $st->execute($params);
            return $st->fetch() ?: ['sales_count'=>0,'total_amount'=>0,'subtotal'=>0,'tax_total'=>0];
        };
        $rows = [
            ['Period','Sales','Subtotal','Tax','Revenue'],
            array_merge(['Today'], $this->rowValues($fn('DATE(created_at) = CURDATE()'))),
            array_merge(['This Week'], $this->rowValues($fn('YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)'))),
            array_merge(['This Month'], $this->rowValues($fn('YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())'))),
            array_merge(['This Year'], $this->rowValues($fn('YEAR(created_at) = YEAR(CURDATE())'))),
        ];
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="sales_report.csv"');
        $out = fopen('php://output', 'w');
        foreach ($rows as $r) { fputcsv($out, $r); }
        fclose($out);
        exit;
    }

    private function rowValues(array $row): array {
        return [
            (int)($row['sales_count'] ?? 0),
            number_format((float)($row['subtotal'] ?? 0), 2, '.', ''),
            number_format((float)($row['tax_total'] ?? 0), 2, '.', ''),
            number_format((float)($row['total_amount'] ?? 0), 2, '.', ''),
        ];
    }
    public function filter(Request $req): void {
        $this->ensureOwnerAdminOrAccountant();
        $storeId = Auth::effectiveStoreId() ?? null;
        $from = trim($req->body['from'] ?? $req->query['from'] ?? '');
        $to = trim($req->body['to'] ?? $req->query['to'] ?? '');
        $productId = ($req->body['product_id'] ?? $req->query['product_id'] ?? '') !== '' ? (int)($req->body['product_id'] ?? $req->query['product_id'] ?? 0) : null;
        $categoryId = ($req->body['category_id'] ?? $req->query['category_id'] ?? '') !== '' ? (int)($req->body['category_id'] ?? $req->query['category_id'] ?? 0) : null;
        $voucherOnly = (($req->body['voucher_only'] ?? $req->query['voucher_only'] ?? '') !== '') ? (bool)($req->body['voucher_only'] ?? $req->query['voucher_only']) : false;
        $pdo = DB::conn();
        // Base conditions
        $conds = [];
        $params = [];
        if ($storeId) { $conds[] = 's.store_id = :sid'; $params['sid'] = $storeId; }
        if ($from !== '') { $conds[] = 'DATE(s.created_at) >= :from'; $params['from'] = $from; }
        if ($to !== '') { $conds[] = 'DATE(s.created_at) <= :to'; $params['to'] = $to; }
        $join = '';
        $joinPayments = '';
        if ($productId || $categoryId) {
            $join = 'INNER JOIN sale_items si ON si.sale_id = s.id';
            if ($productId) { $conds[] = 'si.product_id = :pid'; $params['pid'] = $productId; }
            if ($categoryId) {
                $join .= ' LEFT JOIN products p ON p.id = si.product_id';
                $conds[] = 'p.category_id = :cid'; $params['cid'] = $categoryId;
            }
        } else {
            $join = 'LEFT JOIN sale_items si ON si.sale_id = s.id';
        }
        if ($voucherOnly) {
            // Restrict to sales that include a voucher payment
            $joinPayments = ' INNER JOIN payments pm ON pm.sale_id = s.id AND pm.method = "voucher"';
        }
        $where = $conds ? (' WHERE ' . implode(' AND ', $conds)) : '';
        // Revenue and subtotal/tax
        $sqlAgg = 'SELECT COALESCE(SUM(s.total_amount),0) AS revenue, COALESCE(SUM(s.subtotal),0) AS subtotal, COALESCE(SUM(s.tax_total),0) AS tax_total FROM sales s ' . $join . $joinPayments . $where;
        $st = $pdo->prepare($sqlAgg); $st->execute($params);
        $summary = $st->fetch() ?: ['revenue'=>0,'subtotal'=>0,'tax_total'=>0];
        // Profit approx: sum((si.price - p.cost_price) * si.qty)
        $sqlProfit = 'SELECT COALESCE(SUM((si.price - COALESCE(p.cost_price,0)) * si.qty),0) AS profit
                      FROM sales s INNER JOIN sale_items si ON si.sale_id = s.id
                      LEFT JOIN products p ON p.id = si.product_id' . $joinPayments . $where;
        $st2 = $pdo->prepare($sqlProfit); $st2->execute($params);
        $profit = (float)($st2->fetchColumn() ?: 0);
        // Products list for filter UI
        $products = [];
        try {
            $products = $pdo->prepare('SELECT id,name FROM products WHERE store_id = ? ORDER BY name');
            $products->execute([$storeId]);
            $products = $products->fetchAll();
        } catch (\Throwable $e) { $products = []; }
        // Categories list for filter UI
        $categories = [];
        try {
            $catStmt = $pdo->prepare('SELECT id,name FROM categories WHERE store_id = ? ORDER BY name');
            $catStmt->execute([$storeId]);
            $categories = $catStmt->fetchAll() ?: [];
        } catch (\Throwable $e) { $categories = []; }
        view('reports/filter', [
            'summary' => $summary,
            'profit' => $profit,
            'filters' => ['from' => $from, 'to' => $to, 'product_id' => $productId, 'category_id' => $categoryId, 'voucher_only' => $voucherOnly],
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function general(Request $req): void {
        $this->ensureOwnerOrAdmin();
        $pdo = DB::conn();
        $summary = [
            'stores' => 0,
            'products' => 0,
            'vouchers' => 0,
            'customers' => 0,
            'sales_count' => 0,
            'revenue' => 0.0,
            'expenses' => 0.0,
        ];
        try {
            $summary['stores'] = (int)$pdo->query('SELECT COUNT(*) FROM stores')->fetchColumn();
            $summary['products'] = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
            $summary['vouchers'] = (int)$pdo->query('SELECT COUNT(*) FROM vouchers')->fetchColumn();
            $summary['customers'] = (int)$pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
            $row = $pdo->query('SELECT COALESCE(COUNT(*),0) AS cnt, COALESCE(SUM(total_amount),0) AS revenue FROM sales')->fetch();
            $summary['sales_count'] = (int)($row['cnt'] ?? 0);
            $summary['revenue'] = (float)($row['revenue'] ?? 0.0);
            $ex = new Expense();
            $exSum = $ex->summary(null);
            $summary['expenses'] = (float)($exSum['year'] ?? 0.0);
        } catch (\Throwable $e) { /* swallow */ }
        view('reports/general', ['summary' => $summary]);
    }
}
