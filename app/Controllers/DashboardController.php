<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Auth;
use App\Core\Response;
use App\Models\Sale;
use App\Core\DB;

class DashboardController {
    public function index(Request $req): void {
        if (!\App\Core\Auth::check()) { header('Location: /'); exit; }
        $sale = new Sale();
        $metrics = $sale->dashboardMetrics(Auth::user()['store_id'] ?? null);
        // Recent user activity (last login times)
        $recentUsers = [];
        if (Auth::hasRole('admin')) {
            try {
                $pdo = DB::conn();
                $st = $pdo->query('SELECT name, email, last_login_at FROM users ORDER BY (last_login_at IS NULL), last_login_at DESC LIMIT 10');
                $recentUsers = $st->fetchAll() ?: [];
            } catch (\Throwable $e) { $recentUsers = []; }
        }
        view('dashboard/index', ['metrics' => $metrics, 'recentUsers' => $recentUsers]);
    }
}
