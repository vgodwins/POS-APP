<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\User;
use App\Models\Store;

class UserController {
    private function ensureAdmin(): void {
        if (!Auth::check() || !Auth::hasRole('admin')) { Response::redirect('/'); }
    }

    public function index(Request $req): void {
        $this->ensureAdmin();
        $users = (new User())->all();
        view('users/index', ['users' => $users]);
    }

    public function create(Request $req): void {
        $this->ensureAdmin();
        $roles = (new User())->listRoles();
        $stores = (new Store())->all();
        view('users/create', ['roles' => $roles, 'stores' => $stores]);
    }

    public function save(Request $req): void {
        $this->ensureAdmin();
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { $this->create($req); return; }
        $name = trim($req->body['name'] ?? '');
        $email = trim($req->body['email'] ?? '');
        $password = (string)($req->body['password'] ?? '');
        $storeId = $req->body['store_id'] !== '' ? (int)$req->body['store_id'] : null;
        $roles = $req->body['roles'] ?? [];
        if (!$name || !$email || !$password) { view('users/create', ['error' => 'Name, email, and password are required', 'roles' => (new User())->listRoles(), 'stores' => (new Store())->all()]); return; }
        // Default role if none selected
        $defaultRole = !empty($roles) ? (string)$roles[0] : 'owner';
        $u = new User();
        $userId = $u->create(['name' => $name, 'email' => $email, 'password' => $password], $defaultRole, $storeId);
        if (!empty($roles)) { $u->setRoles($userId, $roles); }
        Response::redirect('/users');
    }

    public function edit(Request $req): void {
        $this->ensureAdmin();
        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/users'); return; }
        $u = new User();
        $user = $u->find($id);
        $roles = $u->listRoles();
        $stores = (new Store())->all();
        if (!$user) { Response::redirect('/users'); return; }
        view('users/edit', ['user' => $user, 'roles' => $roles, 'stores' => $stores]);
    }

    public function update(Request $req): void {
        $this->ensureAdmin();
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { Response::redirect('/users'); return; }
        $id = (int)($req->body['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/users'); return; }
        $name = trim($req->body['name'] ?? '');
        $email = trim($req->body['email'] ?? '');
        $password = (string)($req->body['password'] ?? '');
        $storeId = $req->body['store_id'] !== '' ? (int)$req->body['store_id'] : null;
        $roles = $req->body['roles'] ?? [];
        $u = new User();
        $u->update($id, ['name' => $name, 'email' => $email, 'password' => $password, 'store_id' => $storeId]);
        $u->setRoles($id, $roles);
        Response::redirect('/users');
    }

    public function delete(Request $req): void {
        $this->ensureAdmin();
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { Response::redirect('/users'); return; }
        $id = (int)($req->body['id'] ?? 0);
        if ($id > 0) { (new User())->delete($id); }
        Response::redirect('/users');
    }
}