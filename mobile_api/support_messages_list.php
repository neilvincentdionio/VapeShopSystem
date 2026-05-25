<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/messaging_lib.php';

require_post();
$input = get_request_data();
require_fields($input, ['email']);

$email = normalize_email((string) $input['email']);

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

    $conversation = mobile_get_or_create_conversation($db, $userId);
    $conversationId = (int) ($conversation['id'] ?? 0);

    $markRead = ! in_array(strtolower(trim((string) ($input['mark_read'] ?? '1'))), ['0', 'false', 'no'], true);
    if ($markRead) {
        mobile_mark_staff_messages_read($db, $conversationId);
    }

    $messages = mobile_get_conversation_messages($db, $conversationId);
    $unread = mobile_count_unread_staff_messages($db, $conversationId);

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
