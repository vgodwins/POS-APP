<?php
namespace App\Models;

class Plan extends BaseModel {
    public function allActive(): array {
        try {
            $st = $this->db->query('SELECT * FROM plans WHERE active = 1 ORDER BY level, period');
            $rows = $st->fetchAll();
            if ($rows) { return $rows; }
        } catch (\Throwable $e) {
            // fall through to defaults
        }
        return $this->defaultPlans();
    }

    public function findByCode(string $code): ?array {
        try {
            $st = $this->db->prepare('SELECT * FROM plans WHERE code = ? AND active = 1 LIMIT 1');
            $st->execute([$code]);
            $row = $st->fetch();
            if ($row) { return $row; }
        } catch (\Throwable $e) { /* ignore */ }
        foreach ($this->defaultPlans() as $p) { if ($p['code'] === $code) return $p; }
        return null;
    }

    private function defaultPlans(): array {
        return [
            ['code' => 'store_monthly_basic', 'name' => 'Store Monthly', 'level' => 'store', 'period' => 'monthly', 'amount' => 3000, 'currency_code' => 'NGN'],
            ['code' => 'store_yearly_basic',  'name' => 'Store Yearly',  'level' => 'store', 'period' => 'yearly',  'amount' => 30000, 'currency_code' => 'NGN'],
            ['code' => 'app_monthly_basic',   'name' => 'App Monthly',   'level' => 'app',   'period' => 'monthly', 'amount' => 5000, 'currency_code' => 'NGN'],
            ['code' => 'app_yearly_basic',    'name' => 'App Yearly',    'level' => 'app',   'period' => 'yearly',  'amount' => 50000, 'currency_code' => 'NGN'],
        ];
    }
}

