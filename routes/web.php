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
use App\Controllers\UserController;

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

// User management (Admin)
$router->get('/users', [UserController::class, 'index']);
$router->any('/users/create', [UserController::class, 'create']);
$router->post('/users/save', [UserController::class, 'save']);
$router->any('/users/edit', [UserController::class, 'edit']);
$router->post('/users/update', [UserController::class, 'update']);
$router->post('/users/delete', [UserController::class, 'delete']);

// Products
$router->get('/products', [ProductController::class, 'index']);
$router->any('/products/create', [ProductController::class, 'create']);
$router->post('/products/save', [ProductController::class, 'save']);
$router->any('/products/upload', [ProductController::class, 'uploadCsv']);

// Vouchers
$router->get('/vouchers', [VoucherController::class, 'index']);
$router->any('/vouchers/create', [VoucherController::class, 'create']);
$router->post('/vouchers/save', [VoucherController::class, 'save']);

// Voucher API
$router->get('/vouchers/validate', [VoucherController::class, 'validate']);

// POS Sales
$router->any('/pos', [SaleController::class, 'create']);
$router->post('/pos/checkout', [SaleController::class, 'checkout']);
$router->get('/sales/receipt', [SaleController::class, 'receipt']);

// Reports
$router->get('/reports/sales', [ReportController::class, 'sales']);
$router->get('/reports/sales/export.csv', [ReportController::class, 'exportCsv']);

$router->get('/expenses', [ExpenseController::class, 'index']);
$router->any('/expenses/create', [ExpenseController::class, 'create']);
$router->post('/expenses/save', [ExpenseController::class, 'save']);
$router->get('/settings', [SettingsController::class, 'index']);
$router->post('/settings/save', [SettingsController::class, 'save']);