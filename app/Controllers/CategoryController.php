<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\Category;

class CategoryController {
    private function ensureOwnerOrAdmin(): void {
        if (!Auth::check() || !(Auth::hasRole('admin') || Auth::hasRole('owner'))) {
            Response::redirect('/');
        }
    }

    public function create(Request $req): void {
        $this->ensureOwnerOrAdmin();
        view('categories/create', ['error' => null]);
    }

    public function save(Request $req): void {
        $this->ensureOwnerOrAdmin();
        if (!isset($req->body['csrf']) || !\verify_csrf($req->body['csrf'] ?? null)) {
            view('categories/create', ['error' => 'Invalid CSRF token']);
            return;
        }
        $name = trim($req->body['name'] ?? '');
        if ($name === '') { view('categories/create', ['error' => 'Name is required']); return; }
        $storeId = Auth::effectiveStoreId();
        if (Auth::isWriteLocked($storeId)) { view('categories/create', ['error' => 'Store is locked or outside active hours']); return; }
        if (!$storeId) { view('categories/create', ['error' => 'Missing store context']); return; }
        (new Category())->create(['store_id' => $storeId, 'name' => $name]);
        Response::redirect('/products');
    }
}
