<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\DB;
use App\Core\Config;
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
        // Record login activity timestamp
        try {
            $pdo = DB::conn();
            $st = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
            $st->execute([$user['id']]);
        } catch (\Throwable $e) {
            // ignore activity write failures
        }
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
        
        try {
            $userModel = new User();
            $existing = $userModel->findByEmail($email);
            if ($existing) { view('auth/register', ['error' => 'Email already exists']); return; }

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

            $userId = $userModel->create(['name' => $name, 'email' => $email, 'password' => $password], $role, $storeId);
            view('auth/login', ['success' => 'Account created. Please login.']);
        } catch (\Throwable $e) {
            view('auth/register', ['error' => 'Registration failed. Please check database setup and try again.']);
        }
    }

    // Show forgot password form
    public function forgot(Request $req): void {
        if (Auth::check()) { Response::redirect('/dashboard'); }
        view('auth/forgot');
    }

    // Handle reset request: generate token and provide reset link.
    public function sendReset(Request $req): void {
        if (Auth::check()) { Response::redirect('/dashboard'); }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { view('auth/forgot', ['error' => 'Invalid session']); return; }
        $email = trim($req->body['email'] ?? '');
        $env = strtolower((string)(Config::get('app')['env'] ?? 'development'));
        $appUrl = (string)(Config::get('app')['url'] ?? '');
        try {
            $pdo = DB::conn();
            $user = (new User())->findByEmail($email);
            if ($user) {
                // Remove previous tokens for this user
                $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$user['id']]);
                $token = bin2hex(random_bytes(24));
                $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
                $st = $pdo->prepare('INSERT INTO password_resets(user_id, token, expires_at) VALUES(?,?,?)');
                $st->execute([$user['id'], $token, $expires]);
                $link = rtrim($appUrl ?: '', '/') . '/password/reset?token=' . urlencode($token);
                if ($env !== 'production') {
                    // In dev, expose the link directly to ease testing
                    view('auth/forgot', ['success' => 'Reset link generated.', 'reset_link' => $link]);
                    return;
                }
                // In production, show generic message (mail sending can be integrated later)
            }
            view('auth/forgot', ['success' => 'If the email exists, a reset link was sent.']);
        } catch (\Throwable $e) {
            view('auth/forgot', ['error' => 'Could not process reset.']);
        }
    }

    // Show reset form if token is valid
    public function reset(Request $req): void {
        if (Auth::check()) { Response::redirect('/dashboard'); }
        $token = trim($req->query['token'] ?? '');
        if ($token === '') { view('auth/reset', ['error' => 'Invalid reset link']); return; }
        try {
            $pdo = DB::conn();
            $st = $pdo->prepare('SELECT pr.user_id, pr.expires_at, u.email FROM password_resets pr INNER JOIN users u ON u.id = pr.user_id WHERE pr.token = ? AND pr.expires_at > NOW() LIMIT 1');
            $st->execute([$token]);
            $row = $st->fetch(\PDO::FETCH_ASSOC);
            if (!$row) { view('auth/reset', ['error' => 'Invalid or expired reset link']); return; }
            view('auth/reset', ['token' => $token, 'email' => $row['email'] ?? '']);
        } catch (\Throwable $e) {
            view('auth/reset', ['error' => 'Could not load reset form']);
        }
    }

    // Perform the password reset
    public function doReset(Request $req): void {
        if (Auth::check()) { Response::redirect('/dashboard'); }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { view('auth/reset', ['error' => 'Invalid session']); return; }
        $token = trim($req->body['token'] ?? '');
        $password = (string)($req->body['password'] ?? '');
        $confirm = (string)($req->body['confirm'] ?? '');
        if ($token === '' || $password === '' || $confirm === '') { view('auth/reset', ['error' => 'All fields are required']); return; }
        if ($password !== $confirm) { view('auth/reset', ['error' => 'Passwords do not match', 'token' => $token]); return; }
        if (strlen($password) < 6) { view('auth/reset', ['error' => 'Password must be at least 6 characters', 'token' => $token]); return; }
        try {
            $pdo = DB::conn();
            $st = $pdo->prepare('SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1');
            $st->execute([$token]);
            $row = $st->fetch(\PDO::FETCH_ASSOC);
            if (!$row || !($row['user_id'] ?? null)) { view('auth/reset', ['error' => 'Invalid or expired reset link']); return; }
            $userId = (int)$row['user_id'];
            $pdo->prepare('UPDATE users SET password = :pwd WHERE id = :id')->execute([
                'pwd' => password_hash($password, PASSWORD_DEFAULT),
                'id' => $userId,
            ]);
            $pdo->prepare('DELETE FROM password_resets WHERE token = ?')->execute([$token]);
            view('auth/login', ['success' => 'Password updated. Please login.']);
        } catch (\Throwable $e) {
            view('auth/reset', ['error' => 'Could not reset password', 'token' => $token]);
        }
    }
}
