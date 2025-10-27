<?php
namespace App\Models;

class Expense extends BaseModel {
    public function all(?int $storeId): array {
        if ($storeId) {
            $st = $this->db->prepare('SELECT * FROM expenses WHERE store_id = ? ORDER BY created_at DESC');
            $st->execute([$storeId]);
            return $st->fetchAll();
        }
        return $this->db->query('SELECT * FROM expenses ORDER BY created_at DESC')->fetchAll();
    }
    public function find(int $id): ?array {
        $st = $this->db->prepare('SELECT * FROM expenses WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }
    public function create(array $data): int {
        $st = $this->db->prepare('INSERT INTO expenses(store_id, category, amount, note) VALUES(?,?,?,?)');
        $st->execute([
            $data['store_id'],
            $data['category'],
            (float)$data['amount'],
            $data['note'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }
    public function update(int $id, array $data): void {
        $st = $this->db->prepare('UPDATE expenses SET category = ?, amount = ?, note = ? WHERE id = ?');
        $st->execute([
            $data['category'],
            (float)$data['amount'],
            $data['note'] ?? null,
            $id,
        ]);
    }
    public function delete(int $id): void {
        $st = $this->db->prepare('DELETE FROM expenses WHERE id = ?');
        $st->execute([$id]);
    }
    public function summary(?int $storeId): array {
        $conds = [];
        $params = [];
        $sqlBase = 'SELECT COALESCE(SUM(amount),0) AS total FROM expenses';
        if ($storeId) { $conds[] = 'store_id = :sid'; $params['sid'] = $storeId; }
        $where = $conds ? (' WHERE ' . implode(' AND ', $conds)) : '';
        $pdo = $this->db;
        // Build periods
        $fn = function (string $extra) use ($sqlBase, $where, $params, $pdo) {
            $sql = $sqlBase . $where;
            if ($extra !== '') { $sql .= ($where ? ' AND ' : ' WHERE ') . $extra; }
            $st = $pdo->prepare($sql);
            $st->execute($params);
            return (float)($st->fetchColumn() ?: 0);
        };
        return [
            'today' => $fn('DATE(created_at) = CURDATE()'),
            'week' => $fn('YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)'),
            'month' => $fn('YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())'),
            'year' => $fn('YEAR(created_at) = YEAR(CURDATE())'),
            'all' => $fn(''),
        ];
    }
}
