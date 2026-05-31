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

if (! function_exists('is_system_shipment_meta_line')) {
    /**
     * Rider/customer system lines appended to shipment notes (not customer delivery instructions).
     */
    function is_system_shipment_meta_line(string $line): bool
    {
        $line = trim($line);
        if ($line === '') {
            return false;
        }

        $prefixes = [
            'RIDER_RESCHEDULED:',
            'RIDER_CANCELLED:',
            'RIDER_DECLINED:',
            'CUSTOMER_CANCELLED_AT_DOOR:',
        ];

        foreach ($prefixes as $prefix) {
            if (stripos($line, $prefix) === 0) {
                return true;
            }
        }

        return false;
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
            if (is_system_shipment_meta_line($line)) {
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

if (! function_exists('delivery_is_terminal_status')) {
    function delivery_is_terminal_status(?string $deliveryStatus): bool
    {
        $status = strtolower(trim((string) $deliveryStatus));

        return in_array($status, ['cancelled', 'completed', 'delivered'], true);
    }
}

if (! function_exists('delivery_show_reschedule_notice')) {
    function delivery_show_reschedule_notice(?string $deliveryStatus, ?string $shipmentNotes): bool
    {
        if (delivery_is_terminal_status($deliveryStatus)) {
            return false;
        }

        return delivery_is_rescheduled($shipmentNotes);
    }
}

if (! function_exists('delivery_completion_max_distance_meters')) {
    /**
     * Max distance (meters) between rider GPS and customer delivery pin to allow proof submission.
     * Customer map pins are often imprecise, so default is relaxed.
     */
    function delivery_completion_max_distance_meters(): float
    {
        $raw = getenv('DELIVERY_COMPLETION_MAX_DISTANCE_METERS');
        if ($raw !== false && $raw !== '' && is_numeric($raw)) {
            return max(100.0, (float) $raw);
        }

        return 2000.0;
    }
}

if (! function_exists('resolve_rider_proof_coordinates')) {
    /**
     * @return array{lat: float|null, lng: float|null}
     */
    function resolve_rider_proof_coordinates(array $shipment, ?float $submittedLat, ?float $submittedLng): array
    {
        $deliveryLat = isset($shipment['delivery_latitude']) ? (float) $shipment['delivery_latitude'] : null;
        $deliveryLng = isset($shipment['delivery_longitude']) ? (float) $shipment['delivery_longitude'] : null;
        $maxMeters = delivery_completion_max_distance_meters();

        $candidates = [];
        if ($submittedLat !== null && $submittedLng !== null) {
            $candidates[] = [$submittedLat, $submittedLng];
        }

        $trackedLat = isset($shipment['rider_latitude']) ? (float) $shipment['rider_latitude'] : null;
        $trackedLng = isset($shipment['rider_longitude']) ? (float) $shipment['rider_longitude'] : null;
        if ($trackedLat !== null && $trackedLng !== null && ! ($trackedLat == 0.0 && $trackedLng == 0.0)) {
            $candidates[] = [$trackedLat, $trackedLng];
        }

        if ($deliveryLat !== null && $deliveryLng !== null && $deliveryLat != 0.0 && $deliveryLng != 0.0) {
            foreach ($candidates as [$lat, $lng]) {
                if (delivery_distance_meters($lat, $lng, $deliveryLat, $deliveryLng) <= $maxMeters) {
                    return ['lat' => $lat, 'lng' => $lng];
                }
            }
        }

        if ($submittedLat !== null && $submittedLng !== null) {
            return ['lat' => $submittedLat, 'lng' => $submittedLng];
        }

        if ($trackedLat !== null && $trackedLng !== null) {
            return ['lat' => $trackedLat, 'lng' => $trackedLng];
        }

        return ['lat' => null, 'lng' => null];
    }
}

if (! function_exists('delivery_distance_meters')) {
    function delivery_distance_meters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}

if (! function_exists('delivery_is_rider_declined')) {
    function delivery_is_rider_declined(?string $shipmentNotes): bool
    {
        return stripos((string) $shipmentNotes, 'RIDER_DECLINED:') !== false;
    }
}

if (! function_exists('delivery_rider_decline_meta')) {
    /**
     * @return array{reason: string|null, declined_at: string|null}|null
     */
    function delivery_rider_decline_meta(?string $shipmentNotes): ?array
    {
        $notes = (string) $shipmentNotes;
        if ($notes === '' || ! delivery_is_rider_declined($notes)) {
            return null;
        }

        if (preg_match_all(
            '/RIDER_DECLINED:\s*(.*?)\s*\((\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\)/is',
            $notes,
            $matches,
            PREG_SET_ORDER
        ) < 1) {
            return ['reason' => null, 'declined_at' => null];
        }

        $last = $matches[array_key_last($matches)];
        $reason = trim((string) ($last[1] ?? ''));
        if ($reason === '' || strcasecmp($reason, 'No reason provided') === 0) {
            $reason = null;
        }

        return [
            'reason' => $reason,
            'declined_at' => trim((string) ($last[2] ?? '')) ?: null,
        ];
    }
}

if (! function_exists('delivery_show_rider_declined_notice')) {
    function delivery_show_rider_declined_notice(?string $deliveryStatus, ?string $shipmentNotes): bool
    {
        if (delivery_is_terminal_status($deliveryStatus)) {
            return false;
        }

        $status = strtolower(trim((string) $deliveryStatus));

        return $status === 'to_ship' && delivery_is_rider_declined($shipmentNotes);
    }
}

if (! function_exists('delivery_status_display_label')) {
    function delivery_status_display_label(?string $status, ?string $shipmentNotes = null, array $labels = []): string
    {
        $status = strtolower(trim((string) $status));
        if ($status === 'delivered_to_rider' && delivery_show_reschedule_notice($status, $shipmentNotes)) {
            return 'Rescheduled';
        }

        if ($status === 'to_ship' && delivery_show_rider_declined_notice($status, $shipmentNotes)) {
            return 'Rider Declined';
        }

        if ($labels !== [] && isset($labels[$status])) {
            return (string) $labels[$status];
        }

        return ucfirst(str_replace('_', ' ', $status));
    }
}
