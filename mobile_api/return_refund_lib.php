<?php
declare(strict_types=1);

/**
 * Return/refund helpers aligned with app/Helpers/return_refund_helper.php (web).
 */

function mobile_return_refund_statuses(): array
{
    return ['return_requested', 'return_approved', 'return_picked_up', 'return_refund'];
}

function mobile_is_return_refund_status(string $status): bool
{
    return in_array($status, mobile_return_refund_statuses(), true);
}

function mobile_parse_return_meta(?string $shipmentNotes, ?string $deliveryNotes = null): ?array
{
    foreach ([$deliveryNotes, $shipmentNotes] as $source) {
        $source = trim((string) $source);
        if ($source === '') {
            continue;
        }
        if (preg_match('/RETURN_META:({.+})/s', $source, $matches) !== 1) {
            continue;
        }
        $decoded = json_decode($matches[1], true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }

    return null;
}

function mobile_strip_return_meta_from_notes(?string $notes): string
{
    $notes = trim((string) $notes);
    if ($notes === '') {
        return '';
    }

    while (($pos = strpos($notes, 'RETURN_META:')) !== false) {
        $jsonStart = $pos + strlen('RETURN_META:');
        if (! isset($notes[$jsonStart]) || $notes[$jsonStart] !== '{') {
            $notes = substr($notes, 0, $pos) . substr($notes, $pos + strlen('RETURN_META:'));
            continue;
        }

        $depth = 0;
        $len = strlen($notes);
        $end = null;
        for ($i = $jsonStart; $i < $len; $i++) {
            $ch = $notes[$i];
            if ($ch === '{') {
                $depth++;
            } elseif ($ch === '}') {
                $depth--;
                if ($depth === 0) {
                    $end = $i;
                    break;
                }
            }
        }

        if ($end === null) {
            break;
        }

        $before = rtrim(substr($notes, 0, $pos));
        $after = ltrim(substr($notes, $end + 1));
        $notes = $before === '' ? $after : ($after === '' ? $before : $before . "\n" . $after);
    }

    return trim($notes);
}

function mobile_build_return_meta_line(array $meta): string
{
    return 'RETURN_META:' . json_encode($meta, JSON_UNESCAPED_UNICODE);
}

function mobile_merge_return_meta_into_delivery_notes(string $existingDeliveryNotes, array $meta): string
{
    $line = mobile_build_return_meta_line($meta);
    $humanNotes = mobile_strip_return_meta_from_notes($existingDeliveryNotes);

    if ($humanNotes === '') {
        return $line;
    }

    return $humanNotes . "\n" . $line;
}

function mobile_merge_return_meta_shipment_fields(string $shipmentNotes, string $deliveryNotes, array $meta): array
{
    return [
        'notes' => mobile_strip_return_meta_from_notes($shipmentNotes),
        'delivery_notes' => mobile_merge_return_meta_into_delivery_notes($deliveryNotes, $meta),
    ];
}

function mobile_return_refund_requires_payout(string $type): bool
{
    return in_array($type, ['refund', 'return_and_refund', 'damaged_item'], true);
}

function mobile_validate_return_refund_request_type(string $type): string
{
    $types = ['return_and_refund' => true, 'damaged_item' => true];

    return isset($types[$type]) ? $type : 'return_and_refund';
}

function mobile_normalize_payout_method(string $method): string
{
    $method = strtolower(trim($method));
    if ($method === 'gcash' || str_contains($method, 'gcash')) {
        return 'gcash';
    }
    if ($method === 'maya' || str_contains($method, 'maya')) {
        return 'maya';
    }

    return $method;
}

function mobile_return_payout_methods(): array
{
    return ['gcash' => 'GCash', 'maya' => 'Maya'];
}

function mobile_validate_return_payout_details(string $method, string $account, string $accountName): array
{
    $methods = mobile_return_payout_methods();
    if (! array_key_exists($method, $methods)) {
        return ['valid' => false, 'message' => 'Select a valid payout method (GCash or Maya).'];
    }

    $account = trim($account);
    $accountName = trim($accountName);

    if ($accountName === '' || strlen($accountName) < 2) {
        return ['valid' => false, 'message' => 'Enter the account name registered to the e-wallet.'];
    }

    if ($account === '') {
        return ['valid' => false, 'message' => 'Enter your GCash or Maya mobile number.'];
    }

    $digits = preg_replace('/\D+/', '', $account) ?? '';
    if (strlen($digits) < 10 || strlen($digits) > 13) {
        return ['valid' => false, 'message' => 'Enter a valid Philippine mobile number for GCash/Maya.'];
    }

    return ['valid' => true, 'message' => ''];
}

function mobile_normalize_payout_account(string $method, string $account): string
{
    $account = trim($account);
    if ($account === '' || ! in_array($method, ['gcash', 'maya'], true)) {
        return $account;
    }

    $digits = preg_replace('/\D+/', '', $account) ?? '';
    if ($digits === '') {
        return $account;
    }

    if (str_starts_with($digits, '63') && strlen($digits) === 12) {
        return '+63' . substr($digits, 2);
    }

    if (str_starts_with($digits, '0') && strlen($digits) === 11) {
        return '+63' . substr($digits, 1);
    }

    if (strlen($digits) === 10 && str_starts_with($digits, '9')) {
        return '+63' . $digits;
    }

    return $account;
}

function mobile_generate_return_qr_token(int $orderId): string
{
    return 'RET' . $orderId . strtoupper(bin2hex(random_bytes(6)));
}

function mobile_build_return_qr_payload(int $orderId, string $token, string $reference): string
{
    return 'QUICKPUFF_RETURN|OID:' . $orderId . '|TKN:' . $token . '|REF:' . $reference;
}

function mobile_customer_can_request_return(array $order): array
{
    $status = (string) ($order['delivery_status'] ?? '');

    if (mobile_is_return_refund_status($status)) {
        return ['allowed' => false, 'message' => 'A return/refund request is already in progress for this order.'];
    }

    if ($status !== 'completed') {
        return ['allowed' => false, 'message' => 'Only completed orders can request a return or refund.'];
    }

    $paymentStatus = strtolower((string) ($order['payment_status'] ?? 'unpaid'));
    if ($paymentStatus !== 'paid') {
        return ['allowed' => false, 'message' => 'Return/refund is available only for paid orders.'];
    }

    $referenceDate = (string) ($order['delivered_at'] ?? $order['completed_at'] ?? $order['updated_at'] ?? '');
    if ($referenceDate === '') {
        return ['allowed' => false, 'message' => 'Unable to verify the return window for this order.'];
    }

    $windowDays = 7;
    $deadline = strtotime($referenceDate . ' +' . $windowDays . ' days');
    if ($deadline !== false && time() > $deadline) {
        return [
            'allowed' => false,
            'message' => 'Return/refund requests must be filed within ' . $windowDays . ' days after delivery.',
        ];
    }

    return ['allowed' => true, 'message' => ''];
}

function mobile_find_order_for_customer(PDO $db, int $userId, int $orderId, string $reference): ?array
{
    if ($orderId > 0) {
        $stmt = $db->prepare(
            'SELECT o.id, o.reference_number, o.status AS order_status, o.updated_at,
                    COALESCE(s.status, \'to_pay\') AS delivery_status,
                    s.notes AS shipment_notes, s.delivery_notes, s.delivered_at, s.completed_at,
                    COALESCE(p.status, \'unpaid\') AS payment_status
             FROM orders o
             LEFT JOIN order_shipments s ON s.order_id = o.id
             LEFT JOIN order_payments p ON p.order_id = o.id
             WHERE o.id = :order_id AND o.customer_id = :customer_id
             LIMIT 1'
        );
        $stmt->execute([':order_id' => $orderId, ':customer_id' => $userId]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    $reference = trim($reference);
    if ($reference === '') {
        return null;
    }

    $stmt = $db->prepare(
        'SELECT o.id, o.reference_number, o.status AS order_status, o.updated_at,
                COALESCE(s.status, \'to_pay\') AS delivery_status,
                s.notes AS shipment_notes, s.delivery_notes, s.delivered_at, s.completed_at,
                COALESCE(p.status, \'unpaid\') AS payment_status
         FROM orders o
         LEFT JOIN order_shipments s ON s.order_id = o.id
         LEFT JOIN order_payments p ON p.order_id = o.id
         WHERE o.customer_id = :customer_id AND o.reference_number = :reference
         LIMIT 1'
    );
    $stmt->execute([':customer_id' => $userId, ':reference' => $reference]);
    $row = $stmt->fetch();

    return is_array($row) ? $row : null;
}

/**
 * @return list<array<string, string>>
 */
function mobile_process_return_evidence_uploads(int $orderId): array
{
    $uploadPath = dirname(__DIR__) . '/writable/uploads/return_evidence/';
    if (! is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    $candidates = [];
    if (isset($_FILES['return_evidence'])) {
        $file = $_FILES['return_evidence'];
        if (is_array($file['name'] ?? null)) {
            $count = count($file['name']);
            for ($i = 0; $i < $count; $i++) {
                if (($file['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                    $candidates[] = [
                        'name' => (string) ($file['name'][$i] ?? ''),
                        'type' => (string) ($file['type'][$i] ?? ''),
                        'tmp_name' => (string) ($file['tmp_name'][$i] ?? ''),
                        'error' => (int) ($file['error'][$i] ?? UPLOAD_ERR_NO_FILE),
                        'size' => (int) ($file['size'][$i] ?? 0),
                    ];
                }
            }
        } elseif (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $candidates[] = $file;
        }
    }

    if ($candidates === []) {
        throw new InvalidArgumentException('Please upload at least one photo or video showing the product issue.');
    }

    $imageMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $videoMimes = ['video/mp4', 'video/webm', 'video/quicktime'];
    $allowedMimes = array_merge($imageMimes, $videoMimes);
    $maxImageBytes = 5 * 1024 * 1024;
    $maxVideoBytes = 25 * 1024 * 1024;
    $saved = [];

    foreach (array_slice($candidates, 0, 3) as $file) {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            continue;
        }

        $mime = (string) ($file['type'] ?? '');
        if ($mime === '' || $mime === 'application/octet-stream') {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $detected = finfo_file($finfo, (string) ($file['tmp_name'] ?? ''));
                finfo_close($finfo);
                if (is_string($detected) && $detected !== '') {
                    $mime = $detected;
                }
            }
        }

        if (! in_array($mime, $allowedMimes, true)) {
            throw new InvalidArgumentException('Only JPG, PNG, GIF, WEBP images and MP4, WEBM, MOV videos are allowed.');
        }

        $isVideo = in_array($mime, $videoMimes, true);
        $maxBytes = $isVideo ? $maxVideoBytes : $maxImageBytes;
        if ((int) ($file['size'] ?? 0) > $maxBytes) {
            throw new InvalidArgumentException(
                $isVideo ? 'Each video must be 25MB or smaller.' : 'Each image must be 5MB or smaller.'
            );
        }

        $originalName = (string) ($file['name'] ?? 'evidence');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if ($extension === '') {
            $extension = $isVideo ? 'mp4' : 'jpg';
        }

        $filename = 'return_evidence_' . $orderId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || ! is_uploaded_file($tmpName) || ! move_uploaded_file($tmpName, $uploadPath . $filename)) {
            continue;
        }

        $saved[] = [
            'filename' => $filename,
            'type' => $isVideo ? 'video' : 'image',
            'original_name' => $originalName,
            'uploaded_at' => date('Y-m-d H:i:s'),
        ];
    }

    if ($saved === []) {
        throw new InvalidArgumentException('Unable to save uploaded evidence. Please try again.');
    }

    return $saved;
}

function mobile_update_delivery_status(PDO $db, int $orderId, string $status, array $shipmentData): bool
{
    $now = date('Y-m-d H:i:s');
    $shipmentData['status'] = $status;
    $shipmentData['updated_at'] = $now;

    $existing = $db->prepare('SELECT id FROM order_shipments WHERE order_id = :order_id LIMIT 1');
    $existing->execute([':order_id' => $orderId]);
    $row = $existing->fetch();

    if (is_array($row)) {
        $sets = [];
        $params = [':order_id' => $orderId];
        foreach ($shipmentData as $key => $value) {
            $sets[] = $key . ' = :' . $key;
            $params[':' . $key] = $value;
        }
        $sql = 'UPDATE order_shipments SET ' . implode(', ', $sets) . ' WHERE order_id = :order_id';
        $stmt = $db->prepare($sql);

        return $stmt->execute($params);
    }

    $shipmentData['order_id'] = $orderId;
    $shipmentData['created_at'] = $now;
    $columns = array_keys($shipmentData);
    $placeholders = array_map(static fn (string $col): string => ':' . $col, $columns);
    $params = [];
    foreach ($shipmentData as $key => $value) {
        $params[':' . $key] = $value;
    }
    $sql = 'INSERT INTO order_shipments (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
    $stmt = $db->prepare($sql);

    return $stmt->execute($params);
}
