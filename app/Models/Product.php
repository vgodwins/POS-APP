<?php
namespace App\Models;

class Product extends BaseModel {
    public function all(?int $storeId, ?int $categoryId = null): array {
        if ($storeId) {
            if ($categoryId) {
                $st = $this->db->prepare('SELECT * FROM products WHERE store_id = ? AND category_id = ? ORDER BY name');
                $st->execute([$storeId, $categoryId]);
                return $st->fetchAll();
            }
            $st = $this->db->prepare('SELECT * FROM products WHERE store_id = ? ORDER BY name');
            $st->execute([$storeId]);
            return $st->fetchAll();
        }
        if ($categoryId) {
            $st = $this->db->prepare('SELECT * FROM products WHERE category_id = ? ORDER BY name');
            $st->execute([$categoryId]);
            return $st->fetchAll();
        }
        return $this->db->query('SELECT * FROM products ORDER BY name')->fetchAll();
    }
    public function create(array $data): int {
        // Insert using original columns; cost_price/status may be set via update if present
        $st = $this->db->prepare('INSERT INTO products(store_id, name, sku, barcode, price, tax_rate, stock, category_id) VALUES(:store_id,:name,:sku,:barcode,:price,:tax_rate,:stock,:category_id)');
        $st->execute([
            'store_id' => $data['store_id'],
            'name' => $data['name'],
            'sku' => $data['sku'],
            'barcode' => $data['barcode'],
            'price' => $data['price'],
            'tax_rate' => $data['tax_rate'],
            'stock' => $data['stock'],
            'category_id' => $data['category_id'] ?? null,
        ]);
        $id = (int)$this->db->lastInsertId();
        // Try to update optional fields if provided
        $opt = [];
        if (isset($data['cost_price'])) { $opt['cost_price'] = (float)$data['cost_price']; }
        if (isset($data['status'])) { $opt['status'] = (string)$data['status']; }
        if (!empty($opt)) {
            try { $this->update($id, $opt); } catch (\Throwable $e) { /* ignore if columns missing */ }
        }
        return $id;
    }
    public function find(int $id): ?array {
        $st = $this->db->prepare('SELECT * FROM products WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }
    public function update(int $id, array $data): void {
        $fields = [
            'name' => $data['name'] ?? null,
            'sku' => $data['sku'] ?? null,
            'barcode' => $data['barcode'] ?? null,
            'price' => $data['price'] ?? null,
            'stock' => $data['stock'] ?? null,
            'tax_rate' => $data['tax_rate'] ?? null,
            'cost_price' => $data['cost_price'] ?? null,
            'status' => $data['status'] ?? null,
            'category_id' => $data['category_id'] ?? null,
        ];
        $set = []; $params = ['id' => $id];
        foreach ($fields as $k => $v) { if ($v !== null) { $set[] = "$k = :$k"; $params[$k] = $v; } }
        if (!$set) return;
        $sql = 'UPDATE products SET ' . implode(', ', $set) . ' WHERE id = :id';
        $st = $this->db->prepare($sql);
        $st->execute($params);
    }
}
