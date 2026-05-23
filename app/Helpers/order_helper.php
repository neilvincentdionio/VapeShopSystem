<?php

if (! function_exists('customer_cancellable_statuses')) {
    /**
     * Delivery statuses where the customer may cancel (before out-for-delivery / shipping).
     *
     * @return list<string>
     */
    function customer_cancellable_statuses(): array
    {
        return [
            'to_pay',
            'to_ship',
            'ready_for_pickup',
            'accepted_by_rider',
        ];
    }
}

if (! function_exists('customer_can_cancel_order')) {
    function customer_can_cancel_order(array $order): bool
    {
        $status = strtolower(trim((string) ($order['delivery_status'] ?? '')));

        if ($status === '' || $status === 'cancelled' || is_return_refund_status($status)) {
            return false;
        }

        return in_array($status, customer_cancellable_statuses(), true);
    }
}

if (! function_exists('customer_cancel_requires_stock_restore')) {
    function customer_cancel_requires_stock_restore(array $order): bool
    {
        return strtolower(trim((string) ($order['delivery_status'] ?? ''))) !== 'to_pay';
    }
}

if (! function_exists('customer_cancel_unavailable_message')) {
    function customer_cancel_unavailable_message(array $order): string
    {
        $status = strtolower(trim((string) ($order['delivery_status'] ?? '')));

        if ($status === 'cancelled') {
            return 'This order is already cancelled.';
        }

        if (is_return_refund_status($status)) {
            return 'Return/refund orders cannot be cancelled from this page.';
        }

        if (in_array($status, ['delivered_to_rider', 'to_receive', 'delivered'], true)) {
            return 'This order was already picked up by the rider and cannot be cancelled.';
        }

        if ($status === 'completed') {
            return 'Completed orders cannot be cancelled. You may request a return/refund instead.';
        }

        if ($status === 'failed_delivery') {
            return 'This order cannot be cancelled. Please contact support if you need help.';
        }

        return 'This order cannot be cancelled at its current stage.';
    }
}

if (! function_exists('delivery_is_rescheduled')) {
    function delivery_is_rescheduled(?string $shipmentNotes): bool
    {
        return stripos((string) $shipmentNotes, 'RIDER_RESCHEDULED:') !== false;
    }
}

if (! function_exists('delivery_reschedule_reason_display')) {
    function delivery_reschedule_reason_display(?string $reason): ?string
    {
        $reason = trim((string) $reason);
        if ($reason === '' || strcasecmp($reason, 'No reason provided') === 0) {
            return null;
        }

        return $reason;
    }
}

if (! function_exists('delivery_reschedule_meta')) {
    /**
     * @return array{date: string, reason: string|null}|null
     */
    function delivery_reschedule_meta(?string $shipmentNotes): ?array
    {
        $notes = (string) $shipmentNotes;
        if ($notes === '' || ! delivery_is_rescheduled($notes)) {
            return null;
        }

        if (preg_match_all(
            '/RIDER_RESCHEDULED:\s*(.*?)\s*\|\s*Scheduled:\s*(\d{4}-\d{2}-\d{2})/is',
            $notes,
            $matches,
            PREG_SET_ORDER
        ) < 1) {
            return null;
        }

        $last = $matches[array_key_last($matches)];
        $date = trim((string) ($last[2] ?? ''));
        if ($date === '') {
            return null;
        }

        return [
            'date' => $date,
            'reason' => delivery_reschedule_reason_display(trim((string) ($last[1] ?? ''))),
        ];
    }
}

if (! function_exists('strip_reschedule_meta_from_notes')) {
    function strip_reschedule_meta_from_notes(?string $notes): string
    {
        $notes = trim((string) $notes);
        if ($notes === '') {
            return '';
        }

        $lines = preg_split('/\R/', $notes) ?: [];
        $kept = [];
        foreach ($lines as $line) {
            if (stripos($line, 'RIDER_RESCHEDULED:') === 0) {
                continue;
            }
            $kept[] = $line;
        }

        return trim(implode("\n", $kept));
    }
}

if (! function_exists('delivery_reschedule_scheduled_at')) {
    function delivery_reschedule_scheduled_at(?string $shipmentNotes): ?string
    {
        $meta = delivery_reschedule_meta($shipmentNotes);

        return $meta['date'] ?? null;
    }
}

if (! function_exists('delivery_reschedule_scheduled_date_label')) {
    function delivery_reschedule_scheduled_date_label(?string $shipmentNotes): ?string
    {
        $raw = delivery_reschedule_scheduled_at($shipmentNotes);
        if ($raw === null || $raw === '') {
            return null;
        }

        $dateOnly = \DateTime::createFromFormat('Y-m-d', substr($raw, 0, 10));
        if ($dateOnly instanceof \DateTime) {
            return $dateOnly->format('F j, Y');
        }

        $timestamp = strtotime($raw);
        if ($timestamp !== false) {
            return date('F j, Y', $timestamp);
        }

        return $raw;
    }
}

if (! function_exists('delivery_reschedule_reason')) {
    function delivery_reschedule_reason(?string $shipmentNotes): ?string
    {
        $meta = delivery_reschedule_meta($shipmentNotes);

        return $meta['reason'] ?? null;
    }
}

if (! function_exists('delivery_status_display_label')) {
    function delivery_status_display_label(?string $status, ?string $shipmentNotes = null, array $labels = []): string
    {
        $status = strtolower(trim((string) $status));
        if ($status === 'delivered_to_rider' && delivery_is_rescheduled($shipmentNotes)) {
            return 'Rescheduled';
        }

        if ($labels !== [] && isset($labels[$status])) {
            return (string) $labels[$status];
        }

        return ucfirst(str_replace('_', ' ', $status));
    }
}
