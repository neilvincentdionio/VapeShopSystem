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
        json_response(false, 'Messaging is not set up on the server. Please run database migrations.', null, 503);
    }

    $user = find_user_by_email($db, $email);
    if (!$user) {
        json_response(false, 'User not found.', null, 404);
    }
    $userId = (int) $user['id'];

    if ($role === 'rider') {
        $conversation = mobile_get_rider_conversation($db, $userId, $conversationId, $orderId, $orderReference);
        if (! is_array($conversation)) {
            json_response(true, 'No assigned support chats yet.', [
                'conversation_id' => 0,
                'support_mode' => 'human',
                'status' => 'open',
                'messages' => [],
                'unread_count' => 0,
            ], 200);
        }
    } else {
        $conversation = mobile_get_or_create_conversation($db, $userId);
    }
    $conversationId = (int) ($conversation['id'] ?? 0);

    $markRead = ! in_array(strtolower(trim((string) ($input['mark_read'] ?? '1'))), ['0', 'false', 'no'], true);
    if ($markRead) {
        if ($role === 'rider') {
            mobile_mark_rider_messages_read($db, $conversationId);
        } else {
            mobile_mark_staff_messages_read($db, $conversationId);
        }
    }

    $messages = mobile_get_conversation_messages($db, $conversationId);
    $unread = $role === 'rider'
        ? mobile_count_unread_for_rider_total($db, $userId)
        : mobile_count_unread_staff_messages($db, $conversationId);

    json_response(true, 'Messages loaded.', [
        'conversation_id' => $conversationId,
        'support_mode' => (string) ($conversation['support_mode'] ?? 'bot'),
        'status' => (string) ($conversation['status'] ?? 'open'),
        'messages' => $messages,
        'unread_count' => $unread,
    ], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while loading messages.', null, 500);
}
