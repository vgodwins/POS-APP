<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\Store;

class StoreController {
    public function index(Request $req): void {
        if (!Auth::check() || !Auth::hasRole('admin')) { Response::redirect('/dashboard'); }
        $rows = (new Store())->all();
        view('stores/index', ['stores' => $rows]);
    }
    public function create(Request $req): void {
        if (!Auth::check() || !Auth::hasRole('admin')) { Response::redirect('/dashboard'); }
        view('stores/create');
    }
    public function save(Request $req): void {
        if (!Auth::check() || !Auth::hasRole('admin')) { Response::redirect('/dashboard'); }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { view('stores/create', ['error' => 'Invalid session']); return; }
        $storeId = (new Store())->create([
            'name' => $req->body['name'] ?? 'Store',
            'currency_code' => $req->body['currency_code'] ?? 'NGN',
            'currency_symbol' => $req->body['currency_symbol'] ?? '₦',
            'tax_rate' => (float)($req->body['tax_rate'] ?? 0.075),
            'theme' => $req->body['theme'] ?? 'light',
        ]);
        Response::redirect('/stores');
    }
}