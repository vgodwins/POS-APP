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
}