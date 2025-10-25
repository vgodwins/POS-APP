<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\Store;
use App\Core\Config;

class SettingsController {
    public function index(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $sid = Auth::user()['store_id'] ?? null;
        if (!$sid) { view('settings/index', ['error' => 'No store associated']); return; }
        $store = (new Store())->find((int)$sid);
        view('settings/index', ['store' => $store]);
    }
    public function save(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { $this->index($req); return; }
        $sid = Auth::user()['store_id'] ?? null;
        if (!$sid) { $this->index($req); return; }
        $data = [
            'name' => trim($req->body['name'] ?? ''),
            'currency_code' => trim($req->body['currency_code'] ?? (Config::get('defaults')['currency_code'] ?? 'NGN')),
            'currency_symbol' => trim($req->body['currency_symbol'] ?? (Config::get('defaults')['currency_symbol'] ?? '₦')),
            'tax_rate' => (float)($req->body['tax_rate'] ?? (Config::get('defaults')['tax_rate'] ?? 0.075)),
            'theme' => trim($req->body['theme'] ?? (Config::get('defaults')['theme'] ?? 'light')),
            'address' => trim($req->body['address'] ?? ''),
            'phone' => trim($req->body['phone'] ?? ''),
            'logo_url' => trim($req->body['logo_url'] ?? ''),
        ];
        // Update stores table directly
        $pdo = \App\Core\DB::conn();
        $pdo->prepare('UPDATE stores SET name = :name, currency_code = :currency_code, currency_symbol = :currency_symbol, tax_rate = :tax_rate, theme = :theme, address = :address, phone = :phone, logo_url = :logo_url WHERE id = :id')
            ->execute([
                'name' => $data['name'],
                'currency_code' => $data['currency_code'],
                'currency_symbol' => $data['currency_symbol'],
                'tax_rate' => $data['tax_rate'],
                'theme' => $data['theme'],
                'address' => $data['address'],
                'phone' => $data['phone'],
                'logo_url' => $data['logo_url'],
                'id' => $sid,
            ]);
        Response::redirect('/settings');
    }
}