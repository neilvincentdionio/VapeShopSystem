<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/auth_lib.php';
require_once __DIR__ . '/order_lib.php';

require_post();

$email = normalize_email((string) ($_POST['email'] ?? ''));
$orderId = (int) ($_POST['order_id'] ?? 0);
$deliveryNotes = trim((string) ($_POST['delivery_notes'] ?? ''));
$finalLat = mobile_parse_latitude($_POST['final_rider_latitude'] ?? null);
$finalLng = mobile_parse_longitude($_POST['final_rider_longitude'] ?? null);

if ($orderId <= 0) {
    json_response(false, 'order_id is required.', null, 400);
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
    if ((string) ($order['delivery_status'] ?? '') !== 'to_receive') {
        json_response(false, 'Order must be out for delivery before completion.', null, 400);
    }

    if (! isset($_FILES['delivery_proof']) || ($_FILES['delivery_proof']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        json_response(false, 'Please upload a delivery proof photo.', null, 400);
    }

    $file = $_FILES['delivery_proof'];
    $mime = (string) ($file['type'] ?? '');
    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (! in_array($mime, $allowed, true)) {
        json_response(false, 'Only JPEG, PNG, and GIF images are allowed.', null, 400);
    }
    if ((int) ($file['size'] ?? 0) > 5 * 1024 * 1024) {
        json_response(false, 'Image must be 5MB or smaller.', null, 400);
    }

    $effectiveLat = $finalLat ?? (isset($order['rider_latitude']) ? (float) $order['rider_latitude'] : null);
    $effectiveLng = $finalLng ?? (isset($order['rider_longitude']) ? (float) $order['rider_longitude'] : null);
    $deliveryLat = isset($order['delivery_latitude']) ? (float) $order['delivery_latitude'] : null;
    $deliveryLng = isset($order['delivery_longitude']) ? (float) $order['delivery_longitude'] : null;

    $resolved = mobile_resolve_rider_proof_coordinates($order, $effectiveLat, $effectiveLng);
    $effectiveLat = $resolved['lat'];
    $effectiveLng = $resolved['lng'];

    if ($effectiveLat !== null && $effectiveLng !== null && $deliveryLat !== null && $deliveryLng !== null) {
        $maxMeters = mobile_delivery_completion_max_distance_meters();
        if (mobile_distance_meters($effectiveLat, $effectiveLng, $deliveryLat, $deliveryLng) > $maxMeters) {
            json_response(false, 'You are too far from the customer location to complete delivery.', null, 400);
        }
    }

    $uploadPath = dirname(__DIR__) . '/writable/uploads/delivery_proofs/';
    if (! is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    if ($ext === '') {
        $ext = 'jpg';
    }
    $filename = 'delivery_proof_' . $orderId . '_' . time() . '.' . $ext;
    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || ! is_uploaded_file($tmp) || ! move_uploaded_file($tmp, $uploadPath . $filename)) {
        json_response(false, 'Unable to save delivery proof.', null, 500);
    }

    $now = date('Y-m-d H:i:s');
    $extra = [
        'delivery_proof_image' => $filename,
        'delivery_notes' => $deliveryNotes,
        'delivery_proof_submitted_at' => $now,
        'delivered_at' => $now,
        'updated_at' => $now,
    ];
    if ($effectiveLat !== null && $effectiveLng !== null) {
        $extra['final_rider_latitude'] = $effectiveLat;
        $extra['final_rider_longitude'] = $effectiveLng;
        $extra['delivered_latitude'] = $effectiveLat;
        $extra['delivered_longitude'] = $effectiveLng;
    }

    $ok = mobile_update_delivery_status($db, $orderId, 'delivered', $extra);
    if (! $ok) {
        json_response(false, 'Unable to mark order as delivered.', null, 500);
    }

    $updated = mobile_get_order($db, $orderId);
    json_response(true, 'Delivery proof submitted. Waiting for customer confirmation.', [
        'order' => $updated ? mobile_format_order_row($updated, true, $db) : null,
    ], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while submitting proof.', null, 500);
}
