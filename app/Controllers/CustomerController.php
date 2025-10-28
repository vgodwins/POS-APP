<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\Customer;

class CustomerController {
    private function ensureOwnerOrAdmin(): void {
        if (!Auth::check() || !(Auth::hasRole('admin') || Auth::hasRole('owner'))) {
            Response::redirect('/');
        }
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
        Response::redirect('/products');
    }
}
