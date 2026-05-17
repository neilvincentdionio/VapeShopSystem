<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = [
        'user_id',
        'role',
        'category',
        'type',
        'title',
        'message',
        'link',
        'related_type',
        'related_id',
        'is_read',
        'read_at',
    ];

    public function createForUsers(array $userRows, array $payload): void
    {
        $rows = [];
        $now = date('Y-m-d H:i:s');
        $seen = [];

        foreach ($userRows as $user) {
            $userId = (int) ($user['id'] ?? $user['user_id'] ?? 0);
            if ($userId <= 0 || isset($seen[$userId])) {
                continue;
            }

            $seen[$userId] = true;
            $rows[] = [
                'user_id' => $userId,
                'role' => strtolower((string) ($user['role'] ?? $payload['role'] ?? 'customer')),
                'category' => mb_substr((string) ($payload['category'] ?? 'general'), 0, 40),
                'type' => mb_substr((string) ($payload['type'] ?? 'info'), 0, 60),
                'title' => mb_substr((string) ($payload['title'] ?? 'Notification'), 0, 160),
                'message' => mb_substr((string) ($payload['message'] ?? ''), 0, 500),
                'link' => isset($payload['link']) && $payload['link'] !== '' ? mb_substr((string) $payload['link'], 0, 255) : null,
                'related_type' => isset($payload['related_type']) && $payload['related_type'] !== '' ? mb_substr((string) $payload['related_type'], 0, 60) : null,
                'related_id' => isset($payload['related_id']) && (int) $payload['related_id'] > 0 ? (int) $payload['related_id'] : null,
                'is_read' => 0,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            $this->insertBatch($rows);
        }
    }

    public function getRecentForUser(int $userId, string $role, int $limit = 10): array
    {
        if ($userId <= 0 || $role === '') {
            return [];
        }

        $builder = $this->where('user_id', $userId)
            ->where('role', strtolower($role))
            ->orderBy('created_at', 'DESC')
            ->limit($limit);

        if (strtolower($role) === 'customer') {
            $builder->where('type !=', 'delivery_proof');
        }

        return $builder->findAll();
    }

    public function countUnreadForUser(int $userId, string $role): int
    {
        if ($userId <= 0 || $role === '') {
            return 0;
        }

        $builder = $this->where('user_id', $userId)
            ->where('role', strtolower($role))
            ->where('is_read', 0);

        if (strtolower($role) === 'customer') {
            $builder->where('type !=', 'delivery_proof');
        }

        return $builder->countAllResults();
    }

    public function markReadForUser(int $notificationId, int $userId, string $role): bool
    {
        if ($notificationId <= 0 || $userId <= 0 || $role === '') {
            return false;
        }

        return $this->builder()
            ->where('id', $notificationId)
            ->where('user_id', $userId)
            ->where('role', strtolower($role))
            ->set([
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ])
            ->update();
    }

    public function markAllReadForUser(int $userId, string $role): bool
    {
        if ($userId <= 0 || $role === '') {
            return false;
        }

        return $this->builder()
            ->where('user_id', $userId)
            ->where('role', strtolower($role))
            ->where('is_read', 0)
            ->set([
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ])
            ->update();
    }
}
