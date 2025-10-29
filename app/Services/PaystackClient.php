<?php
namespace App\Services;

use App\Core\Config;

class PaystackClient {
    private string $secretKey;
    private string $publicKey;
    private string $baseUrl = 'https://api.paystack.co';

    public function __construct() {
        $cfg = Config::get('paystack') ?? [];
        $this->secretKey = (string)($cfg['secret_key'] ?? '');
        $this->publicKey = (string)($cfg['public_key'] ?? '');
    }

    public function isConfigured(): bool {
        return $this->secretKey !== '' && $this->publicKey !== '';
    }

    public function initializeTransaction(array $params): array {
        // Expected params: email, amount, currency, reference, callback_url, metadata
        return $this->request('POST', '/transaction/initialize', $params);
    }

    public function verifyTransaction(string $reference): array {
        return $this->request('GET', '/transaction/verify/' . rawurlencode($reference));
    }

    private function request(string $method, string $path, array $payload = []): array {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Paystack is not configured');
        }
        $url = $this->baseUrl . $path;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $headers = [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/json',
        ];
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $resp = curl_exec($ch);
        if ($resp === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('Paystack request failed: ' . $err);
        }
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        $data = json_decode($resp, true);
        if (!is_array($data)) { $data = ['status' => false, 'message' => 'Invalid JSON response']; }
        if ($status < 200 || $status >= 300) {
            $msg = $data['message'] ?? ('HTTP ' . $status);
            throw new \RuntimeException('Paystack API error: ' . $msg);
        }
        return $data;
    }
}

