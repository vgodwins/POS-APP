<?php
namespace App\Models;

class Product extends BaseModel {
    public function all(?int $storeId): array {
        if ($storeId) {
            $st = $this->db->prepare('SELECT * FROM products WHERE store_id = ? ORDER BY name');
            $st->execute([$storeId]);
            return $st->fetchAll();
        }
        return $this->db->query('SELECT * FROM products ORDER BY name')->fetchAll();
    }
    public function create(array $data): int {
        $st = $this->db->prepare('INSERT INTO products(store_id, name, sku, barcode, price, tax_rate, stock) VALUES(:store_id,:name,:sku,:barcode,:price,:tax_rate,:stock)');
        $st->execute([
            'store_id' => $data['store_id'],
            'name' => $data['name'],
            'sku' => $data['sku'],
            'barcode' => $data['barcode'],
            'price' => $data['price'],
            'tax_rate' => $data['tax_rate'],
            'stock' => $data['stock'],
        ]);
        return (int)$this->db->lastInsertId();
    }
}