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
    public function allWithCustomer(?int $storeId, ?int $customerId = null, ?string $linked = null, ?string $sort = null): array {
        $where = [];
        $params = [];
        if ($storeId) { $where[] = 'v.store_id = :sid'; $params['sid'] = $storeId; }
        if ($customerId) { $where[] = 'v.customer_id = :cid'; $params['cid'] = $customerId; }
        if ($linked === '1') { $where[] = 'v.customer_id IS NOT NULL'; }
        if ($linked === '0') { $where[] = 'v.customer_id IS NULL'; }
        $sql = 'SELECT v.*, c.name AS customer_name, c.phone AS customer_phone, c.email AS customer_email FROM vouchers v LEFT JOIN customers c ON v.customer_id = c.id';
        if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
        $order = 'v.created_at DESC';
        if ($sort === 'expiry_asc') { $order = 'v.expiry_date ASC, v.value DESC'; }
        elseif ($sort === 'expiry_desc') { $order = 'v.expiry_date DESC, v.value DESC'; }
        elseif ($sort === 'value_desc') { $order = 'v.value DESC, v.expiry_date ASC'; }
        elseif ($sort === 'value_asc') { $order = 'v.value ASC, v.expiry_date ASC'; }
        $sql .= ' ORDER BY ' . $order;
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function findWithCustomer(int $id): ?array {
        $sql = 'SELECT v.*, c.name AS customer_name, c.phone AS customer_phone, c.email AS customer_email FROM vouchers v LEFT JOIN customers c ON v.customer_id = c.id WHERE v.id = :id';
        $st = $this->db->prepare($sql);
        $st->execute(['id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }
    public function create(array $data): int {
        $st = $this->db->prepare('INSERT INTO vouchers(code, value, currency_code, expiry_date, status, store_id, customer_id) VALUES(:code,:value,:currency_code,:expiry_date,:status,:store_id,:customer_id)');
        $st->execute([
            'code' => $data['code'],
            'value' => $data['value'],
            'currency_code' => $data['currency_code'],
            'expiry_date' => $data['expiry_date'],
            'status' => 'active',
            'store_id' => $data['store_id'],
            'customer_id' => $data['customer_id'] ?? null,
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

    public function findByCodeAnyStatus(string $code, ?int $storeId): ?array {
        $sql = 'SELECT * FROM vouchers WHERE code = :code';
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

    public function redeemPartial(int $id, float $amount): void {
        $amt = max(0.0, (float)$amount);
        // Reduce value, and mark used when balance hits zero
        $st = $this->db->prepare('UPDATE vouchers SET value = GREATEST(value - :amt, 0), status = CASE WHEN (value - :amt) <= 0 THEN "used" ELSE "active" END WHERE id = :id');
        $st->execute(['amt' => $amt, 'id' => $id]);
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

    public function find(int $id): ?array {
        $st = $this->db->prepare('SELECT * FROM vouchers WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function update(int $id, array $fields): bool {
        $allowed = ['value','expiry_date','status','currency_code','customer_id'];
        $set = []; $params = [];
        foreach ($fields as $k => $v) {
            if (!in_array($k, $allowed, true)) { continue; }
            $set[] = "$k = :$k"; $params[$k] = $v;
        }
        if (!$set) { return false; }
        $params['id'] = $id;
        $sql = 'UPDATE vouchers SET ' . implode(', ', $set) . ' WHERE id = :id';
        $st = $this->db->prepare($sql);
        return $st->execute($params) === true;
    }

    public function bulkCreate(int $storeId, float $value, string $currencyCode, string $expiryDate, int $count = 1, string $prefix = ''): array {
        $created = [];
        $prefix = trim($prefix);
        for ($i = 0; $i < max(1, $count); $i++) {
            $code = $this->generateUniqueCode(10);
            if ($prefix !== '') {
                // Ensure combined length fits within VARCHAR(32)
                $maxRandom = 32 - strlen($prefix);
                if ($maxRandom < 4) { $maxRandom = 4; } // minimum
                $code = substr($prefix, 0, 32) . substr($code, 0, $maxRandom);
            }
            $this->create([
                'code' => $code,
                'value' => $value,
                'currency_code' => $currencyCode,
                'expiry_date' => $expiryDate,
                'store_id' => $storeId,
            ]);
            $created[] = $code;
        }
        return $created;
    }
}
