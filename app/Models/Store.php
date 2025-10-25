<?php
namespace App\Models;

class Store extends BaseModel {
    public function create(array $data): int {
        $st = $this->db->prepare('INSERT INTO stores(name, currency_code, currency_symbol, tax_rate, theme) VALUES(?,?,?,?,?)');
        $st->execute([
            $data['name'],
            $data['currency_code'] ?? 'NGN',
            $data['currency_symbol'] ?? '₦',
            (float)($data['tax_rate'] ?? 0.075),
            $data['theme'] ?? 'light',
        ]);
        return (int)$this->db->lastInsertId();
    }
    public function find(int $id): ?array {
        $st = $this->db->prepare('SELECT * FROM stores WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }
    public function all(): array {
        return $this->db->query('SELECT * FROM stores ORDER BY created_at DESC')->fetchAll();
    }
}