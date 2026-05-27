<?php
declare(strict_types=1);

function mobile_parse_rider_list_meta(?string $shipmentNotes): ?array
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

function mobile_strip_rider_list_meta(?string $shipmentNotes): string
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

function mobile_merge_rider_list_meta(string $shipmentNotes, array $meta): string
{
    $line = 'RIDER_LIST_META:' . json_encode($meta, JSON_UNESCAPED_UNICODE);
    $humanNotes = mobile_strip_rider_list_meta($shipmentNotes);

    if ($humanNotes === '') {
        return $line;
    }

    return $humanNotes . "\n" . $line;
}

function mobile_rider_delivery_list_dismissed(array $meta): bool
{
    return trim((string) ($meta['rider_list_dismissed_at'] ?? '')) !== '';
}

function mobile_filter_rider_visible_deliveries(array $orders): array
{
    return array_values(array_filter($orders, static function (array $order): bool {
        $status = (string) ($order['delivery_status'] ?? '');
        if ($status !== 'completed') {
            return true;
        }

        $meta = mobile_parse_rider_list_meta((string) ($order['shipment_notes'] ?? '')) ?? [];

        return ! mobile_rider_delivery_list_dismissed($meta);
    }));
}

function mobile_dismiss_rider_completed_deliveries(PDO $db, int $riderId): int
{
    $stmt = $db->prepare(
        'SELECT o.id, s.notes AS shipment_notes
         FROM orders o
         INNER JOIN order_shipments s ON s.order_id = o.id
         WHERE s.assigned_rider_id = :rider_id
           AND s.status = \'completed\''
    );
    $stmt->execute([':rider_id' => $riderId]);
    $dismissed = 0;

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (! is_array($row)) {
            continue;
        }
        $orderId = (int) ($row['id'] ?? 0);
        if ($orderId <= 0) {
            continue;
        }

        $shipmentNotes = (string) ($row['shipment_notes'] ?? '');
        $meta = mobile_parse_rider_list_meta($shipmentNotes) ?? [];
        if (mobile_rider_delivery_list_dismissed($meta)) {
            continue;
        }

        $meta['rider_list_dismissed_at'] = date('Y-m-d H:i:s');
        $updatedNotes = mobile_merge_rider_list_meta($shipmentNotes, $meta);
        $update = $db->prepare('UPDATE order_shipments SET notes = :notes WHERE order_id = :order_id');
        if ($update->execute([':notes' => $updatedNotes, ':order_id' => $orderId])) {
            $dismissed++;
        }
    }

    return $dismissed;
}
