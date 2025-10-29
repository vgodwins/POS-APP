<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\Customer;

class CustomerController {
    private function ensureOwnerOrAdmin(): void {
        if (!Auth::check() || !(Auth::hasRole('admin') || Auth::hasRole('owner') || Auth::hasRole('manager'))) {
            Response::redirect('/');
        }
    }

    public function index(Request $req): void {
        $this->ensureOwnerOrAdmin();
        $storeId = Auth::effectiveStoreId();
        $customers = [];
        if ($storeId) {
            try { $customers = (new Customer())->allByStore((int)$storeId); } catch (\Throwable $e) { $customers = []; }
        }
        view('customers/index', ['customers' => $customers]);
    }

    public function create(Request $req): void {
        $this->ensureOwnerOrAdmin();
        view('customers/create', ['error' => null]);
    }

    public function save(Request $req): void {
        $this->ensureOwnerOrAdmin();
        if (!isset($req->body['csrf']) || !\verify_csrf($req->body['csrf'] ?? null)) {
            view('customers/create', ['error' => 'Invalid CSRF token']);
            return;
        }
        $name = trim($req->body['name'] ?? '');
        $phone = trim($req->body['phone'] ?? '');
        $email = trim($req->body['email'] ?? '');
        if ($name === '') {
            view('customers/create', ['error' => 'Name is required']);
            return;
        }
        $storeId = Auth::effectiveStoreId();
        if (Auth::isWriteLocked($storeId)) { view('customers/create', ['error' => 'Store is locked or outside active hours']); return; }
        if (!$storeId) { view('customers/create', ['error' => 'Missing store context']); return; }
        $cust = new Customer();
        $cust->create(['store_id' => $storeId, 'name' => $name, 'phone' => $phone ?: null, 'email' => $email ?: null]);
        Response::redirect('/customers');
    }

    public function edit(Request $req): void {
        $this->ensureOwnerOrAdmin();
        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/customers'); return; }
        $cust = null;
        try { $cust = (new Customer())->find($id); } catch (\Throwable $e) { $cust = null; }
        if (!$cust) { Response::redirect('/customers'); return; }
        view('customers/edit', ['customer' => $cust, 'error' => null]);
    }

    public function update(Request $req): void {
        $this->ensureOwnerOrAdmin();
        if (!isset($req->body['csrf']) || !\verify_csrf($req->body['csrf'] ?? null)) {
            view('customers/edit', ['customer' => ['id' => (int)($req->body['id'] ?? 0)], 'error' => 'Invalid CSRF token']);
            return;
        }
        $id = (int)($req->body['id'] ?? 0);
        $name = trim($req->body['name'] ?? '');
        $phone = trim($req->body['phone'] ?? '');
        $email = trim($req->body['email'] ?? '');
        if ($id <= 0 || $name === '') {
            view('customers/edit', ['customer' => ['id' => $id, 'name' => $name, 'phone' => $phone, 'email' => $email], 'error' => 'Invalid input']);
            return;
        }
        try {
            (new Customer())->update($id, ['name' => $name, 'phone' => $phone ?: null, 'email' => $email ?: null]);
        } catch (\Throwable $e) {
            view('customers/edit', ['customer' => ['id' => $id, 'name' => $name, 'phone' => $phone, 'email' => $email], 'error' => 'Failed to update']);
            return;
        }
        \flash('Customer updated', 'success');
        Response::redirect('/customers');
    }

    public function delete(Request $req): void {
        $this->ensureOwnerOrAdmin();
        if (!isset($req->body['csrf']) || !\verify_csrf($req->body['csrf'] ?? null)) { Response::redirect('/customers'); return; }
        $id = (int)($req->body['id'] ?? 0);
        if ($id > 0) {
            try { (new Customer())->delete($id); \flash('Customer deleted', 'success'); } catch (\Throwable $e) { /* swallow */ }
        }
        Response::redirect('/customers');
    }
}
