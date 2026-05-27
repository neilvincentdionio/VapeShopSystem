<?php

if (! function_exists('parse_rider_list_meta')) {
    /**
     * @return array<string, mixed>|null
     */
    function parse_rider_list_meta(?string $shipmentNotes): ?array
    {
        $shipmentNotes = trim((string) $shipmentNotes);
        if ($shipmentNotes === '') {
            return null;
        }

        if (preg_match('/RIDER_LIST_META:({.+})/s', $shipmentNotes, $matches) !== 1) {
            return null;
        }

        $decoded = json_decode($matches[1], true);

        return is_array($decoded) ? $decoded : null;
    }
}

if (! function_exists('strip_rider_list_meta')) {
    function strip_rider_list_meta(?string $shipmentNotes): string
    {
        $shipmentNotes = trim((string) $shipmentNotes);
        if ($shipmentNotes === '') {
            return '';
        }

        while (($pos = strpos($shipmentNotes, 'RIDER_LIST_META:')) !== false) {
            $jsonStart = $pos + strlen('RIDER_LIST_META:');
            if (! isset($shipmentNotes[$jsonStart]) || $shipmentNotes[$jsonStart] !== '{') {
                $shipmentNotes = substr($shipmentNotes, 0, $pos) . substr($shipmentNotes, $pos + strlen('RIDER_LIST_META:'));
                continue;
            }

            $depth = 0;
            $len = strlen($shipmentNotes);
            $end = null;
            for ($i = $jsonStart; $i < $len; $i++) {
                $ch = $shipmentNotes[$i];
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

            $before = rtrim(substr($shipmentNotes, 0, $pos));
            $after = ltrim(substr($shipmentNotes, $end + 1));
            $shipmentNotes = $before === '' ? $after : ($after === '' ? $before : $before . "\n" . $after);
        }

        return trim($shipmentNotes);
    }
}

if (! function_exists('merge_rider_list_meta')) {
    /**
     * @param array<string, mixed> $meta
     */
    function merge_rider_list_meta(string $shipmentNotes, array $meta): string
    {
        $line = 'RIDER_LIST_META:' . json_encode($meta, JSON_UNESCAPED_UNICODE);
        $humanNotes = strip_rider_list_meta($shipmentNotes);

        if ($humanNotes === '') {
            return $line;
        }

        return $humanNotes . "\n" . $line;
    }
}

if (! function_exists('rider_delivery_list_dismissed')) {
    /**
     * @param array<string, mixed> $meta
     */
    function rider_delivery_list_dismissed(array $meta): bool
    {
        return trim((string) ($meta['rider_list_dismissed_at'] ?? '')) !== '';
    }
}

if (! function_exists('dismiss_rider_delivery_from_list')) {
    /**
     * @param array<string, mixed> $meta
     * @return array<string, mixed>
     */
    function dismiss_rider_delivery_from_list(array $meta): array
    {
        $meta['rider_list_dismissed_at'] = date('Y-m-d H:i:s');

        return $meta;
    }
}

if (! function_exists('filter_rider_visible_deliveries')) {
    /**
     * @param array<int, array<string, mixed>> $orders
     * @return array<int, array<string, mixed>>
     */
    function filter_rider_visible_deliveries(array $orders): array
    {
        return array_values(array_filter($orders, static function (array $order): bool {
            $status = (string) ($order['delivery_status'] ?? '');
            if ($status !== 'completed') {
                return true;
            }

            $meta = parse_rider_list_meta((string) ($order['shipment_notes'] ?? '')) ?? [];

            return ! rider_delivery_list_dismissed($meta);
        }));
    }
}
