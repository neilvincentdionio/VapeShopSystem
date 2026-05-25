<?php
declare(strict_types=1);

function mobile_get_or_create_conversation(PDO $db, int $customerId): array
{
    $stmt = $db->prepare(
        'SELECT * FROM message_conversations WHERE customer_id = :customer_id ORDER BY id DESC LIMIT 1'
    );
    $stmt->execute([':customer_id' => $customerId]);
    $conversation = $stmt->fetch();
    if (is_array($conversation)) {
        return $conversation;
    }

    $now = date('Y-m-d H:i:s');
    $insert = $db->prepare(
        'INSERT INTO message_conversations
         (customer_id, subject, support_mode, status, last_message_at, created_at, updated_at)
         VALUES (:customer_id, :subject, :support_mode, :status, :last_message_at, :created_at, :updated_at)'
    );
    $insert->execute([
        ':customer_id' => $customerId,
        ':subject' => 'Customer Support',
        ':support_mode' => 'bot',
        ':status' => 'open',
        ':last_message_at' => $now,
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);

    $id = (int) $db->lastInsertId();

    return [
        'id' => $id,
        'customer_id' => $customerId,
        'subject' => 'Customer Support',
        'support_mode' => 'bot',
        'status' => 'open',
        'last_message_at' => $now,
    ];
}

function mobile_customer_owns_order(PDO $db, int $customerId, int $orderId): bool
{
    if ($orderId <= 0) {
        return false;
    }

    $stmt = $db->prepare('SELECT id FROM orders WHERE id = :id AND customer_id = :customer_id LIMIT 1');
    $stmt->execute([':id' => $orderId, ':customer_id' => $customerId]);

    return $stmt->fetch() !== false;
}

function mobile_resolve_order_id(PDO $db, int $customerId, int $orderId, string $reference): int
{
    if ($orderId > 0 && mobile_customer_owns_order($db, $customerId, $orderId)) {
        return $orderId;
    }

    $reference = trim($reference);
    if ($reference === '') {
        return 0;
    }

    $stmt = $db->prepare(
        'SELECT id FROM orders WHERE customer_id = :customer_id AND reference_number = :reference LIMIT 1'
    );
    $stmt->execute([':customer_id' => $customerId, ':reference' => $reference]);
    $row = $stmt->fetch();

    return is_array($row) ? (int) ($row['id'] ?? 0) : 0;
}

function mobile_add_conversation_message(
    PDO $db,
    int $conversationId,
    int $senderId,
    string $senderRole,
    string $message,
    string $messageType = 'text',
    bool $read = false
): int
{
    $now = date('Y-m-d H:i:s');
    $stmt = $db->prepare(
        'INSERT INTO conversation_messages
         (conversation_id, sender_id, sender_role, message, message_type, is_read, read_at, created_at, updated_at)
         VALUES (:conversation_id, :sender_id, :sender_role, :message, :message_type, :is_read, :read_at, :created_at, :updated_at)'
    );
    $stmt->execute([
        ':conversation_id' => $conversationId,
        ':sender_id' => $senderId,
        ':sender_role' => $senderRole,
        ':message' => function_exists('mb_substr') ? mb_substr($message, 0, 2000) : substr($message, 0, 2000),
        ':message_type' => $messageType,
        ':is_read' => $read ? 1 : 0,
        ':read_at' => $read ? $now : null,
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);

    $touch = $db->prepare(
        'UPDATE message_conversations SET last_message_at = :last_message_at, updated_at = :updated_at WHERE id = :id'
    );
    $touch->execute([':last_message_at' => $now, ':updated_at' => $now, ':id' => $conversationId]);

    return (int) $db->lastInsertId();
}

function mobile_escalate_conversation(PDO $db, int $conversationId, ?int $orderId = null): void
{
    $now = date('Y-m-d H:i:s');
    if ($orderId !== null && $orderId > 0) {
        $stmt = $db->prepare(
            'UPDATE message_conversations
             SET support_mode = :mode, status = :status, escalated_at = :escalated_at,
                 order_id = :order_id, last_message_at = :last_message_at, updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([
            ':mode' => 'human',
            ':status' => 'pending',
            ':escalated_at' => $now,
            ':order_id' => $orderId,
            ':last_message_at' => $now,
            ':updated_at' => $now,
            ':id' => $conversationId,
        ]);
    } else {
        $stmt = $db->prepare(
            'UPDATE message_conversations
             SET support_mode = :mode, status = :status, escalated_at = :escalated_at,
                 last_message_at = :last_message_at, updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([
            ':mode' => 'human',
            ':status' => 'pending',
            ':escalated_at' => $now,
            ':last_message_at' => $now,
            ':updated_at' => $now,
            ':id' => $conversationId,
        ]);
    }
}

function mobile_should_escalate(string $message): bool
{
    $text = strtolower($message);
    foreach (['human', 'admin', 'seller', 'agent', 'support staff', 'representative', 'cannot answer'] as $keyword) {
        if (str_contains($text, $keyword)) {
            return true;
        }
    }

    return false;
}

function mobile_build_bot_reply(PDO $db, string $message, int $customerId, int $orderId): ?string
{
    $text = strtolower($message);

    if (str_contains($text, 'order') || str_contains($text, 'status') || str_contains($text, 'track')) {
        $order = mobile_get_customer_order_for_bot($db, $customerId, $orderId);
        if ($order === null) {
            return 'I could not find an order yet. Choose an order in the dropdown or type "human support" so an admin can help.';
        }

        return sprintf(
            'Order %s is currently %s. Payment is %s. Delivery status is %s.',
            $order['reference_number'] ?? ('#' . $order['id']),
            mobile_humanize_status((string) ($order['order_status'] ?? 'pending')),
            mobile_humanize_status((string) ($order['payment_status'] ?? 'unpaid')),
            mobile_humanize_status((string) ($order['delivery_status'] ?? 'to_pay'))
        );
    }

    if (str_contains($text, 'delivery') || str_contains($text, 'ship') || str_contains($text, 'rider')) {
        return 'For delivery concerns, check your Orders page for the latest tracking status. Type "human support" if you need an admin to coordinate with a rider.';
    }

    if (str_contains($text, 'payment') || str_contains($text, 'gcash') || str_contains($text, 'cash')) {
        return 'We support the payment options shown during checkout. If payment was sent but still appears unpaid, send the reference number and type "human support" for admin verification.';
    }

    if (str_contains($text, 'refund') || str_contains($text, 'return') || str_contains($text, 'cancel')) {
        return 'For returns, refunds, or cancellations, include your order number and reason. Type "human support" if you want an admin to review the request.';
    }

    if (str_contains($text, 'hello') || str_contains($text, 'hi') || str_contains($text, 'help')) {
        return 'Hi! I can help with order status, delivery, payments, and refunds. Type "human support" anytime to talk to an admin/seller.';
    }

    return null;
}

function mobile_get_customer_order_for_bot(PDO $db, int $customerId, int $orderId): ?array
{
    if ($orderId > 0) {
        $stmt = $db->prepare(
            'SELECT o.id, o.reference_number, o.status AS order_status,
                    COALESCE(s.status, \'to_pay\') AS delivery_status,
                    COALESCE(p.status, \'unpaid\') AS payment_status
             FROM orders o
             LEFT JOIN order_shipments s ON s.order_id = o.id
             LEFT JOIN order_payments p ON p.order_id = o.id
             WHERE o.id = :id AND o.customer_id = :customer_id LIMIT 1'
        );
        $stmt->execute([':id' => $orderId, ':customer_id' => $customerId]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    $stmt = $db->prepare(
        'SELECT o.id, o.reference_number, o.status AS order_status,
                COALESCE(s.status, \'to_pay\') AS delivery_status,
                COALESCE(p.status, \'unpaid\') AS payment_status
         FROM orders o
         LEFT JOIN order_shipments s ON s.order_id = o.id
         LEFT JOIN order_payments p ON p.order_id = o.id
         WHERE o.customer_id = :customer_id
         ORDER BY o.id DESC LIMIT 1'
    );
    $stmt->execute([':customer_id' => $customerId]);
    $row = $stmt->fetch();

    return is_array($row) ? $row : null;
}

function mobile_humanize_status(string $status): string
{
    return ucwords(str_replace('_', ' ', $status));
}

/**
 * @return list<array<string, mixed>>
 */
function mobile_get_conversation_messages(PDO $db, int $conversationId): array
{
    $stmt = $db->prepare(
        'SELECT m.id, m.sender_role, m.message, m.message_type, m.created_at, u.name AS sender_name
         FROM conversation_messages m
         LEFT JOIN users u ON u.id = m.sender_id
         WHERE m.conversation_id = :conversation_id
         ORDER BY m.created_at ASC, m.id ASC'
    );
    $stmt->execute([':conversation_id' => $conversationId]);
    $rows = $stmt->fetchAll();

    $messages = [];
    foreach ($rows as $row) {
        if (! is_array($row)) {
            continue;
        }
        $createdAt = (string) ($row['created_at'] ?? '');
        $messages[] = [
            'id' => (int) ($row['id'] ?? 0),
            'sender_role' => (string) ($row['sender_role'] ?? ''),
            'sender_name' => (string) ($row['sender_name'] ?? ''),
            'message' => (string) ($row['message'] ?? ''),
            'message_type' => (string) ($row['message_type'] ?? 'text'),
            'created_at' => $createdAt,
            'created_label' => $createdAt !== '' ? date('M d, Y h:i A', strtotime($createdAt)) : '',
        ];
    }

    return $messages;
}

function mobile_count_unread_staff_messages(PDO $db, int $conversationId): int
{
    if ($conversationId <= 0) {
        return 0;
    }

    $stmt = $db->prepare(
        'SELECT COUNT(*) AS unread_count
         FROM conversation_messages
         WHERE conversation_id = :conversation_id
           AND sender_role IN (\'admin\', \'rider\', \'chatbot\')
           AND is_read = 0'
    );
    $stmt->execute([':conversation_id' => $conversationId]);
    $row = $stmt->fetch();

    return is_array($row) ? (int) ($row['unread_count'] ?? 0) : 0;
}

function mobile_mark_staff_messages_read(PDO $db, int $conversationId): void
{
    $now = date('Y-m-d H:i:s');
    $stmt = $db->prepare(
        'UPDATE conversation_messages
         SET is_read = 1, read_at = :read_at, updated_at = :updated_at
         WHERE conversation_id = :conversation_id
           AND sender_role IN (\'admin\', \'rider\', \'chatbot\')
           AND is_read = 0'
    );
    $stmt->execute([
        ':read_at' => $now,
        ':updated_at' => $now,
        ':conversation_id' => $conversationId,
    ]);
}

function mobile_messaging_tables_exist(PDO $db): bool
{
    try {
        $stmt = $db->query("SHOW TABLES LIKE 'message_conversations'");
        if ($stmt === false || $stmt->fetch() === false) {
            return false;
        }
        $stmt = $db->query("SHOW TABLES LIKE 'conversation_messages'");

        return $stmt !== false && $stmt->fetch() !== false;
    } catch (Throwable $e) {
        return false;
    }
}
