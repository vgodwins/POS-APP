<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\Voucher;
use App\Core\Config;
use App\Models\Store;

class VoucherController {
    public function index(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $storeId = Auth::user()['store_id'] ?? null;
        $v = new Voucher();
        $list = $v->all($storeId);
        view('vouchers/index', ['vouchers' => $list]);
    }
    public function create(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        view('vouchers/create');
    }
    public function save(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { view('vouchers/create', ['error' => 'Invalid session']); return; }
        $value = (float)($req->body['value'] ?? 0);
        $expiry = trim($req->body['expiry_date'] ?? '');
        $storeId = Auth::user()['store_id'] ?? null;
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
        ]);
        Response::redirect('/vouchers');
    }

    public function edit(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/vouchers'); return; }
        $storeId = Auth::user()['store_id'] ?? null;
        $v = new Voucher();
        $voucher = $v->find($id);
        if (!$voucher || ($storeId && (int)$voucher['store_id'] !== (int)$storeId)) { Response::redirect('/vouchers'); return; }
        view('vouchers/edit', ['voucher' => $voucher]);
    }

    public function update(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { Response::redirect('/vouchers'); return; }
        $id = (int)($req->body['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/vouchers'); return; }
        $storeId = Auth::user()['store_id'] ?? null;
        $v = new Voucher();
        $voucher = $v->find($id);
        if (!$voucher || ($storeId && (int)$voucher['store_id'] !== (int)$storeId)) { Response::redirect('/vouchers'); return; }
        $value = isset($req->body['value']) ? (float)$req->body['value'] : (float)$voucher['value'];
        $expiry = trim($req->body['expiry_date'] ?? $voucher['expiry_date']);
        $status = trim($req->body['status'] ?? $voucher['status']);
        $currencyCode = trim($req->body['currency_code'] ?? $voucher['currency_code']);
        try {
            $v->update($id, [
                'value' => $value,
                'expiry_date' => $expiry,
                'status' => $status,
                'currency_code' => $currencyCode,
            ]);
        } catch (\Throwable $e) { /* swallow */ }
        Response::redirect('/vouchers');
    }

    public function bulk(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        view('vouchers/bulk');
    }

    public function bulkSave(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { view('vouchers/bulk', ['error' => 'Invalid session']); return; }
        $storeId = Auth::user()['store_id'] ?? null;
        $store = $storeId ? (new Store())->find((int)$storeId) : null;
        $currencyCode = $store['currency_code'] ?? (Config::get('defaults')['currency_code'] ?? 'NGN');
        $count = max(1, (int)($req->body['count'] ?? 1));
        $value = (float)($req->body['value'] ?? 0);
        $expiry = trim($req->body['expiry_date'] ?? '');
        $prefix = trim($req->body['prefix'] ?? '');
        $v = new Voucher();
        try {
            $codes = $v->bulkCreate((int)$storeId, $value, $currencyCode, $expiry, $count, $prefix);
            view('vouchers/bulk', ['success' => 'Generated ' . count($codes) . ' vouchers', 'codes' => $codes]);
            return;
        } catch (\Throwable $e) {
            view('vouchers/bulk', ['error' => 'Failed to generate vouchers']);
            return;
        }
    }

    public function validate(Request $req): void {
        if (!Auth::check()) { Response::json(['ok' => false, 'error' => 'unauthorized'], 401); }
        $code = trim($req->query['code'] ?? '');
        if ($code === '') { Response::json(['ok' => false, 'error' => 'missing_code'], 400); }
        $storeId = Auth::user()['store_id'] ?? null;
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
