<?php
declare(strict_types=1);

/**
 * Create in-app notifications for customer + admin after mobile checkout.
 * Uses the same NotificationService as the web app.
 */
function mobile_notify_order_placed(int $customerId, int $orderId, string $reference, string $paymentMethod): void
{
    if ($customerId <= 0 || $orderId <= 0 || $reference === '') {
        return;
    }

    try {
        require_once __DIR__ . '/common.php';
        mobile_ci_bootstrap();

        $notificationService = new \App\Libraries\NotificationService();
        $orderId = (int) $orderId;
        $reference = mb_substr($reference, 0, 160);

        $notificationService->notifyUsers([$customerId], [
            'category' => 'orders',
            'type' => 'order_created',
            'title' => 'Order placed',
            'message' => 'Your order ' . $reference . ' was placed successfully.',
            'link' => site_url('customer/order-details/' . $orderId),
            'related_type' => 'order',
            'related_id' => $orderId,
        ]);

        $isGcash = $paymentMethod === 'gcash';
        $notificationService->notifyAdmins([
            'category' => 'orders',
            'type' => $isGcash ? 'payment_received' : 'new_order',
            'title' => $isGcash ? 'GCash order paid' : 'New COD order',
            'message' => 'Order ' . $reference . ' is ready for processing (mobile app).',
            'link' => $isGcash
                ? site_url('admin/order-details/' . $orderId)
                : site_url('orders?order_id=' . $orderId . '#order-' . $orderId),
            'related_type' => 'order',
            'related_id' => $orderId,
        ]);
    } catch (Throwable $e) {
        error_log('mobile_notify_order_placed failed: ' . $e->getMessage());
    }
}
