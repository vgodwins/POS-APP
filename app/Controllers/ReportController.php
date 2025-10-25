<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\DB;

class ReportController {
    public function sales(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $storeId = Auth::user()['store_id'] ?? null;
        $pdo = DB::conn();
        // Helper to run aggregate
        $fn = function (string $where, array $params = []) use ($pdo, $storeId) {
            $sql = 'SELECT COALESCE(COUNT(*),0) AS sales_count, COALESCE(SUM(total_amount),0) AS total_amount, COALESCE(SUM(subtotal),0) AS subtotal, COALESCE(SUM(tax_total),0) AS tax_total FROM sales';
            $conds = [];
            if ($storeId) { $conds[] = 'store_id = :sid'; $params['sid'] = $storeId; }
            if ($where !== '') { $conds[] = $where; }
            if ($conds) { $sql .= ' WHERE ' . implode(' AND ', $conds); }
            $st = $pdo->prepare($sql);
            $st->execute($params);
            return $st->fetch() ?: ['sales_count'=>0,'total_amount'=>0,'subtotal'=>0,'tax_total'=>0];
        };
        $today = $fn('DATE(created_at) = CURDATE()');
        $week = $fn('YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)');
        $month = $fn('YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())');
        $year = $fn('YEAR(created_at) = YEAR(CURDATE())');
        view('reports/sales', ['today' => $today, 'week' => $week, 'month' => $month, 'year' => $year]);
    }
}