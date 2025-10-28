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

    public function index(Request $req): void {
        $this->ensureOwnerOrAdmin();
        $storeId = Auth::effectiveStoreId() ?? null;
        if (!$storeId) { view('categories/index', ['categories' => [], 'error' => 'Missing store context']); return; }
        $cats = (new Category())->allByStore($storeId);
        view('categories/index', ['categories' => $cats]);
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

    public function edit(Request $req): void {
        $this->ensureOwnerOrAdmin();
        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/categories'); return; }
        $c = (new Category())->find($id);
        if (!$c) { Response::redirect('/categories'); return; }
        $storeId = Auth::effectiveStoreId() ?? null;
        if ($storeId && (int)($c['store_id'] ?? 0) !== (int)$storeId) { Response::redirect('/categories'); return; }
        view('categories/edit', ['category' => $c, 'error' => null]);
    }

    public function update(Request $req): void {
        $this->ensureOwnerOrAdmin();
        $csrf = $req->body['csrf'] ?? null;
        if (!\verify_csrf($csrf)) { Response::redirect('/categories'); return; }
        $id = (int)($req->body['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/categories'); return; }
        $name = trim($req->body['name'] ?? '');
        if ($name === '') { Response::redirect('/categories'); return; }
        $storeId = Auth::effectiveStoreId() ?? null;
        if (Auth::isWriteLocked($storeId)) { Response::redirect('/categories'); return; }
        $cat = new Category();
        $existing = $cat->find($id);
        if (!$existing) { Response::redirect('/categories'); return; }
        if ($storeId && (int)($existing['store_id'] ?? 0) !== (int)$storeId) { Response::redirect('/categories'); return; }
        $cat->update($id, ['name' => $name]);
        \flash('Category updated', 'success');
        Response::redirect('/categories');
    }

    public function delete(Request $req): void {
        $this->ensureOwnerOrAdmin();
        $csrf = $req->body['csrf'] ?? null;
        if (!\verify_csrf($csrf)) { Response::redirect('/categories'); return; }
        $id = (int)($req->body['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/categories'); return; }
        $storeId = Auth::effectiveStoreId() ?? null;
        if (Auth::isWriteLocked($storeId)) { Response::redirect('/categories'); return; }
        $cat = new Category();
        $existing = $cat->find($id);
        if ($existing && (!$storeId || (int)($existing['store_id'] ?? 0) === (int)$storeId)) {
            $cat->delete($id);
            \flash('Category deleted', 'success');
        }
        Response::redirect('/categories');
    }
}
