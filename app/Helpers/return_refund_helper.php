<?php

if (! function_exists('return_refund_statuses')) {
    /**
     * @return list<string>
     */
    function return_refund_statuses(): array
    {
        return ['return_requested', 'return_approved', 'return_picked_up', 'return_refund'];
    }
}

if (! function_exists('is_return_refund_status')) {
    function is_return_refund_status(string $status): bool
    {
        return in_array($status, return_refund_statuses(), true);
    }
}

if (! function_exists('return_refund_status_label')) {
    function return_refund_status_label(string $status): string
    {
        $labels = [
            'return_requested' => 'Return Requested',
            'return_approved' => 'Return Approved',
            'return_picked_up' => 'Return Picked Up',
            'return_refund' => 'Refund Completed',
        ];

        return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
}

if (! function_exists('customer_return_status_notice')) {
    /**
     * Customer-facing status message after return workflow steps.
     */
    function customer_return_status_notice(string $deliveryStatus, array $returnMeta = []): ?string
    {
        $requestType = (string) ($returnMeta['type'] ?? 'return_and_refund');

        if ($deliveryStatus === 'return_picked_up') {
            if (return_refund_requires_payout($requestType)) {
                $method = strtolower((string) ($returnMeta['payout_method'] ?? ''));
                if ($method === 'gcash' || $method === 'maya') {
                    return 'Wait for the admin to send your refund via ' . return_payout_method_label($method) . '.';
                }

                return 'Wait for the admin to send your refund via GCash or Maya.';
            }

            return 'Your return was picked up. Please wait for the admin to complete your request.';
        }

        if ($deliveryStatus === 'return_refund') {
            return 'Your refund has been processed. Thank you for your patience.';
        }

        if ($deliveryStatus === 'return_approved') {
            return 'Return approved. Show your return QR code to the rider when they arrive for pickup.';
        }

        if ($deliveryStatus === 'return_requested') {
            return 'Your return request was submitted. Please wait for admin approval.';
        }

        return null;
    }
}

if (! function_exists('return_refund_request_window_days')) {
    function return_refund_request_window_days(): int
    {
        return 7;
    }
}

if (! function_exists('strip_return_meta_from_notes')) {
    /**
     * Remove RETURN_META JSON blobs from shipment/delivery notes (nested JSON safe).
     */
    function strip_return_meta_from_notes(?string $notes): string
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
}

if (! function_exists('delivery_notes_for_display')) {
    function delivery_notes_for_display(?string $notes): string
    {
        return strip_return_meta_from_notes($notes);
    }
}

if (! function_exists('shipment_notes_for_display')) {
    function shipment_notes_for_display(?string $notes): string
    {
        $notes = strip_return_meta_from_notes($notes);
        if (function_exists('strip_reschedule_meta_from_notes')) {
            $notes = strip_reschedule_meta_from_notes($notes);
        }

        return trim($notes);
    }
}

if (! function_exists('parse_return_meta')) {
    /**
     * @return array<string, mixed>|null
     */
    function parse_return_meta(?string $shipmentNotes, ?string $deliveryNotes = null): ?array
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
}

if (! function_exists('build_return_meta_line')) {
    /**
     * @param array<string, mixed> $meta
     */
    function build_return_meta_line(array $meta): string
    {
        return 'RETURN_META:' . json_encode($meta, JSON_UNESCAPED_UNICODE);
    }
}

if (! function_exists('merge_return_meta_into_delivery_notes')) {
    /**
     * @param array<string, mixed> $meta
     */
    function merge_return_meta_into_delivery_notes(string $existingDeliveryNotes, array $meta): string
    {
        $line = build_return_meta_line($meta);
        $humanNotes = strip_return_meta_from_notes($existingDeliveryNotes);

        if ($humanNotes === '') {
            return $line;
        }

        return $humanNotes . "\n" . $line;
    }
}

if (! function_exists('merge_return_meta_shipment_fields')) {
    /**
     * Keep address description in shipment notes; store return metadata in delivery_notes.
     *
     * @param array<string, mixed> $meta
     * @return array{notes: string, delivery_notes: string}
     */
    function merge_return_meta_shipment_fields(string $shipmentNotes, string $deliveryNotes, array $meta): array
    {
        return [
            'notes' => strip_return_meta_from_notes($shipmentNotes),
            'delivery_notes' => merge_return_meta_into_delivery_notes($deliveryNotes, $meta),
        ];
    }
}

if (! function_exists('merge_shipment_notes_with_return_meta')) {
    /**
     * @deprecated Use merge_return_meta_shipment_fields() instead.
     * @param array<string, mixed> $meta
     */
    function merge_shipment_notes_with_return_meta(string $existingNotes, array $meta): string
    {
        return merge_return_meta_into_delivery_notes($existingNotes, $meta);
    }
}

if (! function_exists('customer_can_request_return')) {
    /**
     * @param array<string, mixed> $order
     * @return array{allowed: bool, message: string}
     */
    function customer_can_request_return(array $order): array
    {
        $status = (string) ($order['delivery_status'] ?? '');

        if (is_return_refund_status($status)) {
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

        $deadline = strtotime($referenceDate . ' +' . return_refund_request_window_days() . ' days');
        if ($deadline !== false && time() > $deadline) {
            return [
                'allowed' => false,
                'message' => 'Return/refund requests must be filed within ' . return_refund_request_window_days() . ' days after delivery.',
            ];
        }

        return ['allowed' => true, 'message' => ''];
    }
}

if (! function_exists('return_refund_request_types')) {
    /**
     * @return array<string, string>
     */
    function return_refund_request_types(): array
    {
        return [
            'return_and_refund' => 'Return & Refund',
            'damaged_item' => 'Damaged Item',
        ];
    }
}

if (! function_exists('validate_return_refund_request_type')) {
    function validate_return_refund_request_type(string $type): string
    {
        return array_key_exists($type, return_refund_request_types()) ? $type : 'return_and_refund';
    }
}

if (! function_exists('return_item_stock_key')) {
    function return_item_stock_key(int $productId, ?int $variantId = null): string
    {
        return $productId . ':' . max(0, (int) ($variantId ?? 0));
    }
}

if (! function_exists('parse_return_damaged_item_keys')) {
    /**
     * @return list<string>
     */
    function parse_return_damaged_item_keys(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $keys = [];
        foreach ($raw as $value) {
            $key = trim((string) $value);
            if (preg_match('/^\d+:\d+$/', $key) === 1) {
                $keys[] = $key;
            }
        }

        return array_values(array_unique($keys));
    }
}

if (! function_exists('return_refund_type_label')) {
    function return_refund_type_label(string $type): string
    {
        return return_refund_request_types()[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }
}

if (! function_exists('return_payout_methods')) {
    /**
     * @return array<string, string>
     */
    function return_payout_methods(): array
    {
        return [
            'gcash' => 'GCash',
            'maya' => 'Maya',
        ];
    }
}

if (! function_exists('return_refund_requires_payout')) {
    function return_refund_requires_payout(string $type): bool
    {
        return in_array($type, ['refund', 'return_and_refund', 'damaged_item'], true);
    }
}

if (! function_exists('return_payout_method_label')) {
    function return_payout_method_label(string $method): string
    {
        return return_payout_methods()[$method] ?? ucfirst(str_replace('_', ' ', $method));
    }
}

if (! function_exists('generate_return_qr_token')) {
    function generate_return_qr_token(int $orderId): string
    {
        return 'RET' . $orderId . strtoupper(bin2hex(random_bytes(6)));
    }
}

if (! function_exists('build_return_qr_payload')) {
    function build_return_qr_payload(int $orderId, string $token, string $reference): string
    {
        return 'QUICKPUFF_RETURN|OID:' . $orderId . '|TKN:' . $token . '|REF:' . $reference;
    }
}

if (! function_exists('return_qr_image_url')) {
    function return_qr_image_url(string $payload, int $size = 220): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . rawurlencode($payload);
    }
}

if (! function_exists('parse_return_qr_scan')) {
    /**
     * @return array{order_id?: int, token: string}|null
     */
    function parse_return_qr_scan(string $scan): ?array
    {
        $scan = trim($scan);
        if ($scan === '') {
            return null;
        }

        if (preg_match('/OID:(\d+)/', $scan, $orderMatch) === 1 && preg_match('/TKN:([^|\s]+)/', $scan, $tokenMatch) === 1) {
            return [
                'order_id' => (int) $orderMatch[1],
                'token' => trim((string) $tokenMatch[1]),
            ];
        }

        if (preg_match('/^RET\d+[A-Z0-9]+$/i', $scan) === 1) {
            return ['token' => $scan];
        }

        return null;
    }
}

if (! function_exists('normalize_payout_account')) {
    function normalize_payout_account(string $method, string $account): string
    {
        $account = trim($account);
        if ($account === '') {
            return '';
        }

        if (! in_array($method, ['gcash', 'maya'], true)) {
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
}

if (! function_exists('validate_return_payout_details')) {
    /**
     * @return array{valid: bool, message: string}
     */
    function validate_return_payout_details(string $method, string $account, string $accountName): array
    {
        $methods = return_payout_methods();
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
}

if (! function_exists('rider_accepted_return_pickup')) {
    /**
     * @param array<string, mixed> $meta
     */
    function rider_accepted_return_pickup(array $meta): bool
    {
        return trim((string) ($meta['rider_accepted_pickup_at'] ?? '')) !== '';
    }
}

if (! function_exists('rider_return_list_dismissed')) {
    /**
     * @param array<string, mixed> $meta
     */
    function rider_return_list_dismissed(array $meta): bool
    {
        return trim((string) ($meta['rider_list_dismissed_at'] ?? '')) !== '';
    }
}

if (! function_exists('dismiss_rider_return_from_list')) {
    /**
     * @param array<string, mixed> $meta
     * @return array<string, mixed>
     */
    function dismiss_rider_return_from_list(array $meta): array
    {
        $meta['rider_list_dismissed_at'] = date('Y-m-d H:i:s');

        return $meta;
    }
}

if (! function_exists('filter_rider_visible_return_pickups')) {
    /**
     * @param array<int, array<string, mixed>> $orders
     * @return array<int, array<string, mixed>>
     */
    function filter_rider_visible_return_pickups(array $orders): array
    {
        return array_values(array_filter($orders, static function (array $order): bool {
            $meta = parse_return_meta(
                (string) ($order['shipment_notes'] ?? ''),
                (string) ($order['delivery_notes'] ?? '')
            ) ?? [];

            return ! rider_return_list_dismissed($meta);
        }));
    }
}

if (! function_exists('return_payout_summary')) {
    /**
     * @param array<string, mixed> $meta
     */
    function return_payout_summary(array $meta): string
    {
        $method = (string) ($meta['payout_method'] ?? '');
        $account = (string) ($meta['payout_account'] ?? '');
        $name = (string) ($meta['payout_account_name'] ?? '');

        if ($method === '') {
            return 'Not provided yet';
        }

        if ($account === '') {
            return 'Not provided yet';
        }

        return return_payout_method_label($method) . ': ' . $account . ' — ' . $name;
    }
}

if (! function_exists('generate_pending_refund_reference')) {
    function generate_pending_refund_reference(int $orderId): string
    {
        return 'QP' . $orderId . strtoupper(bin2hex(random_bytes(3)));
    }
}

if (! function_exists('ensure_pending_refund_reference')) {
    /**
     * @param array<string, mixed> $meta
     * @return array<string, mixed>
     */
    function ensure_pending_refund_reference(array $meta, int $orderId): array
    {
        if (trim((string) ($meta['pending_refund_reference'] ?? '')) === '') {
            $meta['pending_refund_reference'] = generate_pending_refund_reference($orderId);
        }

        return $meta;
    }
}

if (! function_exists('payout_account_for_send')) {
    function payout_account_for_send(string $account): string
    {
        $digits = preg_replace('/\D+/', '', $account) ?? '';
        if ($digits === '') {
            return trim($account);
        }

        if (str_starts_with($digits, '63') && strlen($digits) === 12) {
            return '0' . substr($digits, 2);
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return $digits;
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '9')) {
            return '0' . $digits;
        }

        return $digits;
    }
}

if (! function_exists('build_refund_send_clipboard_text')) {
    /**
     * @param array<string, mixed> $meta
     */
    function build_refund_send_clipboard_text(array $meta, float $amount, string $orderReference): string
    {
        $method = return_payout_method_label((string) ($meta['payout_method'] ?? ''));
        $account = payout_account_for_send((string) ($meta['payout_account'] ?? ''));
        $name = trim((string) ($meta['payout_account_name'] ?? ''));
        $pendingRef = trim((string) ($meta['pending_refund_reference'] ?? ''));

        $lines = [
            'QuickPuff Return Refund',
            'Order: ' . $orderReference,
            'Send via: ' . $method,
            'Amount: PHP ' . number_format($amount, 2),
            'To: ' . $account . ($name !== '' ? ' (' . $name . ')' : ''),
        ];

        if ($pendingRef !== '') {
            $lines[] = 'Use this reference in GCash/Maya message: ' . $pendingRef;
        }

        return implode("\n", $lines);
    }
}

if (! function_exists('return_ewallet_open_url')) {
    function return_ewallet_open_url(string $method): string
    {
        $method = strtolower(trim($method));

        if ($method === 'maya') {
            return 'https://www.maya.ph/';
        }

        return 'https://www.gcash.com/';
    }
}

if (! function_exists('return_refund_resolve_qr_payload')) {
    /**
     * @param array<string, mixed> $meta
     */
    function return_refund_resolve_qr_payload(array $meta, int $orderId, string $orderReference): string
    {
        $payload = trim((string) ($meta['qr_payload'] ?? ''));
        if ($payload !== '') {
            return $payload;
        }

        $token = trim((string) ($meta['return_token'] ?? ''));
        if ($token === '' || $orderId <= 0) {
            return '';
        }

        return build_return_qr_payload($orderId, $token, $orderReference);
    }
}

if (! function_exists('format_refund_payout_reference_display')) {
    function format_refund_payout_reference_display(string $reference): string
    {
        $reference = trim($reference);
        if ($reference === '') {
            return '';
        }

        if (preg_match('/QuickPuff|ReturnRefund|Sendvia|Amount:PHP|Usethisreference/i', $reference) === 1) {
            if (preg_match('/QP\d+[A-F0-9]+/i', $reference, $matches) === 1) {
                return strtoupper($matches[0]);
            }

            return '';
        }

        if (strlen($reference) > 48) {
            if (preg_match('/QP\d+[A-F0-9]+/i', $reference, $matches) === 1) {
                return strtoupper($matches[0]);
            }

            return substr($reference, 0, 32) . '…';
        }

        return $reference;
    }
}

if (! function_exists('validate_admin_refund_payout_reference')) {
    /**
     * Validates GCash/Maya transaction reference on admin refund completion.
     * Allows "QWERTY" for testing only. Rejects QuickPuff message codes (QP…).
     *
     * @return array{valid: bool, message: string, normalized: string}
     */
    function validate_admin_refund_payout_reference(string $reference, int $orderId, string $pendingRef = ''): array
    {
        $reference = trim($reference);
        if ($reference === '') {
            return [
                'valid' => false,
                'message' => 'Enter or paste the GCash/Maya transaction reference from your send confirmation.',
                'normalized' => '',
            ];
        }

        $upper = strtoupper($reference);
        if ($upper === 'QWERTY') {
            return ['valid' => true, 'message' => '', 'normalized' => 'QWERTY'];
        }

        $pendingNorm = strtoupper(trim($pendingRef));
        if ($pendingNorm !== '' && $upper === $pendingNorm) {
            return [
                'valid' => false,
                'message' => 'Use the transaction reference from GCash/Maya after sending—not the QuickPuff message code (QP…).',
                'normalized' => '',
            ];
        }

        if ($orderId > 0 && preg_match('/^QP' . preg_quote((string) $orderId, '/') . '[A-F0-9]{6}$/i', $reference) === 1) {
            return [
                'valid' => false,
                'message' => 'The QP code is only for the payment message. Paste the transaction reference from GCash/Maya after you send.',
                'normalized' => '',
            ];
        }

        if (preg_match('/^QP\d+[A-F0-9]{6,}$/i', $reference) === 1) {
            return [
                'valid' => false,
                'message' => 'QuickPuff message codes (QP…) cannot be used as a transaction reference.',
                'normalized' => '',
            ];
        }

        $digitsOnly = preg_replace('/\D+/', '', $reference) ?? '';
        $compact = preg_replace('/\s+/', '', $reference) ?? '';

        if ($digitsOnly !== '' && preg_match('/^\d{10,13}$/', $digitsOnly) === 1) {
            return ['valid' => true, 'message' => '', 'normalized' => $digitsOnly];
        }

        if (preg_match('/^[A-Z0-9-]{8,24}$/i', $compact) === 1
            && preg_match('/\d/', $compact) === 1
            && ! preg_match('/^QP\d/i', $compact)) {
            return ['valid' => true, 'message' => '', 'normalized' => strtoupper($compact)];
        }

        return [
            'valid' => false,
            'message' => 'Enter a valid GCash/Maya transaction reference (10–13 digits from your send confirmation). For testing only, use QWERTY.',
            'normalized' => '',
        ];
    }
}

if (! function_exists('return_qr_download_filename')) {
    function return_qr_download_filename(int $orderId, string $orderReference): string
    {
        $slug = preg_replace('/[^A-Za-z0-9_-]+/', '-', $orderReference) ?? '';
        $slug = trim((string) $slug, '-');
        if ($slug === '') {
            $slug = 'order-' . $orderId;
        }

        return 'return-qr-' . strtolower($slug) . '.png';
    }
}

if (! function_exists('return_evidence_url')) {
    function return_evidence_url(string $filename): string
    {
        return site_url('uploads/return_evidence/' . rawurlencode($filename));
    }
}

if (! function_exists('return_evidence_is_video')) {
    function return_evidence_is_video(string $filename): bool
    {
        return (bool) preg_match('/\.(mp4|webm|mov|m4v)$/i', $filename);
    }
}

if (! function_exists('return_evidence_list')) {
    /**
     * @param array<string, mixed> $meta
     * @return list<array<string, string>>
     */
    function return_evidence_list(array $meta): array
    {
        $files = $meta['evidence_files'] ?? [];

        return is_array($files) ? array_values(array_filter($files, 'is_array')) : [];
    }
}
