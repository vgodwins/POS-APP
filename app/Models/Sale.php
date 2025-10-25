<?php
namespace App\Models;

class Sale extends BaseModel {
    public function dashboardMetrics(?int $storeId): array {
        if ($storeId) {
            $st = $this->db->prepare('SELECT COUNT(*) as sales_count, COALESCE(SUM(total_amount),0) as total_amount FROM sales WHERE store_id = ? AND DATE(created_at) = CURDATE()');
            $st->execute([$storeId]);
        } else {
            $st = $this->db->query('SELECT COUNT(*) as sales_count, COALESCE(SUM(total_amount),0) as total_amount FROM sales WHERE DATE(created_at) = CURDATE()');
        }
        $row = $st->fetch() ?: ['sales_count' => 0, 'total_amount' => 0];
        return $row;
    }
}