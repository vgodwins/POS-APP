<?php
namespace App\Models;

class Customer extends BaseModel {
    public function allByStore(int $storeId): array {
        $st = $this->db->prepare('SELECT * FROM customers WHERE store_id = ? ORDER BY name');
        $st->execute([$storeId]);
        return $st->fetchAll() ?: [];
    }
    public function create(array $data): int {
        $st = $this->db->prepare('INSERT INTO customers(store_id,name,phone,email) VALUES(:store_id,:name,:phone,:email)');
        $st->execute([
            'store_id' => $data['store_id'],
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }
    public function find(int $id): ?array {
        $st = $this->db->prepare('SELECT * FROM customers WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function update(int $id, array $data): void {
        $fields = [
            'name' => $data['name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
        ];
        $set = []; $params = ['id' => $id];
        foreach ($fields as $k => $v) { if ($v !== null) { $set[] = "$k = :$k"; $params[$k] = $v; } }
        if (!$set) return;
        $sql = 'UPDATE customers SET ' . implode(', ', $set) . ' WHERE id = :id';
        $st = $this->db->prepare($sql);
        $st->execute($params);
    }

    public function delete(int $id): void {
        $st = $this->db->prepare('DELETE FROM customers WHERE id = ?');
        $st->execute([$id]);
    }
}
