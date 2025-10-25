<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\DB;
use App\Models\User;
use App\Models\Store;

class AuthController {
    public function login(Request $req): void {
        if (Auth::check()) { Response::redirect('/dashboard'); }
        view('auth/login');
    }
    public function doLogin(Request $req): void {
        $email = trim($req->body['email'] ?? '');
        $password = (string)($req->body['password'] ?? '');
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) {
            view('auth/login', ['error' => 'Invalid session, try again.']);
            return;
        }
        $userModel = new User();
        $user = $userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            view('auth/login', ['error' => 'Invalid credentials']);
            return;
        }
        Auth::login([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'store_id' => $user['store_id'],
            'roles' => $user['roles'],
        ]);
        Response::redirect('/dashboard');
    }
    public function logout(Request $req): void {
        Auth::logout();
        Response::redirect('/');
    }
    public function register(Request $req): void {
        view('auth/register');
    }
    public function saveRegister(Request $req): void {
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { view('auth/register', ['error' => 'Invalid session']); return; }
        $name = trim($req->body['name'] ?? '');
        $email = trim($req->body['email'] ?? '');
        $password = (string)($req->body['password'] ?? '');
        if (!$name || !$email || !$password) {
            view('auth/register', ['error' => 'All fields are required']); return;
        }
        $pdo = DB::conn();
        $count = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $role = $count === 0 ? 'admin' : 'owner';

        $storeId = null;
        if ($role === 'owner') {
            // Auto-create a store for the owner
            $storeModel = new Store();
            $storeId = $storeModel->create([
                'name' => $name . "'s Store",
                'currency_code' => 'NGN',
                'currency_symbol' => '₦',
                'tax_rate' => 0.075,
                'theme' => 'light',
            ]);
        }

        $userModel = new User();
        $userId = $userModel->create(['name' => $name, 'email' => $email, 'password' => $password], $role, $storeId);
        view('auth/login', ['success' => 'Account created. Please login.']);
    }
}