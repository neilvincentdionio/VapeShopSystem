<?php
declare(strict_types=1);

function mobile_notifications_unread_count(PDO $db, int $userId, string $role): int
{
    if ($userId <= 0 || $role === '') {
        return 0;
    }

    $sql = 'SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND role = :role AND is_read = 0';
    if ($role === 'customer') {
        $sql .= " AND type != 'delivery_proof'";
    }
    $stmt = $db->prepare($sql);
    $stmt->execute([':uid' => $userId, ':role' => $role]);

    return (int) $stmt->fetchColumn();
}

function mobile_notifications_recent(PDO $db, int $userId, string $role, int $limit = 20): array
{
    if ($userId <= 0 || $role === '') {
        return [];
    }

    $sql = 'SELECT id, category, type, title, message, link, related_type, related_id, is_read, created_at
            FROM notifications
            WHERE user_id = :uid AND role = :role';
    if ($role === 'customer') {
        $sql .= " AND type != 'delivery_proof'";
    }
    $sql .= ' ORDER BY created_at DESC LIMIT ' . max(1, min(50, $limit));

    $stmt = $db->prepare($sql);
    $stmt->execute([':uid' => $userId, ':role' => $role]);
    $rows = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (is_array($row)) {
            $rows[] = mobile_format_notification_row($row, $role);
        }
    }

    return $rows;
}

function mobile_notification_mark_read(PDO $db, int $notificationId, int $userId, string $role): bool
{
    if ($notificationId <= 0 || $userId <= 0) {
        return false;
    }

    $stmt = $db->prepare(
        'UPDATE notifications SET is_read = 1, read_at = :read_at, updated_at = :updated_at
         WHERE id = :id AND user_id = :uid AND role = :role AND is_read = 0'
    );

    return $stmt->execute([
        ':read_at' => date('Y-m-d H:i:s'),
        ':updated_at' => date('Y-m-d H:i:s'),
        ':id' => $notificationId,
        ':uid' => $userId,
        ':role' => $role,
    ]);
}

function mobile_notification_mark_all_read(PDO $db, int $userId, string $role): void
{
    if ($userId <= 0 || $role === '') {
        return;
    }

    $sql = 'UPDATE notifications SET is_read = 1, read_at = :read_at, updated_at = :updated_at
            WHERE user_id = :uid AND role = :role AND is_read = 0';
    if ($role === 'customer') {
        $sql .= " AND type != 'delivery_proof'";
    }
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':read_at' => date('Y-m-d H:i:s'),
        ':updated_at' => date('Y-m-d H:i:s'),
        ':uid' => $userId,
        ':role' => $role,
    ]);
}

/**
 * @param array<string, mixed> $row
 * @return array<string, mixed>
 */
function mobile_format_notification_row(array $row, string $role): array
{
    $createdAt = (string) ($row['created_at'] ?? '');
    $type = strtolower((string) ($row['type'] ?? ''));
    $category = strtolower((string) ($row['category'] ?? ''));
    $link = (string) ($row['link'] ?? '');
    $relatedId = (int) ($row['related_id'] ?? 0);
    $relatedType = strtolower((string) ($row['related_type'] ?? ''));
    $target = mobile_notification_mobile_target($link, $type, $role, $relatedId, $relatedType, $category);

    return [
        'id' => (int) ($row['id'] ?? 0),
        'category' => (string) ($row['category'] ?? 'general'),
        'type' => (string) ($row['type'] ?? 'info'),
        'title' => (string) ($row['title'] ?? 'Notification'),
        'message' => (string) ($row['message'] ?? ''),
        'link' => $link,
        'is_read' => (int) ($row['is_read'] ?? 0) === 1,
        'created_at' => $createdAt,
        'created_label' => $createdAt !== '' ? date('M d, h:i A', strtotime($createdAt)) : '',
        'mobile_target' => $target['target'],
        'order_id' => $target['order_id'],
    ];
}

/**
 * @return array{target: string, order_id: int}
 */
function mobile_notification_mobile_target(
    string $link,
    string $type,
    string $role,
    int $relatedId,
    string $relatedType = '',
    string $category = ''
): array {
    $orderId = $relatedId;
    if (preg_match('#/order-details/(\d+)#i', $link, $m) === 1) {
        $orderId = (int) $m[1];
    } elseif (preg_match('#/customer/order-details/(\d+)#i', $link, $m) === 1) {
        $orderId = (int) $m[1];
    } elseif (preg_match('#/admin/order-details/(\d+)#i', $link, $m) === 1) {
        $orderId = (int) $m[1];
    } elseif (preg_match('#/rider/order-details/(\d+)#i', $link, $m) === 1) {
        $orderId = (int) $m[1];
    } elseif (preg_match('#[?&]order_id=(\d+)#i', $link, $m) === 1) {
        $orderId = (int) $m[1];
    }

    if ($relatedType === 'order' || mobile_is_order_notification($type, $category)) {
        if ($orderId <= 0) {
            $orderId = $relatedId;
        }
    }

    if ($role === 'rider') {
        if (str_contains($link, '/rider/returns') || str_contains($type, 'return')) {
            return ['target' => 'returns', 'order_id' => $orderId];
        }
        if (str_contains($link, '/rider/deliveries') || $type === 'rider_assigned') {
            return ['target' => 'deliveries', 'order_id' => $orderId];
        }
        if (str_contains($link, '/rider/messages') || $relatedType === 'conversation' || $category === 'messages') {
            return ['target' => 'messages', 'order_id' => 0];
        }
        if ($orderId > 0) {
            return ['target' => 'order_detail', 'order_id' => $orderId];
        }

        return ['target' => 'none', 'order_id' => 0];
    }

    if ($role === 'customer') {
        if (str_contains($link, '/customer/messages') || $relatedType === 'conversation' || $category === 'messages') {
            return ['target' => 'messages', 'order_id' => 0];
        }
        if (str_contains($link, '/customer/orders') || $category === 'orders') {
            return ['target' => 'orders', 'order_id' => $orderId];
        }
        if ($relatedType === 'user' || str_starts_with($type, 'account_')) {
            return ['target' => 'profile', 'order_id' => 0];
        }
        if ($orderId > 0) {
            return ['target' => 'order_detail', 'order_id' => $orderId];
        }
        if (str_starts_with($type, 'review_')) {
            return ['target' => 'orders', 'order_id' => 0];
        }
    }

    return ['target' => 'none', 'order_id' => $orderId];
}

function mobile_is_order_notification(string $type, string $category): bool
{
    if (in_array($category, ['orders', 'payments', 'delivery', 'cancellations'], true)) {
        return true;
    }

    return in_array($type, [
        'approval',
        'cancellation',
        'delivery_accepted',
        'delivery_cancelled',
        'delivery_info',
        'delivery_status',
        'new_order',
        'order_cancelled',
        'order_created',
        'order_handed_to_rider',
        'order_picked_up',
        'order_received',
        'order_status',
        'out_for_delivery',
        'payment_received',
        'rider_assigned',
    ], true);
}
