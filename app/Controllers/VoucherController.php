<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\Voucher;
use App\Core\Config;
use App\Models\Store;
use App\Models\Customer;

class VoucherController {
    private function ensureNotCashier(): void {
        if (!Auth::check() || Auth::hasRole('cashier')) { Response::redirect('/'); }
    }
    public function index(Request $req): void {
        $this->ensureNotCashier();
        $storeId = Auth::effectiveStoreId() ?? null;
        $customerId = ($req->query['customer_id'] ?? '') !== '' ? (int)$req->query['customer_id'] : null;
        $linked = isset($req->query['linked']) ? (string)$req->query['linked'] : null; // '1' or '0'
        $sort = isset($req->query['sort']) ? (string)$req->query['sort'] : null; // 'expiry_asc','expiry_desc','value_desc','value_asc'
        $v = new Voucher();
        $list = [];
        try {
            $list = $v->allWithCustomer($storeId, $customerId, ($linked === '1' || $linked === '0') ? $linked : null, $sort);
        } catch (\Throwable $e) {
            // Fallback if migration isn't applied yet
            $list = $v->all($storeId);
            if ($customerId !== null) { $list = array_values(array_filter($list, fn($row) => (int)($row['customer_id'] ?? 0) === $customerId)); }
            if ($linked === '1') { $list = array_values(array_filter($list, fn($row) => ($row['customer_id'] ?? null) !== null)); }
            if ($linked === '0') { $list = array_values(array_filter($list, fn($row) => ($row['customer_id'] ?? null) === null)); }
            if ($sort) {
                if ($sort === 'expiry_asc' || $sort === 'expiry_desc') {
                    usort($list, function($a, $b) use ($sort) {
                        $ea = strtotime($a['expiry_date'] ?? '1970-01-01');
                        $eb = strtotime($b['expiry_date'] ?? '1970-01-01');
                        $cmp = $ea <=> $eb;
                        return $sort === 'expiry_asc' ? $cmp : -$cmp;
                    });
                } elseif ($sort === 'value_desc' || $sort === 'value_asc') {
                    usort($list, function($a, $b) use ($sort) {
                        $va = (float)($a['value'] ?? 0);
                        $vb = (float)($b['value'] ?? 0);
                        $cmp = $va <=> $vb;
                        return $sort === 'value_asc' ? $cmp : -$cmp;
                    });
                }
            }
        }
        $customers = [];
        try { $customers = (new \App\Models\Customer())->allByStore((int)$storeId); } catch (\Throwable $e) { $customers = []; }
        view('vouchers/index', ['vouchers' => $list, 'customers' => $customers, 'selectedCustomerId' => $customerId, 'selectedLinked' => ($linked === '1' || $linked === '0') ? $linked : null, 'selectedSort' => $sort]);
    }
    public function create(Request $req): void {
        $this->ensureNotCashier();
        $storeId = Auth::effectiveStoreId() ?? null;
        $customers = [];
        try { $customers = (new Customer())->allByStore((int)$storeId); } catch (\Throwable $e) { $customers = []; }
        view('vouchers/create', ['customers' => $customers]);
    }
    public function save(Request $req): void {
        $this->ensureNotCashier();
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { view('vouchers/create', ['error' => 'Invalid session']); return; }
        if (Auth::isWriteLocked(Auth::effectiveStoreId())) { view('vouchers/create', ['error' => 'Store is locked or outside active hours']); return; }
        $value = (float)($req->body['value'] ?? 0);
        $expiry = trim($req->body['expiry_date'] ?? '');
        $storeId = Auth::effectiveStoreId() ?? null;
        $store = $storeId ? (new Store())->find((int)$storeId) : null;
        $currencyCode = $store['currency_code'] ?? (Config::get('defaults')['currency_code'] ?? 'NGN');
        $v = new Voucher();
        $code = $v->generateUniqueCode(10);
        $v->create([
            'code' => $code,
            'value' => $value,
            'currency_code' => $currencyCode,
            'expiry_date' => $expiry,
            'store_id' => $storeId,
            'customer_id' => ($req->body['customer_id'] ?? '') !== '' ? (int)$req->body['customer_id'] : null,
        ]);
        Response::redirect('/vouchers');
    }

    public function edit(Request $req): void {
        $this->ensureNotCashier();
        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/vouchers'); return; }
        $storeId = Auth::effectiveStoreId() ?? null;
        $v = new Voucher();
        $voucher = $v->find($id);
        if (!$voucher || ($storeId && (int)$voucher['store_id'] !== (int)$storeId)) { Response::redirect('/vouchers'); return; }
        $customers = [];
        $currentCustomer = null;
        try {
            $customers = (new Customer())->allByStore((int)$storeId);
            if (!empty($voucher['customer_id'])) {
                $currentCustomer = (new Customer())->find((int)$voucher['customer_id']);
            }
        } catch (\Throwable $e) { $customers = []; $currentCustomer = null; }
        view('vouchers/edit', ['voucher' => $voucher, 'customers' => $customers, 'currentCustomer' => $currentCustomer]);
    }

    public function view(Request $req): void {
        $this->ensureNotCashier();
        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/vouchers'); return; }
        $storeId = Auth::effectiveStoreId() ?? null;
        $v = new Voucher();
        $voucher = null;
        try { $voucher = $v->findWithCustomer($id); } catch (\Throwable $e) { $voucher = $v->find($id); }
        if (!$voucher || ($storeId && (int)$voucher['store_id'] !== (int)$storeId)) { Response::redirect('/vouchers'); return; }
        view('vouchers/view', ['voucher' => $voucher]);
    }

    public function update(Request $req): void {
        $this->ensureNotCashier();
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { Response::redirect('/vouchers'); return; }
        if (Auth::isWriteLocked(Auth::effectiveStoreId())) { Response::redirect('/vouchers'); return; }
        $id = (int)($req->body['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/vouchers'); return; }
        $storeId = Auth::effectiveStoreId() ?? null;
        $v = new Voucher();
        $voucher = $v->find($id);
        if (!$voucher || ($storeId && (int)$voucher['store_id'] !== (int)$storeId)) { Response::redirect('/vouchers'); return; }
        $value = isset($req->body['value']) ? (float)$req->body['value'] : (float)$voucher['value'];
        $topUp = ($req->body['top_up_value'] ?? '') !== '' ? (float)$req->body['top_up_value'] : 0.0;
        if ($topUp > 0) { $value = $value + $topUp; }
        $expiry = trim($req->body['expiry_date'] ?? $voucher['expiry_date']);
        $status = trim($req->body['status'] ?? $voucher['status']);
        $currencyCode = trim($req->body['currency_code'] ?? $voucher['currency_code']);
        try {
            $v->update($id, [
                'value' => $value,
                'expiry_date' => $expiry,
                'status' => $status,
                'currency_code' => $currencyCode,
                'customer_id' => ($req->body['customer_id'] ?? '') !== '' ? (int)$req->body['customer_id'] : null,
            ]);
        } catch (\Throwable $e) { /* swallow */ }
        Response::redirect('/vouchers');
    }

    public function bulk(Request $req): void {
        $this->ensureNotCashier();
        view('vouchers/bulk');
    }

    public function bulkSave(Request $req): void {
        $this->ensureNotCashier();
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { view('vouchers/bulk', ['error' => 'Invalid session']); return; }
        if (Auth::isWriteLocked(Auth::effectiveStoreId())) { view('vouchers/bulk', ['error' => 'Store is locked or outside active hours']); return; }
        $storeId = Auth::effectiveStoreId() ?? null;
        $store = $storeId ? (new Store())->find((int)$storeId) : null;
        $currencyCode = $store['currency_code'] ?? (Config::get('defaults')['currency_code'] ?? 'NGN');
        $count = max(1, (int)($req->body['count'] ?? 1));
        $value = (float)($req->body['value'] ?? 0);
        $expiry = trim($req->body['expiry_date'] ?? '');
        $prefix = trim($req->body['prefix'] ?? '');
        $v = new Voucher();
        try {
            $codes = $v->bulkCreate((int)$storeId, $value, $currencyCode, $expiry, $count, $prefix);
            // Store last bulk codes in session to avoid long URLs
            $_SESSION['last_bulk_codes'] = $codes;
            Response::redirect('/vouchers/print_cards');
            return;
        } catch (\Throwable $e) {
            view('vouchers/bulk', ['error' => 'Failed to generate vouchers']);
            return;
        }
    }

    public function printCards(Request $req): void {
        $this->ensureNotCashier();
        $codesParam = trim($req->query['codes'] ?? '');
        $codes = [];
        if ($codesParam !== '') {
            $codes = array_values(array_filter(array_map('trim', explode(',', $codesParam)), fn($c) => $c !== ''));
        } elseif (!empty($_SESSION['last_bulk_codes']) && is_array($_SESSION['last_bulk_codes'])) {
            $codes = $_SESSION['last_bulk_codes'];
        }
        // Accept filters from vouchers list when codes are not explicitly provided
        $customerId = ($req->query['customer_id'] ?? '') !== '' ? (int)$req->query['customer_id'] : null;
        $linked = isset($req->query['linked']) ? (string)$req->query['linked'] : null; // '1' or '0'
        $sort = isset($req->query['sort']) ? (string)$req->query['sort'] : null;
        $search = trim($req->query['q'] ?? '');
        $showAmount = (($req->query['show_amount'] ?? '') !== '') ? (bool)$req->query['show_amount'] : true;
        $storeId = Auth::effectiveStoreId() ?? null;
        $store = $storeId ? (new Store())->find((int)$storeId) : null;
        $cards = [];
        $v = new Voucher();
        if (!empty($codes)) {
            foreach ($codes as $code) {
                try {
                    $row = $v->findByCode($code, $storeId);
                    if ($row) {
                        $customer = null;
                        if (!empty($row['customer_id'])) {
                            try { $customer = (new Customer())->find((int)$row['customer_id']); } catch (\Throwable $e) { $customer = null; }
                        }
                        $cards[] = ['voucher' => $row, 'customer' => $customer];
                    }
                } catch (\Throwable $e) { /* swallow */ }
            }
        } else {
            // Build from filters
            $list = [];
            try {
                $list = $v->allWithCustomer($storeId, $customerId, ($linked === '1' || $linked === '0') ? $linked : null, $sort);
            } catch (\Throwable $e) {
                $list = $v->all($storeId);
                if ($customerId !== null) { $list = array_values(array_filter($list, fn($row) => (int)($row['customer_id'] ?? 0) === $customerId)); }
                if ($linked === '1') { $list = array_values(array_filter($list, fn($row) => ($row['customer_id'] ?? null) !== null)); }
                if ($linked === '0') { $list = array_values(array_filter($list, fn($row) => ($row['customer_id'] ?? null) === null)); }
                if ($sort) {
                    if ($sort === 'expiry_asc' || $sort === 'expiry_desc') {
                        usort($list, function($a, $b) use ($sort) {
                            $ea = strtotime($a['expiry_date'] ?? '1970-01-01');
                            $eb = strtotime($b['expiry_date'] ?? '1970-01-01');
                            $cmp = $ea <=> $eb;
                            return $sort === 'expiry_asc' ? $cmp : -$cmp;
                        });
                    } elseif ($sort === 'value_desc' || $sort === 'value_asc') {
                        usort($list, function($a, $b) use ($sort) {
                            $va = (float)($a['value'] ?? 0);
                            $vb = (float)($b['value'] ?? 0);
                            $cmp = $va <=> $vb;
                            return $sort === 'value_asc' ? $cmp : -$cmp;
                        });
                    }
                }
            }
            if ($search !== '') {
                $q = strtolower($search);
                $list = array_values(array_filter($list, function($row) use ($q) {
                    $txt = strtolower(($row['code'] ?? '') . ' ' . (string)($row['value'] ?? '') . ' ' . ($row['currency_code'] ?? '') . ' ' . ($row['customer_name'] ?? ''));
                    return strpos($txt, $q) !== false;
                }));
            }
            foreach ($list as $row) {
                $customer = null;
                if (!empty($row['customer_id'])) {
                    // Prefer names from join if available
                    if (isset($row['customer_name']) || isset($row['customer_phone']) || isset($row['customer_email'])) {
                        $customer = [
                            'name' => $row['customer_name'] ?? null,
                            'phone' => $row['customer_phone'] ?? null,
                            'email' => $row['customer_email'] ?? null,
                        ];
                    } else {
                        try { $customer = (new Customer())->find((int)$row['customer_id']); } catch (\Throwable $e) { $customer = null; }
                    }
                }
                $cards[] = ['voucher' => $row, 'customer' => $customer];
            }
        }
        view('vouchers/print_cards', ['cards' => $cards, 'store' => $store, 'showAmount' => $showAmount]);
    }

    public function verifyPage(Request $req): void {
        $code = trim($req->query['code'] ?? '');
        $storeId = Auth::check() ? (Auth::effectiveStoreId() ?? null) : null;
        $status = ['ok' => false, 'message' => 'Missing code'];
        $voucher = null;
        if ($code !== '') {
            try {
                $v = new Voucher();
                $voucher = $v->findByCode($code, $storeId);
                if ($voucher) {
                    $valid = strtotime($voucher['expiry_date']) >= strtotime(date('Y-m-d')) && $voucher['status'] === 'active';
                    $status = $valid ? ['ok' => true, 'message' => 'Voucher is valid'] : ['ok' => false, 'message' => 'Voucher is expired or already used'];
                } else {
                    $status = ['ok' => false, 'message' => 'Voucher not found'];
                }
            } catch (\Throwable $e) {
                $status = ['ok' => false, 'message' => 'Server error'];
            }
        }
        view('vouchers/verify', ['status' => $status, 'voucher' => $voucher]);
    }

    public function scan(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); return; }
        view('vouchers/scan');
    }

    public function validate(Request $req): void {
        if (!Auth::check()) { Response::json(['ok' => false, 'error' => 'unauthorized'], 401); }
        $code = trim($req->query['code'] ?? '');
        if ($code === '') { Response::json(['ok' => false, 'error' => 'missing_code'], 400); }
        $storeId = Auth::effectiveStoreId() ?? null;
        try {
            $v = new Voucher();
            $voucher = $v->findByCode($code, $storeId);
            if (!$voucher) { Response::json(['ok' => false, 'error' => 'invalid'], 404); }
            $valid = strtotime($voucher['expiry_date']) >= strtotime(date('Y-m-d')) && $voucher['status'] === 'active';
            if (!$valid) { Response::json(['ok' => false, 'error' => 'expired_or_used'], 400); }
            Response::json(['ok' => true, 'value' => (float)$voucher['value']]);
        } catch (\Throwable $e) {
            Response::json(['ok' => false, 'error' => 'server_error'], 500);
        }
    }
}
