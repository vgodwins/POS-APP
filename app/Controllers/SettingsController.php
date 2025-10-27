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
            'company_number' => trim($req->body['company_number'] ?? ''),
        ];
        try {
            $pdo = \App\Core\DB::conn();
            $pdo->prepare('UPDATE stores SET name = :name, currency_code = :currency_code, currency_symbol = :currency_symbol, tax_rate = :tax_rate, theme = :theme, address = :address, phone = :phone, logo_url = :logo_url, company_number = :company_number WHERE id = :id')
                ->execute([
                    'name' => $data['name'],
                    'currency_code' => $data['currency_code'],
                    'currency_symbol' => $data['currency_symbol'],
                    'tax_rate' => $data['tax_rate'],
                    'theme' => $data['theme'],
                    'address' => $data['address'],
                    'phone' => $data['phone'],
                    'logo_url' => $data['logo_url'],
                    'company_number' => $data['company_number'],
                    'id' => $sid,
                ]);
            Response::redirect('/settings');
        } catch (\Throwable $e) {
            // Show friendly error with current store values
            $store = (new Store())->find((int)$sid);
            view('settings/index', [
                'store' => $store,
                'error' => 'Could not save settings. Please check database/migrations.',
            ]);
        }
    }

    public function uploadLogo(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { Response::redirect('/settings'); return; }
        $sid = Auth::user()['store_id'] ?? null;
        if (!$sid) { Response::redirect('/settings'); return; }
        if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            Response::redirect('/settings'); return;
        }
        $base = dirname(__DIR__, 2);
        $uploadDir = $base . '/public/uploads/logos';
        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $allowed = ['png','jpg','jpeg','gif','svg'];
        if (!in_array($ext, $allowed, true)) { Response::redirect('/settings'); return; }
        $fname = 'logo_' . (int)$sid . '_' . time() . '.' . $ext;
        $dest = $uploadDir . '/' . $fname;
        if (!@move_uploaded_file($_FILES['logo']['tmp_name'], $dest)) { Response::redirect('/settings'); return; }
        $url = '/uploads/logos/' . $fname;
        try {
            $pdo = \App\Core\DB::conn();
            $pdo->prepare('UPDATE stores SET logo_url = :url WHERE id = :id')->execute(['url' => $url, 'id' => $sid]);
        } catch (\Throwable $e) { /* ignore and proceed */ }
        Response::redirect('/settings');
    }

    public function clearData(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        // Only owner or admin can clear data
        if (!(Auth::hasRole('owner') || Auth::hasRole('admin'))) { Response::redirect('/dashboard'); return; }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { Response::redirect('/settings'); return; }
        $sid = Auth::user()['store_id'] ?? null;
        if (!$sid) { Response::redirect('/settings'); return; }
        // Environment safeguard: disable destructive action in production
        try {
            $appCfg = Config::get('app') ?? [];
            $env = strtolower((string)($appCfg['env'] ?? 'development'));
            if ($env === 'production') {
                $store = (new Store())->find((int)$sid);
                view('settings/index', ['store' => $store, 'error' => 'Clear Data is disabled in production.']);
                return;
            }
        } catch (\Throwable $e) { /* default to allowing when config missing */ }
        $confirmText = trim($req->body['confirm_text'] ?? '');
        if ($confirmText !== 'CLEAR') {
            $store = (new Store())->find((int)$sid);
            view('settings/index', ['store' => $store, 'error' => 'Type CLEAR to confirm data wipe.']);
            return;
        }
        try {
            $pdo = \App\Core\DB::conn();
            // Delete transactional data for this store
            $pdo->prepare('DELETE FROM sales WHERE store_id = ?')->execute([$sid]);
            $pdo->prepare('DELETE FROM expenses WHERE store_id = ?')->execute([$sid]);
            $pdo->prepare('DELETE FROM vouchers WHERE store_id = ?')->execute([$sid]);
            $pdo->prepare('DELETE FROM customers WHERE store_id = ?')->execute([$sid]);
            $store = (new Store())->find((int)$sid);
            view('settings/index', ['store' => $store, 'error' => 'All transactional data cleared for this store.']);
        } catch (\Throwable $e) {
            $store = (new Store())->find((int)$sid);
            view('settings/index', ['store' => $store, 'error' => 'Failed to clear data.']);
        }
    }
}
