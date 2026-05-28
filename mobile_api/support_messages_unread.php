<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/messaging_lib.php';

require_post();
$input = get_request_data();
require_fields($input, ['email']);

$email = normalize_email((string) $input['email']);
$role = strtolower(trim((string) ($input['role'] ?? 'customer')));
$conversationId = (int) ($input['conversation_id'] ?? 0);
$orderId = (int) ($input['order_id'] ?? 0);
$orderReference = trim((string) ($input['related_order'] ?? $input['order_reference'] ?? ''));

try {
    $db = mobile_db();
    if (! mobile_messaging_tables_exist($db)) {
        json_response(true, 'Messaging not configured.', ['unread_count' => 0], 200);
    }

    $user = find_user_by_email($db, $email);
    if (! $user) {
        json_response(false, 'User not found.', null, 404);
    }

    $userId = (int) $user['id'];
    if ($role === 'rider') {
        $conversation = mobile_get_rider_conversation($db, $userId, $conversationId, $orderId, $orderReference);
        $resolvedConversationId = (int) ($conversation['id'] ?? 0);
        $unread = mobile_count_unread_for_rider_total($db, $userId);
    } else {
        $conversation = mobile_get_or_create_conversation($db, $userId);
        $resolvedConversationId = (int) ($conversation['id'] ?? 0);
        $unread = mobile_count_unread_staff_messages($db, $resolvedConversationId);
    }

    json_response(true, 'Unread count loaded.', [
        'unread_count' => $unread,
        'conversation_id' => $resolvedConversationId,
    ], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while loading unread count.', null, 500);
}
