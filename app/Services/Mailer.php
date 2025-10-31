<?php
namespace App\Services;

use App\Core\Config;

class Mailer {
    public function send(string $to, string $subject, string $htmlBody): bool {
        $cfg = Config::get('mail') ?? [];
        $from = $cfg['from'] ?? 'noreply@example.com';
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . $from;
        $headers[] = 'Reply-To: ' . $from;

        $ok = false;
        try {
            // Try native mail() first (configured via php.ini). On failure, still log.
            $ok = @mail($to, $subject, $htmlBody, implode("\r\n", $headers));
        } catch (\Throwable $e) {
            $ok = false;
        }
        $this->logAttempt($to, $subject, $ok);
        return $ok;
    }

    private function logAttempt(string $to, string $subject, bool $ok): void {
        $dir = __DIR__ . '/../../storage/logs';
        if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
        $file = $dir . '/mail.log';
        $line = sprintf("[%s] to=%s subject=%s status=%s\n", date('c'), $to, $subject, $ok ? 'SENT' : 'FAILED');
        @file_put_contents($file, $line, FILE_APPEND);
    }
}
?>
