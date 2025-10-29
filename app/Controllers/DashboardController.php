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

    public function pauseStore(Request $req): void {
        if (!Auth::check() || !Auth::hasRole('admin')) { Response::redirect('/'); }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { Response::redirect('/dashboard'); return; }
        $sid = (int)($req->body['store_id'] ?? 0);
        if ($sid <= 0) { Response::redirect('/dashboard'); return; }
        try {
            $pdo = DB::conn();
            $st = $pdo->prepare('UPDATE stores SET locked = 1 WHERE id = ?');
            $st->execute([$sid]);
            \flash('Shop paused', 'success');
        } catch (\Throwable $e) { \flash('Failed to pause shop', 'error'); }
        Response::redirect('/dashboard');
    }

    public function resumeStore(Request $req): void {
        if (!Auth::check() || !Auth::hasRole('admin')) { Response::redirect('/'); }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { Response::redirect('/dashboard'); return; }
        $sid = (int)($req->body['store_id'] ?? 0);
        if ($sid <= 0) { Response::redirect('/dashboard'); return; }
        try {
            $pdo = DB::conn();
            $st = $pdo->prepare('UPDATE stores SET locked = 0 WHERE id = ?');
            $st->execute([$sid]);
            \flash('Shop resumed', 'success');
        } catch (\Throwable $e) { \flash('Failed to resume shop', 'error'); }
        Response::redirect('/dashboard');
    }

    public function deleteStore(Request $req): void {
        if (!Auth::check() || !Auth::hasRole('admin')) { Response::redirect('/'); }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { Response::redirect('/dashboard'); return; }
        $sid = (int)($req->body['store_id'] ?? 0);
        if ($sid <= 0) { Response::redirect('/dashboard'); return; }
        try {
            $pdo = DB::conn();
            $st = $pdo->prepare('DELETE FROM stores WHERE id = ?');
            $st->execute([$sid]);
            if (isset($_SESSION['admin_view_store_id']) && (int)$_SESSION['admin_view_store_id'] === $sid) {
                unset($_SESSION['admin_view_store_id']);
            }
            \flash('Shop deleted', 'success');
        } catch (\Throwable $e) { \flash('Failed to delete shop', 'error'); }
        Response::redirect('/dashboard');
    }
}
