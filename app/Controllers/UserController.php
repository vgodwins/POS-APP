<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\User;
use App\Models\Store;

class UserController {
    private function ensureOwnerOrAdmin(): void {
        if (!Auth::check() || !(Auth::hasRole('admin') || Auth::hasRole('owner'))) { Response::redirect('/'); }
    }
    private function ownerAllowedRoles(): array { return ['cashier','accountant','manager']; }

    public function index(Request $req): void {
        $this->ensureOwnerOrAdmin();
        $uModel = new User();
        if (Auth::hasRole('admin')) {
            $users = $uModel->all();
            view('users/index', ['users' => $users]);
            return;
        }
        $sid = Auth::effectiveStoreId() ?? null;
        $users = $sid ? $uModel->allByStore((int)$sid) : [];
        // Exclude admin/owner from owner-managed list
        $users = array_values(array_filter($users, function($u){
            $roles = is_array($u['roles']) ? $u['roles'] : (isset($u['roles']) ? explode(',', (string)$u['roles']) : []);
            return !in_array('admin', $roles, true) && !in_array('owner', $roles, true);
        }));
        view('users/index', ['users' => $users, 'ownerMode' => true]);
    }

    public function create(Request $req): void {
        $this->ensureOwnerOrAdmin();
        if (Auth::hasRole('admin')) {
            $roles = (new User())->listRoles();
            $stores = (new Store())->all();
            view('users/create', ['roles' => $roles, 'stores' => $stores]);
            return;
        }
        $roles = $this->ownerAllowedRoles();
        $sid = Auth::effectiveStoreId() ?? null;
        view('users/create', ['roles' => $roles, 'stores' => [], 'ownerMode' => true, 'ownerStoreId' => $sid]);
    }

    public function save(Request $req): void {
        $this->ensureOwnerOrAdmin();
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { $this->create($req); return; }
        $name = trim($req->body['name'] ?? '');
        $email = trim($req->body['email'] ?? '');
        $password = (string)($req->body['password'] ?? '');
        $roles = $req->body['roles'] ?? [];
        $storeId = $req->body['store_id'] !== '' ? (int)$req->body['store_id'] : null;
        if (!$name || !$email || !$password) {
            if (Auth::hasRole('admin')) {
                view('users/create', ['error' => 'Name, email, and password are required', 'roles' => (new User())->listRoles(), 'stores' => (new Store())->all()]);
            } else {
                view('users/create', ['error' => 'Name, email, and password are required', 'roles' => $this->ownerAllowedRoles(), 'ownerMode' => true, 'ownerStoreId' => Auth::effectiveStoreId()]);
            }
            return;
        }
        $u = new User();
        if (Auth::hasRole('admin')) {
            $defaultRole = !empty($roles) ? (string)$roles[0] : 'owner';
            $userId = $u->create(['name' => $name, 'email' => $email, 'password' => $password], $defaultRole, $storeId);
            if (!empty($roles)) { $u->setRoles($userId, $roles); }
            Response::redirect('/users');
            return;
        }
        $sid = Auth::effectiveStoreId() ?? null;
        $allowed = $this->ownerAllowedRoles();
        $roles = array_values(array_intersect(array_map('strval', $roles), $allowed));
        $defaultRole = !empty($roles) ? (string)$roles[0] : 'cashier';
        $userId = $u->create(['name' => $name, 'email' => $email, 'password' => $password], $defaultRole, $sid ? (int)$sid : null);
        if (!empty($roles)) { $u->setRoles($userId, $roles); }
        Response::redirect('/users');
    }

    public function edit(Request $req): void {
        $this->ensureOwnerOrAdmin();
        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/users'); return; }
        $u = new User();
        $user = $u->find($id);
        if (!$user) { Response::redirect('/users'); return; }
        if (Auth::hasRole('admin')) {
            $roles = $u->listRoles();
            $stores = (new Store())->all();
            view('users/edit', ['user' => $user, 'roles' => $roles, 'stores' => $stores]);
            return;
        }
        $sid = Auth::effectiveStoreId() ?? null;
        if ($sid && (int)($user['store_id'] ?? 0) !== (int)$sid) { Response::redirect('/users'); return; }
        $roles = $this->ownerAllowedRoles();
        view('users/edit', ['user' => $user, 'roles' => $roles, 'stores' => [], 'ownerMode' => true, 'ownerStoreId' => $sid]);
    }

    public function update(Request $req): void {
        $this->ensureOwnerOrAdmin();
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
        if (Auth::hasRole('admin')) {
            $u->update($id, ['name' => $name, 'email' => $email, 'password' => $password, 'store_id' => $storeId]);
            $u->setRoles($id, $roles);
            Response::redirect('/users');
            return;
        }
        $sid = Auth::effectiveStoreId() ?? null;
        $user = $u->find($id);
        if (!$user || ($sid && (int)($user['store_id'] ?? 0) !== (int)$sid)) { Response::redirect('/users'); return; }
        $u->update($id, ['name' => $name, 'email' => $email, 'password' => $password, 'store_id' => $sid ? (int)$sid : null]);
        $allowed = $this->ownerAllowedRoles();
        $roles = array_values(array_intersect(array_map('strval', $roles), $allowed));
        $u->setRoles($id, $roles);
        Response::redirect('/users');
    }

    public function delete(Request $req): void {
        $this->ensureOwnerOrAdmin();
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { Response::redirect('/users'); return; }
        $id = (int)($req->body['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/users'); return; }
        $u = new User();
        if (Auth::hasRole('admin')) {
            $u->delete($id);
            Response::redirect('/users');
            return;
        }
        $sid = Auth::effectiveStoreId() ?? null;
        $user = $u->find($id);
        if (!$user || ($sid && (int)($user['store_id'] ?? 0) !== (int)$sid)) { Response::redirect('/users'); return; }
        $roles = is_array($user['roles']) ? $user['roles'] : (isset($user['roles']) ? explode(',', (string)$user['roles']) : []);
        if (in_array('admin', $roles, true) || in_array('owner', $roles, true)) { Response::redirect('/users'); return; }
        $u->delete($id);
        Response::redirect('/users');
    }
}
