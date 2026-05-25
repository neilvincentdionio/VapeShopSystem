<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/auth_lib.php';
require_once __DIR__ . '/order_lib.php';
require_once __DIR__ . '/return_refund_lib.php';

require_post();
$email = normalize_email((string) ($_POST['email'] ?? ''));
$orderId = (int) ($_POST['order_id'] ?? 0);
$status = trim((string) ($_POST['status'] ?? ''));

if ($orderId <= 0 || $status === '') {
    json_response(false, 'order_id and status are required.', null, 400);
}

try {
    $db = mobile_db();
    $user = mobile_require_rider($db, $email);
    $riderId = (int) $user['id'];

    $order = mobile_get_order($db, $orderId);
    if ($order === null) {
        json_response(false, 'Shipment not found.', null, 404);
    }
    if ((int) ($order['assigned_rider_id'] ?? 0) !== $riderId) {
        json_response(false, 'This order is not assigned to you.', null, 403);
    }

    $current = (string) ($order['delivery_status'] ?? 'to_pay');
    $now = date('Y-m-d H:i:s');
    $lat = mobile_parse_coordinate($_POST['rider_latitude'] ?? null);
    $lng = mobile_parse_coordinate($_POST['rider_longitude'] ?? null);
    $loc = [];
    if ($lat !== null && $lng !== null) {
        $loc = ['rider_latitude' => $lat, 'rider_longitude' => $lng, 'last_location_updated_at' => $now];
    }

    $result = ['success' => false, 'message' => 'Invalid status transition.'];

    if ($status === 'accepted_by_rider') {
        if ($current !== 'ready_for_pickup') {
            json_response(false, 'Order is not ready for acceptance.', null, 400);
        }
        $ok = mobile_update_delivery_status($db, $orderId, 'accepted_by_rider', array_merge($loc, ['updated_at' => $now]));
        $result = ['success' => $ok, 'message' => $ok ? 'Delivery accepted successfully.' : 'Unable to accept delivery.'];
    } elseif ($status === 'delivered_to_rider') {
        if ($current !== 'accepted_by_rider') {
            json_response(false, 'Please accept the delivery before pickup.', null, 400);
        }
        $ok = mobile_update_delivery_status($db, $orderId, 'delivered_to_rider', array_merge($loc, [
            'picked_up_at' => $now, 'updated_at' => $now,
        ]));
        $result = ['success' => $ok, 'message' => $ok ? 'Order picked up successfully.' : 'Unable to update pickup status.'];
    } elseif ($status === 'to_receive') {
        if (! in_array($current, ['delivered_to_rider', 'failed_delivery'], true)) {
            json_response(false, 'Order cannot start delivery from current state.', null, 400);
        }
        if (mobile_rider_has_active_delivery($db, $riderId, $orderId)) {
            json_response(false, 'Finish your current active delivery first.', null, 400);
        }
        $ok = mobile_update_delivery_status($db, $orderId, 'to_receive', array_merge($loc, ['updated_at' => $now]));
        $result = ['success' => $ok, 'message' => $ok ? 'Delivery started.' : 'Unable to start delivery.'];
    } elseif ($status === 'reschedule_delivery') {
        if ($current !== 'to_receive') {
            json_response(false, 'Only out-for-delivery orders can be rescheduled.', null, 400);
        }
        $rescheduleAt = trim((string) ($_POST['reschedule_at'] ?? ''));
        $rescheduleReason = trim((string) ($_POST['reschedule_reason'] ?? 'No reason provided'));
        if ($rescheduleAt === '') {
            json_response(false, 'Please select a new delivery date.', null, 400);
        }
        $notes = trim((string) ($order['shipment_notes'] ?? ''));
        $line = 'RIDER_RESCHEDULED: ' . $rescheduleReason . ' | Scheduled: ' . $rescheduleAt;
        $notes = $notes !== '' ? $notes . "\n" . $line : $line;
        $ok = mobile_update_delivery_status($db, $orderId, 'delivered_to_rider', array_merge($loc, [
            'notes' => $notes, 'updated_at' => $now,
        ]));
        $result = ['success' => $ok, 'message' => $ok ? 'Delivery rescheduled.' : 'Unable to reschedule.'];
    } elseif ($status === 'customer_cancelled_at_delivery') {
        if ($current !== 'to_receive') {
            json_response(false, 'Customer cancellation only allowed while out for delivery.', null, 400);
        }
        $cancelReason = trim((string) ($_POST['cancel_reason'] ?? 'Customer cancelled at delivery location.'));
        $db->beginTransaction();
        try {
            mobile_restore_order_stock($db, $orderId);
            $notes = trim((string) ($order['shipment_notes'] ?? ''));
            $line = 'CUSTOMER_CANCELLED_AT_DOOR: ' . $cancelReason . ' (' . $now . ')';
            $notes = $notes !== '' ? $notes . "\n" . $line : $line;
            $db->prepare('UPDATE orders SET status = :s, updated_at = :now WHERE id = :id')
                ->execute([':s' => 'cancelled', ':now' => $now, ':id' => $orderId]);
            mobile_update_delivery_status($db, $orderId, 'cancelled', ['notes' => $notes, 'updated_at' => $now]);
            $db->commit();
            $result = ['success' => true, 'message' => 'Order cancelled at delivery.'];
        } catch (Throwable $e) {
            $db->rollBack();
            json_response(false, $e->getMessage(), null, 500);
        }
    } elseif ($status === 'failed_delivery') {
        if (! in_array($current, ['accepted_by_rider', 'delivered_to_rider', 'to_receive'], true)) {
            json_response(false, 'Delivery cannot be cancelled from current state.', null, 400);
        }
        $cancelReason = trim((string) ($_POST['cancel_reason'] ?? 'No reason provided'));
        $notes = trim((string) ($order['shipment_notes'] ?? ''));
        $line = 'RIDER_CANCELLED: ' . $cancelReason . ' (' . $now . ')';
        $notes = $notes !== '' ? $notes . "\n" . $line : $line;
        $ok = mobile_update_delivery_status($db, $orderId, 'failed_delivery', array_merge($loc, [
            'notes' => $notes, 'updated_at' => $now,
        ]));
        $result = ['success' => $ok, 'message' => $ok ? 'Delivery marked as failed.' : 'Unable to update.'];
    } elseif ($status === 'accept_return_pickup') {
        if ($current !== 'return_approved') {
            json_response(false, 'Return pickup is not ready for acceptance.', null, 400);
        }
        $meta = mobile_parse_return_meta((string) ($order['shipment_notes'] ?? ''), (string) ($order['delivery_notes'] ?? '')) ?? [];
        $meta['rider_accepted_pickup_at'] = $now;
        $meta['rider_accepted_pickup_by'] = $riderId;
        $fields = mobile_merge_return_meta_shipment_fields(
            (string) ($order['shipment_notes'] ?? ''),
            (string) ($order['delivery_notes'] ?? ''),
            $meta
        );
        $ok = mobile_update_delivery_status($db, $orderId, 'return_approved', array_merge($fields, ['updated_at' => $now]));
        $result = ['success' => $ok, 'message' => $ok ? 'Return pickup accepted. Scan customer QR next.' : 'Unable to accept return pickup.'];
    } elseif ($status === 'return_picked_up') {
        if ($current !== 'return_approved') {
            json_response(false, 'Return is not approved yet.', null, 400);
        }
        $meta = mobile_parse_return_meta((string) ($order['shipment_notes'] ?? ''), (string) ($order['delivery_notes'] ?? '')) ?? [];
        if (trim((string) ($meta['rider_accepted_pickup_at'] ?? '')) === '') {
            json_response(false, 'Accept return pickup first before scanning QR.', null, 400);
        }
        $scan = trim((string) ($_POST['return_token'] ?? $_POST['return_qr_scan'] ?? ''));
        $parsed = null;
        if (preg_match('/OID:(\d+)/', $scan, $om) === 1 && preg_match('/TKN:([^|\s]+)/', $scan, $tm) === 1) {
            $parsed = ['order_id' => (int) $om[1], 'token' => trim($tm[1])];
        } elseif (preg_match('/^RET\d+[A-Z0-9]+$/i', $scan) === 1) {
            $parsed = ['token' => $scan];
        }
        if ($parsed === null) {
            json_response(false, 'Invalid return QR code.', null, 400);
        }
        if (isset($parsed['order_id']) && (int) $parsed['order_id'] !== $orderId) {
            json_response(false, 'QR code belongs to a different order.', null, 400);
        }
        $expected = (string) ($meta['return_token'] ?? '');
        if ($expected === '' || ! hash_equals($expected, (string) $parsed['token'])) {
            json_response(false, 'Return QR does not match this order.', null, 400);
        }
        $meta['qr_scanned_at'] = $now;
        $meta['qr_scanned_by'] = $riderId;
        $meta['status'] = 'return_picked_up';
        if (trim((string) ($meta['pending_refund_reference'] ?? '')) === '') {
            $meta['pending_refund_reference'] = 'QP' . $orderId . strtoupper(bin2hex(random_bytes(3)));
        }
        $fields = mobile_merge_return_meta_shipment_fields(
            (string) ($order['shipment_notes'] ?? ''),
            (string) ($order['delivery_notes'] ?? ''),
            $meta
        );
        $ok = mobile_update_delivery_status($db, $orderId, 'return_picked_up', array_merge($fields, [
            'picked_up_at' => $now, 'updated_at' => $now,
        ]));
        $result = ['success' => $ok, 'message' => $ok ? 'Return pickup recorded successfully.' : 'Unable to record return pickup.'];
    }

    if (! ($result['success'] ?? false)) {
        json_response(false, (string) ($result['message'] ?? 'Update failed.'), null, 400);
    }

    $updated = mobile_get_order($db, $orderId);
    json_response(true, (string) $result['message'], [
        'order' => $updated ? mobile_format_order_row($updated, true, $db) : null,
    ], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while updating delivery.', null, 500);
}
