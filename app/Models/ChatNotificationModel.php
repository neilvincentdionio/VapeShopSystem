<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatNotificationModel extends Model
{
    protected $table = 'chat_notifications';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = [
        'conversation_id',
        'user_id',
        'message',
        'is_read',
    ];

    public function notifyUsers(int $conversationId, array $userIds, string $message): void
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds), static fn (int $id): bool => $id > 0)));
        if ($userIds === []) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $rows = [];
        foreach ($userIds as $userId) {
            $rows[] = [
                'conversation_id' => $conversationId,
                'user_id' => $userId,
                'message' => mb_substr($message, 0, 255),
                'is_read' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->insertBatch($rows);
    }

    public function countUnread(int $userId): int
    {
        return $this->where('user_id', $userId)
            ->where('is_read', 0)
            ->countAllResults();
    }

    public function markConversationRead(int $conversationId, int $userId): bool
    {
        return $this->builder()
            ->where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->set([
                'is_read' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ])
            ->update();
    }
}
