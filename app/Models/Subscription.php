<?php
namespace App\Models;

class Subscription extends BaseModel {
    public function createPending(array $data): int {
        $st = $this->db->prepare('INSERT INTO subscriptions(user_id, store_id, plan_code, level, period, amount, currency_code, gateway, reference, status) VALUES(:user_id,:store_id,:plan_code,:level,:period,:amount,:currency_code,:gateway,:reference,:status)');
        $st->execute([
            'user_id' => $data['user_id'],
            'store_id' => $data['store_id'] ?? null,
            'plan_code' => $data['plan_code'],
            'level' => $data['level'],
            'period' => $data['period'],
            'amount' => (float)$data['amount'],
            'currency_code' => $data['currency_code'] ?? 'NGN',
            'gateway' => $data['gateway'] ?? 'paystack',
            'reference' => $data['reference'],
            'status' => 'pending',
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function findByReference(string $ref): ?array {
        $st = $this->db->prepare('SELECT * FROM subscriptions WHERE reference = ?');
        $st->execute([$ref]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function updateStatusByReference(string $ref, string $status, ?string $startsAt = null, ?string $endsAt = null): void {
        $set = ['status = :status'];
        $params = ['reference' => $ref, 'status' => $status];
        if ($startsAt !== null) { $set[] = 'starts_at = :starts_at'; $params['starts_at'] = $startsAt; }
        if ($endsAt !== null) { $set[] = 'ends_at = :ends_at'; $params['ends_at'] = $endsAt; }
        $sql = 'UPDATE subscriptions SET ' . implode(', ', $set) . ' WHERE reference = :reference';
        $st = $this->db->prepare($sql);
        $st->execute($params);
    }
}

