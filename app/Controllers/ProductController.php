<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Auth;
use App\Core\Response;
use App\Core\Config;
use App\Models\Product;

class ProductController {
    public function index(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $p = new Product();
        $list = $p->all(Auth::user()['store_id'] ?? null);
        view('products/index', ['products' => $list]);
    }
    public function create(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        view('products/create');
    }
    public function save(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { view('products/create', ['error' => 'Invalid session']); return; }
        $data = [
            'store_id' => Auth::user()['store_id'] ?? null,
            'name' => trim($req->body['name'] ?? ''),
            'sku' => trim($req->body['sku'] ?? ''),
            'barcode' => trim($req->body['barcode'] ?? ''),
            'price' => (float)($req->body['price'] ?? 0),
            'tax_rate' => (float)(Config::get('defaults')['tax_rate'] ?? 0),
            'stock' => (int)($req->body['stock'] ?? 0),
        ];
        $p = new Product();
        $p->create($data);
        Response::redirect('/products');
    }
    public function uploadCsv(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        if (($req->method === 'POST') && isset($_FILES['csv']) && $_FILES['csv']['error'] === UPLOAD_ERR_OK) {
            $storeId = Auth::user()['store_id'] ?? null;
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