<?php
namespace App\Models;

use App\Core\DB;

class Message {
    public function allForUser(int $userId, ?int $storeId = null): array {
        $pdo = DB::conn();
        $conds = ['(recipient_id = :uid OR recipient_id IS NULL)'];
        $params = ['uid' => $userId];
        if ($storeId) { $conds[] = 'store_id = :sid'; $params['sid'] = $storeId; }
        $sql = 'SELECT m.*, s.name AS store_name, u.name AS sender_name FROM messages m'
             . ' LEFT JOIN stores s ON s.id = m.store_id'
             . ' LEFT JOIN users u ON u.id = m.sender_id'
             . ' WHERE ' . implode(' AND ', $conds) . ' ORDER BY m.created_at DESC';
        $st = $pdo->prepare($sql); $st->execute($params); return $st->fetchAll() ?: [];
    }
    public function create(array $data): int {
        $pdo = DB::conn();
        $st = $pdo->prepare('INSERT INTO messages (store_id, sender_id, recipient_id, subject, body, status) VALUES (?,?,?,?,?,?)');
        $st->execute([
            $data['store_id'] ?? null,
            (int)$data['sender_id'],
            $data['recipient_id'] ?? null,
            $data['subject'] ?? null,
            $data['body'],
            $data['status'] ?? 'unread',
        ]);
        return (int)$pdo->lastInsertId();
    }
    public function find(int $id): ?array {
        $pdo = DB::conn();
        $st = $pdo->prepare('SELECT * FROM messages WHERE id = ?'); $st->execute([$id]); $row = $st->fetch();
        return $row ?: null;
    }
    public function markRead(int $id): void {
        DB::conn()->prepare('UPDATE messages SET status = "read" WHERE id = ?')->execute([$id]);
    }
    public function delete(int $id): void {
        DB::conn()->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
    }
    public function unreadCountForUser(int $userId, ?int $storeId = null): int {
        $pdo = DB::conn();
        $conds = ['status = "unread"', '(recipient_id = :uid OR recipient_id IS NULL)'];
        $params = ['uid' => $userId];
        if ($storeId) { $conds[] = 'store_id = :sid'; $params['sid'] = $storeId; }
        $sql = 'SELECT COUNT(*) FROM messages WHERE ' . implode(' AND ', $conds);
        $st = $pdo->prepare($sql); $st->execute($params); return (int)$st->fetchColumn();
    }
}
?>
