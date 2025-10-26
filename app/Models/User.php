<?php
namespace App\Models;

use PDO;

class User extends BaseModel {
    public function findByEmail(string $email): ?array {
        $st = $this->db->prepare('SELECT u.*, GROUP_CONCAT(r.name) as roles
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r ON r.id = ur.role_id
            WHERE u.email = :email GROUP BY u.id');
        $st->execute(['email' => $email]);
        $row = $st->fetch();
        if (!$row) return null;
        $row['roles'] = $row['roles'] ? explode(',', $row['roles']) : [];
        return $row;
    }
    public function create(array $data, string $role = 'owner', ?int $storeId = null): int {
        $st = $this->db->prepare('INSERT INTO users(name,email,password,store_id) VALUES(:name,:email,:password,:store_id)');
        $st->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'store_id' => $storeId,
        ]);
        $userId = (int)$this->db->lastInsertId();
        // Attach role
        $roleId = $this->getRoleId($role);
        $st2 = $this->db->prepare('INSERT INTO user_roles(user_id,role_id) VALUES(?,?)');
        $st2->execute([$userId, $roleId]);
        return $userId;
    }
    public function getRoleId(string $role): int {
        $st = $this->db->prepare('SELECT id FROM roles WHERE name = :name');
        $st->execute(['name' => $role]);
        $id = $st->fetchColumn();
        if ($id) return (int)$id;
        $this->db->prepare('INSERT INTO roles(name) VALUES(?)')->execute([$role]);
        return (int)$this->db->lastInsertId();
    }
    public function all(): array {
        $sql = 'SELECT u.*, s.name AS store_name, GROUP_CONCAT(r.name) AS roles
                FROM users u
                LEFT JOIN stores s ON s.id = u.store_id
                LEFT JOIN user_roles ur ON ur.user_id = u.id
                LEFT JOIN roles r ON r.id = ur.role_id
                GROUP BY u.id ORDER BY u.created_at DESC';
        return $this->db->query($sql)->fetchAll();
    }
    public function find(int $id): ?array {
        $st = $this->db->prepare('SELECT u.*, s.name AS store_name, GROUP_CONCAT(r.name) AS roles
                FROM users u
                LEFT JOIN stores s ON s.id = u.store_id
                LEFT JOIN user_roles ur ON ur.user_id = u.id
                LEFT JOIN roles r ON r.id = ur.role_id
                WHERE u.id = :id GROUP BY u.id');
        $st->execute(['id' => $id]);
        $row = $st->fetch();
        if (!$row) return null;
        $row['roles'] = $row['roles'] ? explode(',', $row['roles']) : [];
        return $row;
    }
    public function update(int $id, array $data): void {
        $fields = ['name' => $data['name'] ?? null, 'email' => $data['email'] ?? null, 'store_id' => $data['store_id'] ?? null];
        $set = [];
        $params = ['id' => $id];
        foreach ($fields as $k => $v) {
            if ($v !== null) { $set[] = "$k = :$k"; $params[$k] = $v; }
        }
        if (!empty($data['password'])) {
            $set[] = 'password = :password';
            $params['password'] = password_hash((string)$data['password'], PASSWORD_DEFAULT);
        }
        if (!$set) return;
        $sql = 'UPDATE users SET ' . implode(', ', $set) . ' WHERE id = :id';
        $st = $this->db->prepare($sql);
        $st->execute($params);
    }
    public function delete(int $id): void {
        // Remove role mappings first (CASCADE also handles if FK defined)
        $this->db->prepare('DELETE FROM user_roles WHERE user_id = ?')->execute([$id]);
        $this->db->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
    }
    public function setRoles(int $userId, array $roles): void {
        // Normalize roles to unique names
        $roles = array_values(array_unique(array_map('strval', $roles)));
        $this->db->prepare('DELETE FROM user_roles WHERE user_id = ?')->execute([$userId]);
        foreach ($roles as $name) {
            $rid = $this->getRoleId($name);
            $this->db->prepare('INSERT INTO user_roles(user_id, role_id) VALUES(?, ?)')->execute([$userId, $rid]);
        }
    }
    public function listRoles(): array {
        return $this->db->query('SELECT name FROM roles ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
    }
}