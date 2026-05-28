<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/order_lib.php';

require_post();

$input = get_request_data();
require_fields($input, ['email']);

$email = normalize_email((string) ($input['email'] ?? ''));
$orderId = (int) ($input['order_id'] ?? 0);
$reference = trim((string) ($input['order_reference'] ?? $input['reference_number'] ?? ''));
$rating = (int) ($input['rating'] ?? $input['review_rating'] ?? 0);
$reviewText = trim((string) ($input['review_text'] ?? $input['comment'] ?? ''));

if ($rating < 1 || $rating > 5) {
    json_response(false, 'Rating must be between 1 and 5 stars.', null, 400);
}

if ($reviewText !== '') {
    $reviewText = mb_substr($reviewText, 0, 1000);
}

try {
    $db = mobile_db();
    $user = find_user_by_email($db, $email);
    if (! is_array($user)) {
        json_response(false, 'User not found.', null, 404);
    }

    $userId = (int) ($user['id'] ?? 0);
    if ($userId <= 0) {
        json_response(false, 'Invalid user account.', null, 400);
    }

    $order = mobile_find_order_for_customer($db, $userId, $orderId, $reference);
    if (! is_array($order)) {
        json_response(false, 'Order not found.', null, 404);
    }

    $resolvedOrderId = (int) ($order['id'] ?? 0);
    if ($resolvedOrderId <= 0) {
        json_response(false, 'Order not found.', null, 404);
    }

    $deliveryStatus = strtolower(trim((string) ($order['delivery_status'] ?? '')));
    if ($deliveryStatus !== 'completed') {
        json_response(false, 'Only completed orders can be reviewed.', null, 400);
    }

    $items = mobile_get_order_items($db, $resolvedOrderId);
    if ($items === []) {
        json_response(false, 'No order items found for review.', null, 400);
    }

    $db->beginTransaction();
    try {
        $upsertStmt = $db->prepare(
            'INSERT INTO product_reviews (product_id, user_id, order_id, rating, review_text, status, created_at, updated_at)
             VALUES (:product_id, :user_id, :order_id, :rating, :review_text, :status, :created_at, :updated_at)
             ON DUPLICATE KEY UPDATE
                rating = VALUES(rating),
                review_text = VALUES(review_text),
                status = VALUES(status),
                updated_at = VALUES(updated_at)'
        );

        $reviewedProductCount = 0;
        $now = date('Y-m-d H:i:s');
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $productId = (int) ($item['product_id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }

            $upsertStmt->execute([
                ':product_id' => $productId,
                ':user_id' => $userId,
                ':order_id' => $resolvedOrderId,
                ':rating' => $rating,
                ':review_text' => $reviewText !== '' ? $reviewText : null,
                ':status' => 'approved',
                ':created_at' => $now,
                ':updated_at' => $now,
            ]);
            $reviewedProductCount++;
        }

        if ($reviewedProductCount <= 0) {
            throw new RuntimeException('No valid products found to review.');
        }

        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }

    mobile_notify_review_submitted($resolvedOrderId, $rating, $reviewText);

    json_response(true, 'Review submitted successfully.', [
        'order_id' => $resolvedOrderId,
        'reference_number' => (string) ($order['reference_number'] ?? ''),
        'review_submitted' => true,
    ], 200);
} catch (Throwable $e) {
    error_log('review_submit.php: ' . $e->getMessage());
    json_response(false, 'Server error while submitting review.', null, 500);
}

function mobile_notify_review_submitted(int $orderId, int $rating, string $reviewText): void
{
    if ($orderId <= 0) {
        return;
    }

    try {
        mobile_ci_bootstrap();
        $notificationService = new \App\Libraries\NotificationService();
        $message = 'A customer submitted a ' . $rating . '-star mobile app review for order #' . $orderId . '.';
        if ($reviewText !== '') {
            $message .= ' "' . mb_substr($reviewText, 0, 120) . (mb_strlen($reviewText) > 120 ? '...' : '') . '"';
        }

        $notificationService->notifyAdmins([
            'category' => 'approvals',
            'type' => 'review_submitted',
            'title' => 'Mobile review submitted',
            'message' => $message,
            'link' => site_url('orders?order_id=' . $orderId),
            'related_type' => 'order',
            'related_id' => $orderId,
        ]);
    } catch (Throwable $e) {
        error_log('mobile_notify_review_submitted failed: ' . $e->getMessage());
    }
}
