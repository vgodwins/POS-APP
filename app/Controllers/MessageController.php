<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\Message;
use App\Models\User;

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
        try { if ($storeId) { $users = (new User())->allByStore((int)$storeId); } } catch (\Throwable $e) { $users = []; }
        view('messages/create', ['users' => $users, 'error' => null]);
    }

    public function save(Request $req): void {
        $this->ensureAuthenticated();
        $csrf = $req->body['csrf'] ?? null;
        if (!\verify_csrf($csrf)) { view('messages/create', ['error' => 'Invalid session']); return; }
        $storeId = Auth::effectiveStoreId() ?? null;
        if (Auth::isWriteLocked($storeId)) { view('messages/create', ['error' => 'Store is locked or outside active hours']); return; }
        $user = Auth::user();
        $recipientId = ($req->body['recipient_id'] ?? '') !== '' ? (int)$req->body['recipient_id'] : null;
        $subject = trim((string)($req->body['subject'] ?? ''));
        $body = trim((string)($req->body['body'] ?? ''));
        if ($body === '') { view('messages/create', ['error' => 'Message body is required', 'users' => (new User())->allByStore((int)($storeId ?? 0))]); return; }
        try {
            (new Message())->create([
                'store_id' => $storeId,
                'sender_id' => (int)($user['id'] ?? 0),
                'recipient_id' => $recipientId,
                'subject' => $subject,
                'body' => $body,
            ]);
            \flash('Message sent successfully', 'success');
            Response::redirect('/messages');
        } catch (\Throwable $e) {
            \flash('Failed to send message', 'error');
            view('messages/create', ['error' => 'Could not send message', 'users' => (new User())->allByStore((int)($storeId ?? 0))]);
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
}
?>
