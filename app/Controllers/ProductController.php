<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Auth;
use App\Core\Response;
use App\Core\Config;
use App\Models\Product;
use App\Models\Category;

class ProductController {
    public function index(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $storeId = Auth::effectiveStoreId() ?? null;
        $categoryId = ($req->query['category_id'] ?? '') !== '' ? (int)$req->query['category_id'] : null;
        $p = new Product();
        $list = $p->all($storeId, $categoryId);
        $cats = (new Category())->allByStore($storeId);
        // Inventory summary and low-stock metrics
        $threshold = (int)(Config::get('defaults')['low_stock_threshold'] ?? 5);
        $summary = [
            'totalProducts' => count($list),
            'totalStock' => array_sum(array_map(fn($r) => (int)($r['stock'] ?? 0), $list)),
            'valid' => 0,
            'expired' => 0,
            'damaged' => 0,
            'returned' => 0,
            'lowStockCount' => 0,
        ];
        foreach ($list as $row) {
            $status = strtolower($row['status'] ?? 'valid');
            if (isset($summary[$status])) { $summary[$status]++; }
            if ((int)($row['stock'] ?? 0) <= $threshold) { $summary['lowStockCount']++; }
        }
        view('products/index', [
            'products' => $list,
            'categories' => $cats,
            'selectedCategoryId' => $categoryId,
            'summary' => $summary,
            'lowThreshold' => $threshold,
        ]);
    }

    public function exportCsv(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        if (!(Auth::hasRole('owner') || Auth::hasRole('admin'))) { Response::redirect('/products'); return; }
        $storeId = Auth::effectiveStoreId() ?? null;
        $categoryId = ($req->query['category_id'] ?? '') !== '' ? (int)$req->query['category_id'] : null;
        $p = new Product();
        $list = $p->all($storeId, $categoryId);
        $catMap = [];
        try {
            $cats = (new Category())->allByStore($storeId ?? 0);
            foreach ($cats as $c) { $catMap[(int)($c['id'] ?? 0)] = (string)($c['name'] ?? ''); }
        } catch (\Throwable $e) { /* ignore */ }
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="inventory_export.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Name','SKU','Barcode','Price','Stock','Status','Category','Cost Price','Tax Rate']);
        foreach ($list as $r) {
            $row = [
                (string)($r['name'] ?? ''),
                (string)($r['sku'] ?? ''),
                (string)($r['barcode'] ?? ''),
                (string)($r['price'] ?? ''),
                (string)($r['stock'] ?? ''),
                (string)($r['status'] ?? ''),
                (string)($catMap[(int)($r['category_id'] ?? 0)] ?? ''),
                (string)($r['cost_price'] ?? ''),
                (string)($r['tax_rate'] ?? ''),
            ];
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }
    public function create(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $storeId = Auth::effectiveStoreId() ?? null;
        $cats = (new Category())->allByStore($storeId);
        view('products/create', ['categories' => $cats]);
    }
    public function save(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { view('products/create', ['error' => 'Invalid session']); return; }
        $storeId = Auth::effectiveStoreId() ?? null;
        if (Auth::isWriteLocked($storeId)) { view('products/create', ['error' => 'Store is locked or outside active hours']); return; }
        $data = [
            'store_id' => $storeId,
            'name' => trim($req->body['name'] ?? ''),
            'sku' => trim($req->body['sku'] ?? ''),
            'barcode' => trim($req->body['barcode'] ?? ''),
            'price' => (float)($req->body['price'] ?? 0),
            'tax_rate' => (float)(Config::get('defaults')['tax_rate'] ?? 0),
            'stock' => (int)($req->body['stock'] ?? 0),
            'cost_price' => (float)($req->body['cost_price'] ?? 0),
            'status' => (string)($req->body['status'] ?? 'valid'),
            'category_id' => ($req->body['category_id'] ?? '') !== '' ? (int)$req->body['category_id'] : null,
        ];
        $p = new Product();
        $p->create($data);
        Response::redirect('/products');
    }
    public function edit(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/products'); return; }
        $p = new Product();
        $prod = $p->find($id);
        if (!$prod) { Response::redirect('/products'); return; }
        $storeId = Auth::user()['store_id'] ?? null;
        $cats = (new Category())->allByStore($storeId);
        view('products/edit', ['product' => $prod, 'categories' => $cats]);
    }
    public function update(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { Response::redirect('/products'); return; }
        $id = (int)($req->body['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/products'); return; }
        $storeId = Auth::effectiveStoreId() ?? null;
        if (Auth::isWriteLocked($storeId)) { Response::redirect('/products'); return; }
        $data = [
            'name' => trim($req->body['name'] ?? ''),
            'sku' => trim($req->body['sku'] ?? ''),
            'barcode' => trim($req->body['barcode'] ?? ''),
            'price' => ($req->body['price'] !== '' ? (float)$req->body['price'] : null),
            'stock' => ($req->body['stock'] !== '' ? (int)$req->body['stock'] : null),
            'cost_price' => ($req->body['cost_price'] !== '' ? (float)$req->body['cost_price'] : null),
            'status' => (string)($req->body['status'] ?? 'valid'),
            'category_id' => ($req->body['category_id'] ?? '') !== '' ? (int)$req->body['category_id'] : null,
        ];
        (new Product())->update($id, $data);
        Response::redirect('/products');
    }
    public function uploadCsv(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        if (($req->method === 'POST') && isset($_FILES['csv']) && $_FILES['csv']['error'] === UPLOAD_ERR_OK) {
            $storeId = Auth::effectiveStoreId() ?? null;
            if (Auth::isWriteLocked($storeId)) { view('products/upload', ['error' => 'Store is locked or outside active hours']); return; }
            $p = new Product();
            $fh = fopen($_FILES['csv']['tmp_name'], 'r');
            // Assume header: name,sku,barcode,price,stock
            $first = true;
            while (($row = fgetcsv($fh)) !== false) {
                if ($first) { $first = false; continue; }
                [$name, $sku, $barcode, $price, $stock] = $row;
                $p->create([
                    'store_id' => $storeId,
                    'name' => $name,
                    'sku' => $sku,
                    'barcode' => $barcode,
                    'price' => (float)$price,
                    'tax_rate' => (float)(Config::get('defaults')['tax_rate'] ?? 0),
                    'stock' => (int)$stock,
                ]);
            }
            fclose($fh);
            Response::redirect('/products');
        } else {
            view('products/upload');
        }
    }
}
