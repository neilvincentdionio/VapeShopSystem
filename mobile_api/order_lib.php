<?php
declare(strict_types=1);

require_once __DIR__ . '/return_refund_lib.php';

function mobile_customer_cancellable_statuses(): array
{
    return ['to_pay', 'to_ship', 'ready_for_pickup', 'accepted_by_rider'];
}

function mobile_customer_can_cancel_order(array $order): bool
{
    $status = strtolower(trim((string) ($order['delivery_status'] ?? '')));
    if ($status === '' || $status === 'cancelled' || mobile_is_return_refund_status($status)) {
        return false;
    }

    return in_array($status, mobile_customer_cancellable_statuses(), true);
}

function mobile_order_is_cod(array $order): bool
{
    $method = strtolower((string) ($order['payment_method'] ?? ''));
    $notes = (string) ($order['notes'] ?? '');

    return in_array($method, ['cash', 'cod', 'cash_on_delivery'], true)
        || str_contains($notes, 'PAYMENT_METHOD:COD');
}

function mobile_get_order(PDO $db, int $orderId): ?array
{
    $stmt = $db->prepare(
        'SELECT o.id, o.customer_id, o.reference_number, o.order_date, o.status AS order_status,
                o.notes, o.total_amount, o.created_at, o.updated_at,
                COALESCE(s.status, \'to_pay\') AS delivery_status,
                s.notes AS shipment_notes, s.delivery_notes, s.shipping_address,
                s.contact_number, s.tracking_number, s.assigned_rider_id,
                s.delivered_at, s.completed_at, s.shipped_at, s.picked_up_at,
                s.delivery_latitude, s.delivery_longitude,
                s.store_latitude, s.store_longitude, s.store_address,
                s.rider_latitude, s.rider_longitude, s.last_location_updated_at,
                s.delivery_proof_image, s.delivery_proof_submitted_at,
                s.final_rider_latitude, s.final_rider_longitude,
                COALESCE(p.method, \'cash\') AS payment_method,
                COALESCE(p.status, \'unpaid\') AS payment_status,
                COALESCE(p.amount, 0) AS payment_amount,
                u.name AS customer_name, u.email AS customer_email,
                cup.phone_number AS customer_phone,
                r.name AS rider_name,
                rup.phone_number AS rider_phone
         FROM orders o
         LEFT JOIN order_shipments s ON s.order_id = o.id
         LEFT JOIN order_payments p ON p.order_id = o.id
         LEFT JOIN users u ON u.id = o.customer_id
         LEFT JOIN user_profiles cup ON cup.user_id = u.id
         LEFT JOIN users r ON r.id = s.assigned_rider_id
         LEFT JOIN user_profiles rup ON rup.user_id = r.id
         WHERE o.id = :order_id
         LIMIT 1'
    );
    $stmt->execute([':order_id' => $orderId]);
    $row = $stmt->fetch();

    return is_array($row) ? $row : null;
}

function mobile_get_order_items(PDO $db, int $orderId): array
{
    $stmt = $db->prepare(
        'SELECT product_id, product_name, quantity, unit_price, selling_price, subtotal, profit
         FROM order_items WHERE order_id = :order_id ORDER BY id ASC'
    );
    $stmt->execute([':order_id' => $orderId]);

    return $stmt->fetchAll() ?: [];
}

function mobile_format_order_row(array $order, bool $includeItems = true, ?PDO $db = null): array
{
    $orderId = (int) ($order['id'] ?? 0);
    $deliveryStatus = (string) ($order['delivery_status'] ?? 'to_pay');
    $returnMeta = mobile_parse_return_meta(
        (string) ($order['shipment_notes'] ?? ''),
        (string) ($order['delivery_notes'] ?? '')
    );

    $items = [];
    if ($includeItems && $db instanceof PDO && $orderId > 0) {
        $items = mobile_get_order_items($db, $orderId);
    } elseif (! empty($order['items']) && is_array($order['items'])) {
        $items = $order['items'];
    }

    $qrPayload = '';
    $returnToken = '';
    if (is_array($returnMeta)) {
        $returnToken = (string) ($returnMeta['return_token'] ?? '');
        $qrPayload = (string) ($returnMeta['qr_payload'] ?? '');
        if ($qrPayload === '' && $returnToken !== '') {
            $qrPayload = mobile_build_return_qr_payload(
                $orderId,
                $returnToken,
                (string) ($order['reference_number'] ?? ('#' . $orderId))
            );
        }
    }

    $eligibility = mobile_customer_can_request_return([
        'delivery_status' => $deliveryStatus,
        'payment_status' => (string) ($order['payment_status'] ?? 'unpaid'),
        'delivered_at' => (string) ($order['delivered_at'] ?? ''),
        'completed_at' => (string) ($order['completed_at'] ?? ''),
        'updated_at' => (string) ($order['updated_at'] ?? ''),
    ]);

    return [
        'order_id' => $orderId,
        'reference_number' => (string) ($order['reference_number'] ?? ''),
        'order_date' => (string) ($order['order_date'] ?? ''),
        'created_at' => (string) ($order['created_at'] ?? ''),
        'placed_at' => (string) ($order['created_at'] ?? ''),
        'status' => (string) ($order['order_status'] ?? ''),
        'delivery_status' => $deliveryStatus,
        'payment_status' => (string) ($order['payment_status'] ?? 'unpaid'),
        'payment_method' => (string) ($order['payment_method'] ?? ''),
        'total_amount' => round((float) ($order['total_amount'] ?? 0), 2),
        'customer_id' => (int) ($order['customer_id'] ?? 0),
        'customer_name' => (string) ($order['customer_name'] ?? ''),
        'customer_email' => (string) ($order['customer_email'] ?? ''),
        'customer_phone' => (string) ($order['customer_phone'] ?? $order['contact_number'] ?? ''),
        'assigned_rider_id' => (int) ($order['assigned_rider_id'] ?? 0),
        'rider_name' => (string) ($order['rider_name'] ?? ''),
        'rider_phone' => (string) ($order['rider_phone'] ?? ''),
        'tracking_number' => (string) ($order['tracking_number'] ?? ''),
        'can_cancel' => mobile_customer_can_cancel_order($order),
        'can_request_return' => $eligibility['allowed'],
        'can_confirm_received' => $deliveryStatus === 'delivered',
        'can_pay' => $deliveryStatus === 'to_pay',
        'refund_requested' => $returnMeta !== null || mobile_is_return_refund_status($deliveryStatus),
        'return_token' => $returnToken,
        'qr_payload' => $qrPayload,
        'return_meta' => $returnMeta,
        'shipment' => [
            'status' => $deliveryStatus,
            'shipping_address' => (string) ($order['shipping_address'] ?? ''),
            'contact_number' => (string) ($order['contact_number'] ?? ''),
            'delivery_latitude' => isset($order['delivery_latitude']) ? (float) $order['delivery_latitude'] : null,
            'delivery_longitude' => isset($order['delivery_longitude']) ? (float) $order['delivery_longitude'] : null,
            'store_latitude' => isset($order['store_latitude']) ? (float) $order['store_latitude'] : null,
            'store_longitude' => isset($order['store_longitude']) ? (float) $order['store_longitude'] : null,
            'store_address' => (string) ($order['store_address'] ?? ''),
            'rider_latitude' => isset($order['rider_latitude']) ? (float) $order['rider_latitude'] : null,
            'rider_longitude' => isset($order['rider_longitude']) ? (float) $order['rider_longitude'] : null,
            'proof_image' => (string) ($order['delivery_proof_image'] ?? ''),
        ],
        'payment' => [
            'method' => (string) ($order['payment_method'] ?? ''),
            'status' => (string) ($order['payment_status'] ?? ''),
            'amount' => round((float) ($order['payment_amount'] ?? $order['total_amount'] ?? 0), 2),
        ],
        'items' => array_map(static function (array $item): array {
            return [
                'product_id' => (int) ($item['product_id'] ?? 0),
                'product_name' => (string) ($item['product_name'] ?? ''),
                'quantity' => (int) ($item['quantity'] ?? 0),
                'unit_price' => (float) ($item['unit_price'] ?? 0),
                'selling_price' => (float) ($item['selling_price'] ?? 0),
                'subtotal' => (float) ($item['subtotal'] ?? 0),
            ];
        }, $items),
    ];
}

function mobile_build_tracking_payload(array $order, string $viewerRole): array
{
    $status = (string) ($order['delivery_status'] ?? 'to_pay');
    $allowLiveRider = in_array($status, ['delivered_to_rider', 'to_receive', 'delivered', 'completed'], true);
    $riderLat = isset($order['rider_latitude']) ? (float) $order['rider_latitude'] : null;
    $riderLng = isset($order['rider_longitude']) ? (float) $order['rider_longitude'] : null;
    if ($viewerRole === 'customer' && ! $allowLiveRider) {
        $riderLat = null;
        $riderLng = null;
    }

    $delLat = isset($order['delivery_latitude']) ? (float) $order['delivery_latitude'] : null;
    $delLng = isset($order['delivery_longitude']) ? (float) $order['delivery_longitude'] : null;
    $distanceKm = null;
    $etaMinutes = null;
    if ($riderLat !== null && $riderLng !== null && $delLat !== null && $delLng !== null) {
        $distanceKm = round(mobile_haversine_km($riderLat, $riderLng, $delLat, $delLng), 2);
        $etaMinutes = max(2, (int) round(($distanceKm / 25) * 60));
    }

    return [
        'order_id' => (int) ($order['id'] ?? 0),
        'status' => $status,
        'phase' => in_array($status, ['ready_for_pickup', 'accepted_by_rider'], true)
            ? 'pickup'
            : (in_array($status, ['delivered_to_rider', 'to_receive', 'delivered'], true) ? 'delivery' : 'none'),
        'delivery_address' => (string) ($order['shipping_address'] ?? ''),
        'delivery_latitude' => isset($order['delivery_latitude']) ? (float) $order['delivery_latitude'] : null,
        'delivery_longitude' => isset($order['delivery_longitude']) ? (float) $order['delivery_longitude'] : null,
        'store_address' => (string) ($order['store_address'] ?? ''),
        'store_latitude' => isset($order['store_latitude']) ? (float) $order['store_latitude'] : null,
        'store_longitude' => isset($order['store_longitude']) ? (float) $order['store_longitude'] : null,
        'rider_latitude' => $riderLat,
        'rider_longitude' => $riderLng,
        'last_location_updated_at' => $order['last_location_updated_at'] ?? null,
        'rider' => [
            'name' => (string) ($order['rider_name'] ?? ''),
            'contact' => (string) ($order['rider_phone'] ?? ''),
        ],
        'proof_image' => (string) ($order['delivery_proof_image'] ?? ''),
        'proof_notes' => mobile_strip_return_meta_from_notes((string) ($order['delivery_notes'] ?? '')),
        'proof_submitted_at' => $order['delivery_proof_submitted_at'] ?? null,
        'distance_km' => $distanceKm,
        'eta_minutes' => $etaMinutes,
    ];
}

function mobile_haversine_km(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $r = 6371.0;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = pow(sin($dLat / 2), 2)
        + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * pow(sin($dLon / 2), 2);

    return 2 * $r * atan2(sqrt($a), sqrt(1 - $a));
}

function mobile_reserve_order_stock(PDO $db, int $orderId): bool
{
    $items = mobile_get_order_items($db, $orderId);
    foreach ($items as $item) {
        $qty = (int) ($item['quantity'] ?? 0);
        $productId = (int) ($item['product_id'] ?? 0);
        if ($qty <= 0 || $productId <= 0) {
            continue;
        }
        $stmt = $db->prepare(
            'UPDATE products SET stock_qty = stock_qty - :qty WHERE id = :id AND stock_qty >= :qty'
        );
        $stmt->execute([':qty' => $qty, ':id' => $productId]);
        if ($stmt->rowCount() === 0) {
            return false;
        }
    }

    return true;
}

function mobile_restore_order_stock(PDO $db, int $orderId): bool
{
    $items = mobile_get_order_items($db, $orderId);
    foreach ($items as $item) {
        $qty = (int) ($item['quantity'] ?? 0);
        $productId = (int) ($item['product_id'] ?? 0);
        if ($qty <= 0 || $productId <= 0) {
            continue;
        }
        $stmt = $db->prepare('UPDATE products SET stock_qty = stock_qty + :qty WHERE id = :id');
        $stmt->execute([':qty' => $qty, ':id' => $productId]);
    }

    return true;
}

function mobile_order_pay(PDO $db, array $order, int $customerId): array
{
    if ((string) ($order['delivery_status'] ?? '') !== 'to_pay') {
        return ['success' => false, 'message' => 'Order cannot be paid.'];
    }
    if ((int) ($order['customer_id'] ?? 0) !== $customerId) {
        return ['success' => false, 'message' => 'Access denied.'];
    }

    $orderId = (int) $order['id'];
    $db->beginTransaction();
    try {
        if (! mobile_reserve_order_stock($db, $orderId)) {
            throw new RuntimeException('Insufficient stock for one of the order items.');
        }

        $now = date('Y-m-d H:i:s');
        $total = round((float) ($order['total_amount'] ?? 0), 2);

        $db->prepare('UPDATE orders SET status = :status, updated_at = :now WHERE id = :id')
            ->execute([':status' => 'completed', ':now' => $now, ':id' => $orderId]);

        $payCheck = $db->prepare('SELECT id FROM order_payments WHERE order_id = :order_id LIMIT 1');
        $payCheck->execute([':order_id' => $orderId]);
        if ($payCheck->fetch()) {
            $db->prepare(
                'UPDATE order_payments SET status = :status, amount = :amount, amount_received = :amount_received,
                 change_amount = 0, paid_at = :paid_at, updated_at = :updated_at WHERE order_id = :order_id'
            )->execute([
                ':status' => 'paid',
                ':amount' => $total,
                ':amount_received' => $total,
                ':paid_at' => $now,
                ':updated_at' => $now,
                ':order_id' => $orderId,
            ]);
        } else {
            $db->prepare(
                'INSERT INTO order_payments (order_id, method, status, amount, amount_received, change_amount, paid_at, created_at, updated_at)
                 VALUES (:order_id, :method, :status, :amount, :amount_received, 0, :paid_at, :created_at, :updated_at)'
            )->execute([
                ':order_id' => $orderId,
                ':method' => 'online',
                ':status' => 'paid',
                ':amount' => $total,
                ':amount_received' => $total,
                ':paid_at' => $now,
                ':created_at' => $now,
                ':updated_at' => $now,
            ]);
        }

        mobile_update_delivery_status($db, $orderId, 'to_ship', ['updated_at' => $now]);
        $db->commit();

        return ['success' => true, 'message' => 'Payment processed. Order is ready for shipping.'];
    } catch (Throwable $e) {
        $db->rollBack();

        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function mobile_order_cancel(PDO $db, array $order, int $customerId): array
{
    if ((int) ($order['customer_id'] ?? 0) !== $customerId) {
        return ['success' => false, 'message' => 'Access denied.'];
    }
    if (! mobile_customer_can_cancel_order($order)) {
        return ['success' => false, 'message' => 'This order cannot be cancelled at its current stage.'];
    }

    $orderId = (int) $order['id'];
    $status = strtolower((string) ($order['delivery_status'] ?? ''));
    $db->beginTransaction();
    try {
        if ($status !== 'to_pay') {
            mobile_restore_order_stock($db, $orderId);
        }
        $now = date('Y-m-d H:i:s');
        $db->prepare('UPDATE orders SET status = :status, updated_at = :now WHERE id = :id')
            ->execute([':status' => 'cancelled', ':now' => $now, ':id' => $orderId]);
        mobile_update_delivery_status($db, $orderId, 'cancelled', ['updated_at' => $now]);
        $db->commit();

        return ['success' => true, 'message' => 'Order cancelled successfully.'];
    } catch (Throwable $e) {
        $db->rollBack();

        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function mobile_order_confirm_received(PDO $db, array $order, int $customerId): array
{
    if ((int) ($order['customer_id'] ?? 0) !== $customerId) {
        return ['success' => false, 'message' => 'Access denied.'];
    }
    if ((string) ($order['delivery_status'] ?? '') !== 'delivered') {
        return ['success' => false, 'message' => 'Order cannot be confirmed yet.'];
    }

    $orderId = (int) $order['id'];
    $now = date('Y-m-d H:i:s');
    $shipmentData = [
        'status' => 'completed',
        'delivered_at' => (string) ($order['delivered_at'] ?? '') !== '' ? (string) $order['delivered_at'] : $now,
        'completed_at' => $now,
    ];

    $db->beginTransaction();
    try {
        if (mobile_order_is_cod($order) && (string) ($order['payment_status'] ?? '') !== 'paid') {
            $total = round((float) ($order['total_amount'] ?? 0), 2);
            $db->prepare('UPDATE orders SET status = :status, updated_at = :updated_at WHERE id = :id')
                ->execute([':status' => 'completed', ':updated_at' => $now, ':id' => $orderId]);

            $payCheck = $db->prepare('SELECT id FROM order_payments WHERE order_id = :order_id LIMIT 1');
            $payCheck->execute([':order_id' => $orderId]);
            $paymentMethod = strtolower((string) ($order['payment_method'] ?? 'cash'));
            if ($payCheck->fetch()) {
                $db->prepare(
                    'UPDATE order_payments SET status = :status, amount = :amount, amount_received = :amount_received,
                     change_amount = 0, paid_at = :paid_at, updated_at = :updated_at WHERE order_id = :order_id'
                )->execute([
                    ':status' => 'paid',
                    ':amount' => $total,
                    ':amount_received' => $total,
                    ':paid_at' => $now,
                    ':updated_at' => $now,
                    ':order_id' => $orderId,
                ]);
            } else {
                $db->prepare(
                    'INSERT INTO order_payments (order_id, method, status, amount, amount_received, change_amount, paid_at, created_at, updated_at)
                     VALUES (:order_id, :method, :status, :amount, :amount_received, 0, :paid_at, :created_at, :updated_at)'
                )->execute([
                    ':order_id' => $orderId,
                    ':method' => $paymentMethod !== '' ? $paymentMethod : 'cash',
                    ':status' => 'paid',
                    ':amount' => $total,
                    ':amount_received' => $total,
                    ':paid_at' => $now,
                    ':created_at' => $now,
                    ':updated_at' => $now,
                ]);
            }
        } else {
            $db->prepare('UPDATE orders SET status = :status, updated_at = :updated_at WHERE id = :id')
                ->execute([':status' => 'completed', ':updated_at' => $now, ':id' => $orderId]);
        }

        if (! mobile_update_delivery_status($db, $orderId, 'completed', $shipmentData)) {
            throw new RuntimeException('Unable to update delivery status.');
        }

        $db->commit();

        return ['success' => true, 'message' => 'Order received confirmed. Thank you!'];
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function mobile_assign_rider(PDO $db, int $orderId, int $riderId): array
{
    $order = mobile_get_order($db, $orderId);
    if ($order === null) {
        return ['success' => false, 'message' => 'Order not found.'];
    }

    $riderStmt = $db->prepare('SELECT id, role FROM users WHERE id = :id AND is_active = 1 LIMIT 1');
    $riderStmt->execute([':id' => $riderId]);
    $rider = $riderStmt->fetch();
    if (! is_array($rider) || mobile_normalize_role((string) ($rider['role'] ?? '')) !== 'rider') {
        return ['success' => false, 'message' => 'Invalid rider selected.'];
    }

    $current = (string) ($order['delivery_status'] ?? 'to_pay');
    if (in_array($current, ['completed', 'cancelled', 'return_refund'], true) || mobile_is_return_refund_status($current)) {
        return ['success' => false, 'message' => 'This order cannot be assigned to a rider.'];
    }
    if (in_array($current, ['to_receive', 'delivered_to_rider'], true)) {
        return ['success' => false, 'message' => 'Rider cannot be reassigned after pickup.'];
    }

    $now = date('Y-m-d H:i:s');
    $ok = mobile_update_delivery_status($db, $orderId, 'ready_for_pickup', [
        'assigned_rider_id' => $riderId,
        'assigned_at' => $now,
        'updated_at' => $now,
    ]);

    return $ok
        ? ['success' => true, 'message' => 'Rider assigned successfully.']
        : ['success' => false, 'message' => 'Failed to assign rider.'];
}

function mobile_admin_update_status(PDO $db, int $orderId, string $newStatus): array
{
    $valid = ['to_pay', 'to_ship', 'ready_for_pickup', 'accepted_by_rider', 'delivered_to_rider', 'to_receive', 'completed', 'failed_delivery'];
    if (! in_array($newStatus, $valid, true)) {
        return ['success' => false, 'message' => 'Invalid delivery status.'];
    }

    $order = mobile_get_order($db, $orderId);
    if ($order === null) {
        return ['success' => false, 'message' => 'Order not found.'];
    }

    $current = (string) ($order['delivery_status'] ?? 'to_pay');
    if ($current === 'to_pay' && $newStatus === 'to_ship' && (string) ($order['payment_status'] ?? '') !== 'paid' && ! mobile_order_is_cod($order)) {
        return ['success' => false, 'message' => 'Process payment first before shipping.'];
    }
    if ($newStatus === 'delivered_to_rider' && $current !== 'accepted_by_rider') {
        return ['success' => false, 'message' => 'Pickup can only be marked after rider accepts.'];
    }
    if ($newStatus === 'completed') {
        if ($current !== 'delivered') {
            return ['success' => false, 'message' => 'Confirm only after rider marks delivered.'];
        }
        if (trim((string) ($order['delivery_proof_image'] ?? '')) === '') {
            return ['success' => false, 'message' => 'Delivery proof is required before confirmation.'];
        }
    }

    $extra = ['updated_at' => date('Y-m-d H:i:s')];
    if ($newStatus === 'delivered_to_rider') {
        $extra['picked_up_at'] = date('Y-m-d H:i:s');
    }
    if ($newStatus === 'completed') {
        $extra['completed_at'] = date('Y-m-d H:i:s');
        $extra['delivered_at'] = date('Y-m-d H:i:s');
    }
    if ($newStatus === 'to_ship' && trim((string) ($order['tracking_number'] ?? '')) === '') {
        $extra['tracking_number'] = 'TRK' . $orderId . strtoupper(bin2hex(random_bytes(3)));
    }

    $ok = mobile_update_delivery_status($db, $orderId, $newStatus, $extra);
    if ($ok && $newStatus === 'completed') {
        $db->prepare('UPDATE orders SET status = :status, updated_at = :now WHERE id = :id')
            ->execute([':status' => 'completed', ':now' => date('Y-m-d H:i:s'), ':id' => $orderId]);
    }

    return $ok
        ? ['success' => true, 'message' => 'Delivery status updated.']
        : ['success' => false, 'message' => 'Unable to update status.'];
}

function mobile_distance_meters(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $earth = 6371000;
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

    return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

function mobile_parse_coordinate(mixed $value): ?float
{
    if ($value === null || $value === '') {
        return null;
    }
    if (! is_numeric($value)) {
        return null;
    }
    $f = (float) $value;

    return $f >= -90 && $f <= 90 ? $f : null;
}

function mobile_rider_has_active_delivery(PDO $db, int $riderId, int $excludeOrderId = 0): bool
{
    $stmt = $db->prepare(
        'SELECT id FROM order_shipments WHERE assigned_rider_id = :rider_id AND status = :status
         AND order_id != :exclude LIMIT 1'
    );
    $stmt->execute([':rider_id' => $riderId, ':status' => 'to_receive', ':exclude' => $excludeOrderId]);

    return $stmt->fetch() !== false;
}
