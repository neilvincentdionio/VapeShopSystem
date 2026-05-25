<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/messaging_lib.php';

require_post();
$input = get_request_data();
require_fields($input, ['email', 'message']);

$email = normalize_email((string) $input['email']);
$message = trim((string) $input['message']);
$orderId = (int) ($input['order_id'] ?? 0);
$orderReference = trim((string) ($input['related_order'] ?? $input['order_reference'] ?? $input['reference_number'] ?? ''));
$supportTarget = strtolower(trim((string) ($input['support_target'] ?? '')));

if ($message === '') {
    json_response(false, 'Please enter a message.', null, 400);
}

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
    $customerName = trim((string) ($user['name'] ?? $user['full_name'] ?? 'Customer'));

    $resolvedOrderId = mobile_resolve_order_id($db, $userId, $orderId, $orderReference);
    $conversation = mobile_get_or_create_conversation($db, $userId);
    $conversationId = (int) ($conversation['id'] ?? 0);

    mobile_add_conversation_message($db, $conversationId, $userId, 'customer', $message);

    $humanRequested = $supportTarget === 'human' || mobile_should_escalate($message);
    $botRequested = $supportTarget === 'bot';
    $replyMessage = 'Message sent.';
    $botReply = null;

    if ($botRequested && ! $humanRequested) {
        $botReply = mobile_build_bot_reply($db, $message, $userId, $resolvedOrderId)
            ?? 'I can answer FAQs about order status, delivery, payments, and refunds. Type "human support" to talk to an admin/seller.';
        mobile_add_conversation_message($db, $conversationId, $userId, 'chatbot', $botReply, 'bot', true);
        $replyMessage = 'Chatbot replied.';
    } elseif (($conversation['support_mode'] ?? 'bot') === 'human' && ! $humanRequested) {
        if ($resolvedOrderId > 0) {
            $stmt = $db->prepare(
                'UPDATE message_conversations SET status = :status, order_id = :order_id,
                 last_message_at = :last_message_at, updated_at = :updated_at WHERE id = :id'
            );
            $nowTs = date('Y-m-d H:i:s');
            $stmt->execute([
                ':status' => 'pending',
                ':order_id' => $resolvedOrderId,
                ':last_message_at' => $nowTs,
                ':updated_at' => $nowTs,
                ':id' => $conversationId,
            ]);
        }
        $replyMessage = 'Message sent to support.';
    } elseif ($humanRequested) {
        mobile_escalate_conversation($db, $conversationId, $resolvedOrderId > 0 ? $resolvedOrderId : null);
        $systemMsg = 'Customer requested human support. ' . $customerName . ' is waiting for a reply.';
        mobile_add_conversation_message($db, $conversationId, $userId, 'chatbot', $systemMsg, 'system', true);
        $replyMessage = 'Your chat was escalated to admin support.';
    } else {
        $botReply = mobile_build_bot_reply($db, $message, $userId, $resolvedOrderId);
        if ($botReply !== null) {
            mobile_add_conversation_message($db, $conversationId, $userId, 'chatbot', $botReply, 'bot', true);
            $replyMessage = 'Chatbot replied.';
        } else {
            mobile_escalate_conversation($db, $conversationId, $resolvedOrderId > 0 ? $resolvedOrderId : null);
            $replyMessage = 'Message sent. An admin will respond soon.';
        }
    }

    $messages = mobile_get_conversation_messages($db, $conversationId);

    json_response(true, $replyMessage, [
        'conversation_id' => $conversationId,
        'bot_reply' => $botReply,
        'messages' => $messages,
    ], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while sending message.', null, 500);
}
