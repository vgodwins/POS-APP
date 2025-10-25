<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Auth;
use App\Core\Response;
use App\Models\Sale;

class DashboardController {
    public function index(Request $req): void {
        if (!\App\Core\Auth::check()) { header('Location: /'); exit; }
        $sale = new Sale();
        $metrics = $sale->dashboardMetrics(Auth::user()['store_id'] ?? null);
        view('dashboard/index', ['metrics' => $metrics]);
    }
}