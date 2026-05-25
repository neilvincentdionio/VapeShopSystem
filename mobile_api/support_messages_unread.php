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
        json_response(true, 'Messaging not configured.', ['unread_count' => 0], 200);
    }

    $user = find_user_by_email($db, $email);
    if (! $user) {
        json_response(false, 'User not found.', null, 404);
    }

    $userId = (int) $user['id'];
    $conversation = mobile_get_or_create_conversation($db, $userId);
    $conversationId = (int) ($conversation['id'] ?? 0);
    $unread = mobile_count_unread_staff_messages($db, $conversationId);

    json_response(true, 'Unread count loaded.', [
        'unread_count' => $unread,
        'conversation_id' => $conversationId,
    ], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while loading unread count.', null, 500);
}
