<?php
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\StoreController;
use App\Controllers\ProductController;
use App\Controllers\VoucherController;
use App\Controllers\SaleController;
use App\Controllers\ReportController;
use App\Controllers\ExpenseController;
use App\Controllers\SettingsController;

/* @var $router Router */

$router->any('/', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'doLogin']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->any('/register', [AuthController::class, 'register']);
$router->post('/register/save', [AuthController::class, 'saveRegister']);

$router->get('/dashboard', [DashboardController::class, 'index']);

// Store management (Admin)
$router->get('/stores', [StoreController::class, 'index']);
$router->any('/stores/create', [StoreController::class, 'create']);
$router->post('/stores/save', [StoreController::class, 'save']);

// Products
$router->get('/products', [ProductController::class, 'index']);
$router->any('/products/create', [ProductController::class, 'create']);
$router->post('/products/save', [ProductController::class, 'save']);
$router->any('/products/upload', [ProductController::class, 'uploadCsv']);

// Vouchers
$router->get('/vouchers', [VoucherController::class, 'index']);
$router->any('/vouchers/create', [VoucherController::class, 'create']);
$router->post('/vouchers/save', [VoucherController::class, 'save']);

// POS Sales
$router->any('/pos', [SaleController::class, 'create']);
$router->post('/pos/checkout', [SaleController::class, 'checkout']);
$router->get('/sales/receipt', [SaleController::class, 'receipt']);
$router->get('/reports/sales', [ReportController::class, 'sales']);
$router->get('/expenses', [ExpenseController::class, 'index']);
$router->any('/expenses/create', [ExpenseController::class, 'create']);
$router->post('/expenses/save', [ExpenseController::class, 'save']);
$router->get('/settings', [SettingsController::class, 'index']);
$router->post('/settings/save', [SettingsController::class, 'save']);