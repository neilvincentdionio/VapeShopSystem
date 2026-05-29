<?php

if (! function_exists('record_format_return_refund_display')) {
    /**
     * Human-readable return/refund label for admin records.
     *
     * @param array<string, mixed>|null $returnMeta Parsed RETURN_META from order shipment notes.
     */
    function record_format_return_refund_display(
        ?string $orderDeliveryStatus,
        ?string $recordStatus = null,
        ?array $returnMeta = null
    ): string {
        helper('return_refund');

        $orderDeliveryStatus = strtolower(trim((string) $orderDeliveryStatus));
        $recordStatus = strtolower(trim((string) $recordStatus));
        $requestType = is_array($returnMeta) ? (string) ($returnMeta['type'] ?? '') : '';
        $damagedCount = is_array($returnMeta) && is_array($returnMeta['damaged_items'] ?? null)
            ? count($returnMeta['damaged_items'])
            : 0;

        $typeSuffix = '';
        if ($requestType === 'damaged_item') {
            $typeSuffix = 'Damaged Item';
        } elseif ($requestType !== '') {
            $typeSuffix = return_refund_type_label($requestType);
        }

        if ($orderDeliveryStatus !== '' && is_return_refund_status($orderDeliveryStatus)) {
            $label = 'Yes — ' . return_refund_status_label($orderDeliveryStatus);
            if ($typeSuffix !== '') {
                $label .= ' (' . $typeSuffix . ')';
            }
            if ($damagedCount > 0) {
                $label .= ' · ' . $damagedCount . ' damaged line(s)';
            }

            return $label;
        }

        if ($recordStatus === 'return_refund') {
            if ($typeSuffix !== '') {
                return 'Yes — ' . $typeSuffix;
            }

            return 'Yes — Return/Refund';
        }

        return 'No';
    }
}

if (! function_exists('record_damaged_inventory_reference')) {
    function record_damaged_inventory_reference(string $orderReference, int $productId, int $variantId = 0): string
    {
        $base = preg_replace('/[^A-Za-z0-9\-_]/', '', $orderReference) ?? $orderReference;

        return $base . '-DMG-' . $productId . '-' . max(0, $variantId);
    }
}

if (! function_exists('record_is_damaged_inventory_reference')) {
    function record_is_damaged_inventory_reference(string $referenceNumber): bool
    {
        return preg_match('/-DMG-\d+-\d+$/', trim($referenceNumber)) === 1;
    }
}

if (! function_exists('record_format_status_cell')) {
    /**
     * Single status label for records table (includes return/refund + damaged item detail).
     *
     * @param array<string, mixed> $record
     */
    function record_format_status_cell(array $record): string
    {
        helper('return_refund');

        $status = strtolower(trim((string) ($record['status'] ?? 'pending')));
        $recordType = strtolower(trim((string) ($record['record_type'] ?? '')));
        $reference = trim((string) ($record['reference_number'] ?? ''));

        if ($recordType === 'inventory' && record_is_damaged_inventory_reference($reference)) {
            return 'Damaged Item';
        }

        $returnLine = trim((string) ($record['return_refund_display'] ?? ''));
        if (
            $status === 'return_refund'
            || ($returnLine !== '' && strcasecmp($returnLine, 'No') !== 0 && $returnLine !== '—')
            || record_has_return_refund(
                (string) ($record['order_delivery_status'] ?? ''),
                $status,
                $reference
            )
        ) {
            return 'Return/Refund';
        }

        $labels = [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
}

if (! function_exists('record_status_badge_class')) {
    /**
     * CSS class for records status pill (matches admin returns/orders badge style).
     *
     * @param array<string, mixed> $record
     */
    function record_status_badge_class(array $record): string
    {
        $status = strtolower(trim((string) ($record['status'] ?? 'pending')));
        $recordType = strtolower(trim((string) ($record['record_type'] ?? '')));
        $reference = trim((string) ($record['reference_number'] ?? ''));
        $label = strtolower(record_format_status_cell($record));

        if (
            ($recordType === 'inventory' && record_is_damaged_inventory_reference($reference))
            || str_contains($label, 'damaged item')
        ) {
            return 'status-damaged';
        }

        if ($status === 'return_refund' || str_contains($label, 'refund') || str_contains($label, 'return')) {
            return 'status-return-refund';
        }

        if ($status === 'completed') {
            return 'status-completed';
        }

        if ($status === 'cancelled') {
            return 'status-cancelled';
        }

        return 'status-pending';
    }
}

if (! function_exists('record_has_return_refund')) {
    function record_has_return_refund(
        ?string $orderDeliveryStatus,
        ?string $recordStatus = null,
        ?string $referenceNumber = null
    ): bool {
        helper('return_refund');

        $orderDeliveryStatus = strtolower(trim((string) $orderDeliveryStatus));
        if ($orderDeliveryStatus !== '' && is_return_refund_status($orderDeliveryStatus)) {
            return true;
        }

        if (strtolower(trim((string) $recordStatus)) === 'return_refund') {
            return true;
        }

        return record_is_damaged_inventory_reference((string) $referenceNumber);
    }
}
