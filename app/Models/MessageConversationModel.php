<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageConversationModel extends Model
{
    protected $table = 'message_conversations';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = [
        'customer_id',
        'assigned_admin_id',
        'order_id',
        'assigned_rider_id',
        'subject',
        'support_mode',
        'priority',
        'status',
        'last_message_at',
        'escalated_at',
    ];

    private string $messagesTable = 'conversation_messages';

    public function getOrCreateForCustomer(int $customerId): array
    {
        $conversation = $this->where('customer_id', $customerId)
            ->orderBy('id', 'DESC')
            ->first();

        if ($conversation) {
            return $conversation;
        }

        $now = date('Y-m-d H:i:s');
        $id = (int) $this->insert([
            'customer_id' => $customerId,
            'subject' => 'Customer Support',
            'support_mode' => 'bot',
            'status' => 'open',
            'last_message_at' => $now,
        ]);

        return $this->find($id) ?: [
            'id' => $id,
            'customer_id' => $customerId,
            'subject' => 'Customer Support',
            'status' => 'open',
            'support_mode' => 'bot',
            'last_message_at' => $now,
        ];
    }

    public function getAdminInbox(?string $status = null): array
    {
        $builder = $this->db->table($this->table . ' c')
            ->select(
                'c.*, u.name AS customer_name, u.email AS customer_email, ' .
                'r.name AS rider_name, o.reference_number, ' .
                'latest.message AS latest_message, latest.sender_role AS latest_sender_role, ' .
                'COALESCE(unread.unread_count, 0) AS unread_count',
                false
            )
            ->join('users u', 'u.id = c.customer_id', 'left')
            ->join('users r', 'r.id = c.assigned_rider_id', 'left')
            ->join('orders o', 'o.id = c.order_id', 'left')
            ->join(
                '(SELECT m1.conversation_id, m1.message, m1.sender_role
                  FROM conversation_messages m1
                  INNER JOIN (
                      SELECT conversation_id, MAX(id) AS latest_id
                      FROM conversation_messages
                      GROUP BY conversation_id
                  ) m2 ON m2.latest_id = m1.id) latest',
                'latest.conversation_id = c.id',
                'left',
                false
            )
            ->join(
                "(SELECT conversation_id, COUNT(*) AS unread_count
                  FROM conversation_messages
                  WHERE sender_role IN ('customer','rider') AND is_read = 0
                  GROUP BY conversation_id) unread",
                'unread.conversation_id = c.id',
                'left',
                false
            )
            ->where('c.support_mode', 'human')
            ->orderBy('c.last_message_at', 'DESC')
            ->orderBy('c.updated_at', 'DESC');

        if ($status !== null && in_array($status, ['open', 'pending', 'resolved'], true)) {
            $builder->where('c.status', $status);
        }

        return $builder->get()->getResultArray();
    }

    public function getAdminConversation(int $conversationId): ?array
    {
        $row = $this->db->table($this->table . ' c')
            ->select(
                'c.*, u.name AS customer_name, u.email AS customer_email, ' .
                'admin.name AS assigned_admin_name, r.name AS rider_name, o.reference_number, ' .
                'o.title AS order_title, o.status AS order_status',
                false
            )
            ->join('users u', 'u.id = c.customer_id', 'left')
            ->join('users admin', 'admin.id = c.assigned_admin_id', 'left')
            ->join('users r', 'r.id = c.assigned_rider_id', 'left')
            ->join('orders o', 'o.id = c.order_id', 'left')
            ->where('c.id', $conversationId)
            ->get()
            ->getRowArray();

        return is_array($row) ? $row : null;
    }

    public function touchConversation(int $conversationId, ?int $adminId = null): bool
    {
        $payload = [
            'last_message_at' => date('Y-m-d H:i:s'),
        ];

        if ($adminId !== null) {
            $payload['assigned_admin_id'] = $adminId;
        }

        return $this->update($conversationId, $payload);
    }

    public function escalateToHuman(int $conversationId, ?int $orderId = null): bool
    {
        $payload = [
            'support_mode' => 'human',
            'status' => 'pending',
            'escalated_at' => date('Y-m-d H:i:s'),
            'last_message_at' => date('Y-m-d H:i:s'),
        ];

        if ($orderId !== null && $orderId > 0) {
            $payload['order_id'] = $orderId;
        }

        return $this->update($conversationId, $payload);
    }

    public function getRiderInbox(int $riderId): array
    {
        return $this->db->table($this->table . ' c')
            ->select(
                'c.*, u.name AS customer_name, u.email AS customer_email, o.reference_number, ' .
                'latest.message AS latest_message, latest.sender_role AS latest_sender_role, ' .
                'COALESCE(unread.unread_count, 0) AS unread_count',
                false
            )
            ->join('users u', 'u.id = c.customer_id', 'left')
            ->join('orders o', 'o.id = c.order_id', 'left')
            ->join(
                '(SELECT m1.conversation_id, m1.message, m1.sender_role
                  FROM conversation_messages m1
                  INNER JOIN (
                      SELECT conversation_id, MAX(id) AS latest_id
                      FROM conversation_messages
                      GROUP BY conversation_id
                  ) m2 ON m2.latest_id = m1.id) latest',
                'latest.conversation_id = c.id',
                'left',
                false
            )
            ->join(
                "(SELECT conversation_id, COUNT(*) AS unread_count
                  FROM conversation_messages
                  WHERE sender_role IN ('admin','customer') AND is_read = 0
                  GROUP BY conversation_id) unread",
                'unread.conversation_id = c.id',
                'left',
                false
            )
            ->where('c.assigned_rider_id', $riderId)
            ->orderBy('c.last_message_at', 'DESC')
            ->orderBy('c.updated_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function addMessage(array $data): int
    {
        $timestamp = date('Y-m-d H:i:s');
        $payload = array_intersect_key($data, array_flip([
            'conversation_id',
            'sender_id',
            'sender_role',
            'message',
            'message_type',
            'is_read',
            'read_at',
        ]));

        $payload = array_merge([
            'message_type' => 'text',
            'is_read' => 0,
            'read_at' => null,
        ], $payload, [
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $this->db->table($this->messagesTable)->insert($payload);

        return (int) $this->db->insertID();
    }

    public function getMessagesForConversation(int $conversationId): array
    {
        return $this->db->table($this->messagesTable . ' m')
            ->select('m.*, u.name AS sender_name')
            ->join('users u', 'u.id = m.sender_id', 'left')
            ->where('m.conversation_id', $conversationId)
            ->orderBy('m.created_at', 'ASC')
            ->orderBy('m.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getMessagesAfterId(int $conversationId, int $afterId): array
    {
        return $this->db->table($this->messagesTable . ' m')
            ->select('m.*, u.name AS sender_name')
            ->join('users u', 'u.id = m.sender_id', 'left')
            ->where('m.conversation_id', $conversationId)
            ->where('m.id >', $afterId)
            ->orderBy('m.created_at', 'ASC')
            ->orderBy('m.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function markMessagesReadForRole(int $conversationId, string $senderRole): bool
    {
        return $this->db->table($this->messagesTable)
            ->where('conversation_id', $conversationId)
            ->where('sender_role', $senderRole)
            ->where('is_read', 0)
            ->set([
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ])
            ->update();
    }
}
