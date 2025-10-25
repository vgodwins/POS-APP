<?php
namespace App\Models;

class Voucher extends BaseModel {
    public function all(?int $storeId): array {
        if ($storeId) {
            $st = $this->db->prepare('SELECT * FROM vouchers WHERE store_id = ? ORDER BY created_at DESC');
            $st->execute([$storeId]);
            return $st->fetchAll();
        }
        return $this->db->query('SELECT * FROM vouchers ORDER BY created_at DESC')->fetchAll();
    }
    public function create(array $data): int {
        $st = $this->db->prepare('INSERT INTO vouchers(code, value, currency_code, expiry_date, status, store_id) VALUES(:code,:value,:currency_code,:expiry_date,:status,:store_id)');
        $st->execute([
            'code' => $data['code'],
            'value' => $data['value'],
            'currency_code' => $data['currency_code'],
            'expiry_date' => $data['expiry_date'],
            'status' => 'active',
            'store_id' => $data['store_id'],
        ]);
        return (int)$this->db->lastInsertId();
    }
    public function findByCode(string $code, ?int $storeId): ?array {
        $sql = 'SELECT * FROM vouchers WHERE code = :code AND status = "active"';
        $params = ['code' => $code];
        if ($storeId) { $sql .= ' AND store_id = :sid'; $params['sid'] = $storeId; }
        $st = $this->db->prepare($sql);
        $st->execute($params);
        $row = $st->fetch();
        return $row ?: null;
    }
    public function markUsed(int $id): void {
        $this->db->prepare('UPDATE vouchers SET status = "used" WHERE id = ?')->execute([$id]);
    }
    public function generateUniqueCode(int $length = 10): string {
        do {
            $code = strtoupper(bin2hex(random_bytes((int)ceil($length/2))));
            $st = $this->db->prepare('SELECT id FROM vouchers WHERE code = ?');
            $st->execute([$code]);
            $exists = $st->fetchColumn();
        } while ($exists);
        return $code;
    }
}