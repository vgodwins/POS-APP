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
use App\Controllers\CustomerController;
use App\Controllers\CategoryController;

/* @var $router Router */

$router->any('/', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'doLogin']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->any('/register', [AuthController::class, 'register']);
$router->post('/register/save', [AuthController::class, 'saveRegister']);

// Password reset
$router->any('/password/forgot', [AuthController::class, 'forgot']);
$router->post('/password/send_reset', [AuthController::class, 'sendReset']);
$router->any('/password/reset', [AuthController::class, 'reset']);
$router->post('/password/do_reset', [AuthController::class, 'doReset']);

$router->get('/dashboard', [DashboardController::class, 'index']);
// Admin store context switcher
$router->post('/admin/store/switch', [DashboardController::class, 'switchStore']);

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
$router->any('/vouchers/edit', [VoucherController::class, 'edit']);
$router->post('/vouchers/update', [VoucherController::class, 'update']);
$router->any('/vouchers/view', [VoucherController::class, 'view']);
$router->any('/vouchers/bulk', [VoucherController::class, 'bulk']);
$router->post('/vouchers/bulk_save', [VoucherController::class, 'bulkSave']);
// Voucher cards print and verification
$router->get('/vouchers/print_cards', [VoucherController::class, 'printCards']);
$router->get('/vouchers/verify', [VoucherController::class, 'verifyPage']);
$router->get('/vouchers/scan', [VoucherController::class, 'scan']);

// Voucher API
$router->get('/vouchers/validate', [VoucherController::class, 'validate']);

// POS Sales
$router->any('/pos', [SaleController::class, 'create']);
$router->post('/pos/checkout', [SaleController::class, 'checkout']);
$router->get('/sales/receipt', [SaleController::class, 'receipt']);
$router->get('/sales/invoice', [SaleController::class, 'invoice']);

// Reports
$router->get('/reports/sales', [ReportController::class, 'sales']);
$router->any('/reports/sales/filter', [ReportController::class, 'filter']);
$router->get('/reports/sales/export.csv', [ReportController::class, 'exportCsv']);
// General reports
$router->get('/reports/general', [ReportController::class, 'general']);

$router->get('/expenses', [ExpenseController::class, 'index']);
$router->any('/expenses/create', [ExpenseController::class, 'create']);
$router->post('/expenses/save', [ExpenseController::class, 'save']);
$router->any('/expenses/edit', [ExpenseController::class, 'edit']);
$router->post('/expenses/update', [ExpenseController::class, 'update']);
$router->post('/expenses/delete', [ExpenseController::class, 'delete']);
$router->get('/settings', [SettingsController::class, 'index']);
$router->post('/settings/save', [SettingsController::class, 'save']);
$router->post('/settings/upload_logo', [SettingsController::class, 'uploadLogo']);
$router->post('/settings/clear_data', [SettingsController::class, 'clearData']);

// Product editing
$router->any('/products/edit', [ProductController::class, 'edit']);
$router->post('/products/update', [ProductController::class, 'update']);

// Customers
$router->any('/customers/create', [CustomerController::class, 'create']);
$router->post('/customers/save', [CustomerController::class, 'save']);

// Categories
$router->any('/categories/create', [CategoryController::class, 'create']);
$router->post('/categories/save', [CategoryController::class, 'save']);
