<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Auth;
use App\Core\Response;
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
}