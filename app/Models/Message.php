<?php
namespace App\Models;

use App\Core\DB;

class Message {
    public function allForUser(int $userId, ?int $storeId = null): array {
        $pdo = DB::conn();
        $conds = ['(m.recipient_id = :uid OR m.recipient_id IS NULL)'];
        $params = ['uid' => $userId];
        if ($storeId) { $conds[] = 'm.store_id = :sid'; $params['sid'] = $storeId; }
        $sql = 'SELECT m.*, s.name AS store_name, u.name AS sender_name FROM messages m'
             . ' LEFT JOIN stores s ON s.id = m.store_id'
             . ' LEFT JOIN users u ON u.id = m.sender_id'
             . ' WHERE ' . implode(' AND ', $conds) . ' ORDER BY m.created_at DESC';
        $st = $pdo->prepare($sql); $st->execute($params); return $st->fetchAll() ?: [];
    }
    public function create(array $data): int {
        $pdo = DB::conn();
        $st = $pdo->prepare('INSERT INTO messages (store_id, sender_id, recipient_id, recipient_customer_id, parent_id, subject, body, status) VALUES (?,?,?,?,?,?,?,?)');
        $st->execute([
            $data['store_id'] ?? null,
            (int)$data['sender_id'],
            $data['recipient_id'] ?? null,
            $data['recipient_customer_id'] ?? null,
            $data['parent_id'] ?? null,
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
    public function thread(int $rootId): array {
        $pdo = DB::conn();
        $sql = 'SELECT m.*, us.name AS sender_name, ur.name AS recipient_name, c.name AS recipient_customer_name'
             . ' FROM messages m'
             . ' LEFT JOIN users us ON us.id = m.sender_id'
             . ' LEFT JOIN users ur ON ur.id = m.recipient_id'
             . ' LEFT JOIN customers c ON c.id = m.recipient_customer_id'
             . ' WHERE (m.id = :root OR m.parent_id = :root)'
             . ' ORDER BY m.created_at ASC';
        $st = $pdo->prepare($sql); $st->execute(['root' => $rootId]);
        return $st->fetchAll() ?: [];
    }
    public function resolveRootId(array $msg): int {
        return (int)($msg['parent_id'] ?? 0) > 0 ? (int)$msg['parent_id'] : (int)$msg['id'];
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
