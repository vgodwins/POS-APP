<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Auth;
use App\Core\Response;
use App\Models\Sale;
use App\Core\DB;
use App\Models\Store;

class DashboardController {
    public function index(Request $req): void {
        if (!\App\Core\Auth::check()) { header('Location: /'); exit; }
        $sale = new Sale();
        $metrics = $sale->dashboardMetrics(Auth::effectiveStoreId() ?? null);
        // Recent user activity (last login times)
        $recentUsers = [];
        $stores = [];
        $selectedStoreId = Auth::effectiveStoreId();
        if (Auth::hasRole('admin')) {
            try {
                $pdo = DB::conn();
                $st = $pdo->query('SELECT name, email, last_login_at FROM users ORDER BY (last_login_at IS NULL), last_login_at DESC LIMIT 10');
                $recentUsers = $st->fetchAll() ?: [];
                $stores = (new Store())->all();
            } catch (\Throwable $e) { $recentUsers = []; }
        }
        view('dashboard/index', ['metrics' => $metrics, 'recentUsers' => $recentUsers, 'stores' => $stores, 'selectedStoreId' => $selectedStoreId]);
    }
    public function switchStore(Request $req): void {
        if (!Auth::check() || !Auth::hasRole('admin')) { Response::redirect('/'); }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { Response::redirect('/dashboard'); return; }
        $sid = ($req->body['store_id'] ?? '') !== '' ? (int)$req->body['store_id'] : null;
        if ($sid && $sid > 0) {
            $_SESSION['admin_view_store_id'] = $sid;
        } else {
            unset($_SESSION['admin_view_store_id']);
        }
        Response::redirect('/dashboard');
    }
}
