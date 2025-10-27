<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\Expense;

class ExpenseController {
    public function index(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $storeId = Auth::user()['store_id'] ?? null;
        $model = new Expense();
        $list = $model->all($storeId);
        $summary = $model->summary($storeId);
        view('expenses/index', ['expenses' => $list, 'summary' => $summary]);
    }
    public function create(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        view('expenses/create');
    }
    public function save(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { view('expenses/create', ['error' => 'Invalid session']); return; }
        $storeId = Auth::user()['store_id'] ?? null;
        $cat = trim($req->body['category'] ?? 'General');
        $amount = (float)($req->body['amount'] ?? 0);
        $note = trim($req->body['note'] ?? '');
        if ($amount <= 0) { view('expenses/create', ['error' => 'Amount must be positive']); return; }
        (new Expense())->create(['store_id' => $storeId, 'category' => $cat, 'amount' => $amount, 'note' => $note]);
        Response::redirect('/expenses');
    }
    public function edit(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/expenses'); return; }
        $ex = (new Expense())->find($id);
        // Scope to user's store
        $storeId = Auth::user()['store_id'] ?? null;
        if (!$ex || ($storeId && (int)$ex['store_id'] !== (int)$storeId)) { Response::redirect('/expenses'); return; }
        view('expenses/edit', ['expense' => $ex]);
    }
    public function update(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { Response::redirect('/expenses'); return; }
        $id = (int)($req->body['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/expenses'); return; }
        $cat = trim($req->body['category'] ?? 'General');
        $amount = (float)($req->body['amount'] ?? 0);
        $note = trim($req->body['note'] ?? '');
        if ($amount <= 0) { Response::redirect('/expenses'); return; }
        // Verify scope
        $ex = (new Expense())->find($id);
        $storeId = Auth::user()['store_id'] ?? null;
        if (!$ex || ($storeId && (int)$ex['store_id'] !== (int)$storeId)) { Response::redirect('/expenses'); return; }
        (new Expense())->update($id, ['category' => $cat, 'amount' => $amount, 'note' => $note]);
        Response::redirect('/expenses');
    }
    public function delete(Request $req): void {
        if (!Auth::check()) { Response::redirect('/'); }
        $csrf = $req->body['csrf'] ?? null;
        if (!verify_csrf($csrf)) { Response::redirect('/expenses'); return; }
        $id = (int)($req->body['id'] ?? 0);
        if ($id <= 0) { Response::redirect('/expenses'); return; }
        $ex = (new Expense())->find($id);
        $storeId = Auth::user()['store_id'] ?? null;
        if (!$ex || ($storeId && (int)$ex['store_id'] !== (int)$storeId)) { Response::redirect('/expenses'); return; }
        (new Expense())->delete($id);
        Response::redirect('/expenses');
    }
}
