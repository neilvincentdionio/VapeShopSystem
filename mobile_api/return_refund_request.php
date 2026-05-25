<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/return_refund_lib.php';

require_post();

$email = normalize_email((string) ($_POST['email'] ?? ''));
$orderId = (int) ($_POST['order_id'] ?? 0);
$reference = trim((string) ($_POST['order_reference'] ?? $_POST['reference_number'] ?? $_POST['related_order'] ?? ''));
$requestType = mobile_validate_return_refund_request_type((string) ($_POST['request_type'] ?? 'return_and_refund'));
$reason = trim((string) ($_POST['reason'] ?? ''));

$payoutMethod = mobile_normalize_payout_method((string) ($_POST['payout_method'] ?? $_POST['refund_method'] ?? ''));
$payoutAccount = trim((string) ($_POST['payout_account'] ?? ''));
$payoutAccountName = trim((string) ($_POST['payout_account_name'] ?? ''));

// Legacy mobile combined "number (name)" in refund_account
if ($payoutAccountName === '' && $payoutAccount !== '' && preg_match('/^(.+?)\s*\(([^)]+)\)\s*$/', $payoutAccount, $m) === 1) {
    $payoutAccount = trim($m[1]);
    $payoutAccountName = trim($m[2]);
}

if ($reason === '' || strlen($reason) < 10) {
    json_response(false, 'Please provide a reason (at least 10 characters).', null, 400);
}

try {
    $db = mobile_db();
    $user = find_user_by_email($db, $email);
    if (!$user) {
        json_response(false, 'User not found.', null, 404);
    }
    $userId = (int) $user['id'];

    $order = mobile_find_order_for_customer($db, $userId, $orderId, $reference);
    if ($order === null) {
        json_response(false, 'Order not found.', null, 404);
    }

    $orderId = (int) $order['id'];
    $eligibility = mobile_customer_can_request_return($order);
    if (!$eligibility['allowed']) {
        json_response(false, $eligibility['message'], null, 400);
    }

    if (mobile_return_refund_requires_payout($requestType)) {
        $payoutCheck = mobile_validate_return_payout_details($payoutMethod, $payoutAccount, $payoutAccountName);
        if (!$payoutCheck['valid']) {
            json_response(false, $payoutCheck['message'], null, 400);
        }
    }

    try {
        $evidenceFiles = mobile_process_return_evidence_uploads($orderId);
    } catch (InvalidArgumentException $e) {
        json_response(false, $e->getMessage(), null, 400);
    }

    $referenceNumber = (string) ($order['reference_number'] ?? ('#' . $orderId));
    $returnToken = mobile_generate_return_qr_token($orderId);

    $meta = [
        'type' => $requestType,
        'reason' => $reason,
        'requested_at' => date('Y-m-d H:i:s'),
        'customer_id' => $userId,
        'status' => 'return_requested',
        'return_token' => $returnToken,
        'qr_payload' => mobile_build_return_qr_payload($orderId, $returnToken, $referenceNumber),
        'evidence_files' => $evidenceFiles,
    ];

    if (mobile_return_refund_requires_payout($requestType)) {
        $meta['payout_method'] = $payoutMethod;
        $meta['payout_account'] = mobile_normalize_payout_account($payoutMethod, $payoutAccount);
        $meta['payout_account_name'] = $payoutAccountName;
        $meta['payout_collected_by'] = 'customer';
    }

    $returnFields = mobile_merge_return_meta_shipment_fields(
        (string) ($order['shipment_notes'] ?? ''),
        (string) ($order['delivery_notes'] ?? ''),
        $meta
    );

    $updated = mobile_update_delivery_status($db, $orderId, 'return_requested', $returnFields);
    if (!$updated) {
        json_response(false, 'Unable to submit return/refund request.', null, 500);
    }

    json_response(true, 'Return/refund request submitted. Show your return QR code to the rider during pickup.', [
        'order_id' => $orderId,
        'reference_number' => $referenceNumber,
        'delivery_status' => 'return_requested',
        'return_token' => $returnToken,
        'qr_payload' => $meta['qr_payload'],
        'return_meta' => $meta,
    ], 200);
} catch (Throwable $e) {
    json_response(false, 'Server error while submitting return/refund request.', null, 500);
}
