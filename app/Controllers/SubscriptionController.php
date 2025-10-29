<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\Config;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\PaystackClient;

class SubscriptionController {
    private function ensureOwnerOrAdmin(): void {
        if (!Auth::check() || !(Auth::hasRole('admin') || Auth::hasRole('owner'))) {
            Response::redirect('/');
        }
    }

    public function index(Request $req): void {
        $this->ensureOwnerOrAdmin();
        $level = (string)($req->query['level'] ?? 'store');
        // Admin can view app-level; owners default to store-level
        if (!Auth::hasRole('admin')) { $level = 'store'; }
        $plans = (new Plan())->allActive();
        // Filter by level for display
        $plans = array_values(array_filter($plans, fn($p) => ($p['level'] ?? '') === $level));
        view('subscriptions/plans', ['plans' => $plans, 'level' => $level]);
    }

    public function startPaystack(Request $req): void {
        $this->ensureOwnerOrAdmin();
        if (!isset($req->body['csrf']) || !\verify_csrf($req->body['csrf'] ?? null)) {
            \flash('Invalid session', 'error'); Response::redirect('/subscriptions'); return;
        }
        $planCode = (string)($req->body['plan_code'] ?? '');
        $level = (string)($req->body['level'] ?? 'store');
        if (!Auth::hasRole('admin')) { $level = 'store'; }
        $plan = (new Plan())->findByCode($planCode);
        if (!$plan) { \flash('Plan not found', 'error'); Response::redirect('/subscriptions'); return; }
        if (($plan['level'] ?? '') !== $level) { \flash('Invalid plan level', 'error'); Response::redirect('/subscriptions'); return; }

        $user = Auth::user();
        $sid = Auth::effectiveStoreId();
        if ($level === 'store' && !$sid) { \flash('No store context for subscription', 'error'); Response::redirect('/subscriptions'); return; }

        $amount = (float)$plan['amount'];
        $currency = (string)($plan['currency_code'] ?? 'NGN');
        $period = (string)$plan['period'];
        $reference = 'SUB-' . strtoupper(bin2hex(random_bytes(6)));
        $subModel = new Subscription();
        try {
            $subModel->createPending([
                'user_id' => (int)$user['id'],
                'store_id' => $level === 'store' ? (int)$sid : null,
                'plan_code' => $plan['code'],
                'level' => $level,
                'period' => $period,
                'amount' => $amount,
                'currency_code' => $currency,
                'gateway' => 'paystack',
                'reference' => $reference,
            ]);
        } catch (\Throwable $e) {
            \flash('Could not start subscription. Check migrations.', 'error'); Response::redirect('/subscriptions'); return;
        }

        $client = new PaystackClient();
        if (!$client->isConfigured()) { \flash('Paystack not configured. Set keys in config.', 'error'); Response::redirect('/subscriptions'); return; }

        $callbackDefault = (Config::get('paystack')['callback_url'] ?? (Config::get('app')['url'] ?? 'http://localhost:8000') . '/subscriptions/paystack/callback');
        $email = (string)($user['email'] ?? 'user@example.com');
        // Paystack amount is in kobo (for NGN) => multiply by 100
        $amountMinor = (int)round($amount * 100);
        $metadata = [
            'plan_code' => $plan['code'],
            'level' => $level,
            'period' => $period,
            'store_id' => $sid,
            'user_id' => (int)$user['id'],
        ];
        try {
            $resp = $client->initializeTransaction([
                'email' => $email,
                'amount' => $amountMinor,
                'currency' => $currency,
                'reference' => $reference,
                'callback_url' => $callbackDefault,
                'metadata' => $metadata,
            ]);
            $authUrl = (string)($resp['data']['authorization_url'] ?? '');
            if (!$authUrl) { throw new \RuntimeException('Missing authorization_url'); }
            Response::redirect($authUrl);
        } catch (\Throwable $e) {
            \flash('Failed to initialize Paystack: ' . $e->getMessage(), 'error');
            Response::redirect('/subscriptions');
        }
    }

    public function callbackPaystack(Request $req): void {
        $this->ensureOwnerOrAdmin();
        $reference = (string)($req->query['reference'] ?? '');
        if ($reference === '') { \flash('Missing reference', 'error'); Response::redirect('/subscriptions'); return; }
        $client = new PaystackClient();
        if (!$client->isConfigured()) { \flash('Paystack not configured', 'error'); Response::redirect('/subscriptions'); return; }
        try {
            $resp = $client->verifyTransaction($reference);
            $status = (string)($resp['data']['status'] ?? '');
            if (strtolower($status) === 'success') {
                $sub = (new Subscription())->findByReference($reference);
                $period = (string)($sub['period'] ?? 'monthly');
                $start = date('Y-m-d H:i:s');
                $end = date('Y-m-d H:i:s', strtotime($period === 'yearly' ? '+1 year' : '+1 month'));
                (new Subscription())->updateStatusByReference($reference, 'active', $start, $end);
                \flash('Subscription activated successfully', 'success');
            } else {
                (new Subscription())->updateStatusByReference($reference, 'failed');
                \flash('Subscription failed or canceled', 'error');
            }
        } catch (\Throwable $e) {
            \flash('Verification error: ' . $e->getMessage(), 'error');
        }
        Response::redirect('/subscriptions');
    }
}

