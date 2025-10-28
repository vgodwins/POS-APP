<?php
namespace App\Models;

class Customer extends BaseModel {
    public function allByStore(int $storeId): array {
        $st = $this->db->prepare('SELECT * FROM customers WHERE store_id = ? ORDER BY name');
        $st->execute([$storeId]);
        return $st->fetchAll() ?: [];
    }
    public function find(int $id): ?array {
        $st = $this->db->prepare('SELECT * FROM customers WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }
}

