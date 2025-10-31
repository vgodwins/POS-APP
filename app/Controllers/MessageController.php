<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\Message;
use App\Models\User;
use App\Models\Customer;
use App\Services\Mailer;

class MessageController {
    private function ensureAuthenticated(): void {
        if (!Auth::check()) { Response::redirect('/'); }
    }

    public function index(Request $req): void {
        $this->ensureAuthenticated();
        $user = Auth::user();
        $storeId = Auth::effectiveStoreId() ?? null;
        $unreadOnly = (bool)($req->query['unread_only'] ?? false);
        $m = new Message();
        $messages = $m->allForUser((int)($user['id'] ?? 0), $storeId);
        if ($unreadOnly) { $messages = array_values(array_filter($messages, fn($r) => strtolower($r['status'] ?? '') === 'unread')); }
        view('messages/index', ['messages' => $messages, 'unreadOnly' => $unreadOnly]);
    }

    public function create(Request $req): void {
        $this->ensureAuthenticated();
        if ($req->method === 'POST') { $this->save($req); return; }
        $storeId = Auth::effectiveStoreId() ?? null;
        $users = [];
        $customers = [];
        try {
            if ($storeId) {
                $users = (new User())->allByStore((int)$storeId);
                $customers = (new Customer())->allByStore((int)$storeId);
            }
        } catch (\Throwable $e) { $users = []; $customers = []; }
        view('messages/create', ['users' => $users, 'customers' => $customers, 'error' => null]);
    }

    public function save(Request $req): void {
        $this->ensureAuthenticated();
        $csrf = $req->body['csrf'] ?? null;
        if (!\verify_csrf($csrf)) { view('messages/create', ['error' => 'Invalid session']); return; }
        $storeId = Auth::effectiveStoreId() ?? null;
        if (Auth::isWriteLocked($storeId)) { view('messages/create', ['error' => 'Store is locked or outside active hours']); return; }
        $user = Auth::user();
        $recipientId = ($req->body['recipient_id'] ?? '') !== '' ? (int)$req->body['recipient_id'] : null;
        $recipientCustomerId = ($req->body['recipient_customer_id'] ?? '') !== '' ? (int)$req->body['recipient_customer_id'] : null;
        $subject = trim((string)($req->body['subject'] ?? ''));
        $body = trim((string)($req->body['body'] ?? ''));
        if ($body === '') {
            view('messages/create', ['error' => 'Message body is required', 'users' => (new User())->allByStore((int)($storeId ?? 0)), 'customers' => (new Customer())->allByStore((int)($storeId ?? 0))]);
            return;
        }
        // Only admin/owner/manager can message customers
        if ($recipientCustomerId !== null && !(Auth::hasRole('admin') || Auth::hasRole('owner') || Auth::hasRole('manager'))) {
            view('messages/create', ['error' => 'You do not have permission to message customers', 'users' => (new User())->allByStore((int)($storeId ?? 0)), 'customers' => (new Customer())->allByStore((int)($storeId ?? 0))]);
            return;
        }
        try {
            $msgId = (new Message())->create([
                'store_id' => $storeId,
                'sender_id' => (int)($user['id'] ?? 0),
                'recipient_id' => $recipientId,
                'recipient_customer_id' => $recipientCustomerId,
                'parent_id' => null,
                'subject' => $subject,
                'body' => $body,
            ]);
            // If customer recipient, send email notification and consolidate status
            $emailAttempted = false; $emailOk = null; $emailInfo = null;
            if ($recipientCustomerId !== null) {
                $emailAttempted = true;
                try {
                    $customer = (new Customer())->find((int)$recipientCustomerId);
                    $toEmail = $customer['email'] ?? null;
                    if ($toEmail) {
                        $mailer = new Mailer();
                        $emailOk = $mailer->send($toEmail, $subject !== '' ? $subject : 'New message from store', nl2br(htmlspecialchars($body)));
                        if (!$emailOk) { $emailInfo = 'email could not be sent'; }
                    } else {
                        $emailOk = false; $emailInfo = 'customer has no email on file';
                    }
                } catch (\Throwable $e) { $emailOk = false; $emailInfo = 'email error occurred'; }
            }
            if ($emailAttempted) {
                if ($emailOk) { \flash('Message sent successfully', 'success'); }
                else { \flash('Message sent successfully; ' . (string)$emailInfo, 'warning'); }
            } else {
                \flash('Message sent successfully', 'success');
            }
            Response::redirect('/messages');
        } catch (\Throwable $e) {
            \flash('Failed to send message', 'error');
            view('messages/create', ['error' => 'Could not send message', 'users' => (new User())->allByStore((int)($storeId ?? 0)), 'customers' => (new Customer())->allByStore((int)($storeId ?? 0))]);
        }
    }

    public function markRead(Request $req): void {
        $this->ensureAuthenticated();
        $csrf = $req->body['csrf'] ?? null;
        if (!\verify_csrf($csrf)) { Response::redirect('/messages'); return; }
        $id = (int)($req->body['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/messages'); return; }
        $m = new Message();
        $msg = $m->find($id);
        $uid = (int)(Auth::user()['id'] ?? 0);
        if (!$msg) { Response::redirect('/messages'); return; }
        // Allow marking read if broadcast or assigned to current user
        if (($msg['recipient_id'] ?? null) !== null && (int)$msg['recipient_id'] !== $uid) { Response::redirect('/messages'); return; }
        try { $m->markRead($id); } catch (\Throwable $e) { /* ignore */ }
        Response::redirect('/messages');
    }

    public function view(Request $req): void {
        $this->ensureAuthenticated();
        $m = new Message();
        if ($req->method === 'POST') {
            $csrf = $req->body['csrf'] ?? null;
            if (!\verify_csrf($csrf)) { Response::redirect('/messages'); return; }
            $id = (int)($req->body['id'] ?? 0);
            $body = trim((string)($req->body['body'] ?? ''));
            if ($id <= 0 || $body === '') { Response::redirect('/messages'); return; }
            $orig = $m->find($id);
            if (!$orig) { Response::redirect('/messages'); return; }
            $rootId = $m->resolveRootId($orig);
            $senderId = (int)(Auth::user()['id'] ?? 0);
            $subject = 'Re: ' . trim((string)($orig['subject'] ?? ''));
            $recipientCustomerId = $orig['recipient_customer_id'] ?? null;
            $recipientId = null;
            if ($recipientCustomerId === null) {
                // Internal: reply to the other party (original sender if broadcast or recipient is current user)
                $recipientId = (int)($orig['sender_id'] ?? 0);
                if ($recipientId <= 0) { Response::redirect('/messages'); return; }
            }
            try {
                $newId = $m->create([
                    'store_id' => Auth::effectiveStoreId() ?? null,
                    'sender_id' => $senderId,
                    'recipient_id' => $recipientId,
                    'recipient_customer_id' => $recipientCustomerId,
                    'parent_id' => $rootId,
                    'subject' => $subject,
                    'body' => $body,
                ]);
                // If replying to customer, send email and consolidate status
                $emailAttempted = false; $emailOk = null; $emailInfo = null;
                if ($recipientCustomerId !== null) {
                    $emailAttempted = true;
                    try {
                        $customer = (new Customer())->find((int)$recipientCustomerId);
                        $toEmail = $customer['email'] ?? null;
                        if ($toEmail) {
                            $mailer = new Mailer();
                            $emailOk = $mailer->send($toEmail, $subject, nl2br(htmlspecialchars($body)));
                            if (!$emailOk) { $emailInfo = 'email could not be sent'; }
                        } else {
                            $emailOk = false; $emailInfo = 'customer has no email on file';
                        }
                    } catch (\Throwable $e) { $emailOk = false; $emailInfo = 'email error occurred'; }
                }
                if ($emailAttempted) {
                    if ($emailOk) { \flash('Reply sent', 'success'); }
                    else { \flash('Reply sent; ' . (string)$emailInfo, 'warning'); }
                } else {
                    \flash('Reply sent', 'success');
                }
                Response::redirect('/messages/view?id=' . (int)$rootId);
            } catch (\Throwable $e) {
                \flash('Failed to send reply', 'error');
                Response::redirect('/messages');
            }
            return;
        }
        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/messages'); return; }
        $msg = $m->find($id);
        if (!$msg) { Response::redirect('/messages'); return; }
        $rootId = $m->resolveRootId($msg);
        $thread = $m->thread($rootId);
        view('messages/view', ['root' => $thread[0] ?? $msg, 'thread' => $thread]);
    }
}
?>
