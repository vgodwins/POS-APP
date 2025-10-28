<?php
namespace App\Models;

class Category extends BaseModel {
    public function allByStore(int $storeId): array {
        $st = $this->db->prepare('SELECT * FROM categories WHERE store_id = ? ORDER BY name');
        $st->execute([$storeId]);
        return $st->fetchAll();
    }
    public function create(array $data): int {
        $st = $this->db->prepare('INSERT INTO categories(store_id, name) VALUES(:store_id,:name)');
        $st->execute(['store_id' => $data['store_id'], 'name' => $data['name']]);
        return (int)$this->db->lastInsertId();
    }
    public function find(int $id): ?array {
        $st = $this->db->prepare('SELECT * FROM categories WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }
    public function update(int $id, array $data): void {
        $fields = ['name' => $data['name'] ?? null];
        $set = []; $params = ['id' => $id];
        foreach ($fields as $k => $v) { if ($v !== null) { $set[] = "$k = :$k"; $params[$k] = $v; } }
        if (!$set) return;
        $sql = 'UPDATE categories SET ' . implode(', ', $set) . ' WHERE id = :id';
        $st = $this->db->prepare($sql);
        $st->execute($params);
    }
}

