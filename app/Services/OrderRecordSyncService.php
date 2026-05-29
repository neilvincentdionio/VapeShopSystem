<?php

namespace App\Services;

use App\Models\OrderModel;
use App\Models\RecordModel;

/**
 * Syncs orders (sales + damaged inventory lines) into the records module.
 */
class OrderRecordSyncService
{
    protected OrderModel $orderModel;
    protected RecordModel $recordModel;

    public function __construct(?OrderModel $orderModel = null, ?RecordModel $recordModel = null)
    {
        $this->orderModel = $orderModel ?? new OrderModel();
        $this->recordModel = $recordModel ?? new RecordModel();
    }

    /**
     * @return array{sales:int, damaged:int, skipped:int}
     */
    public function syncAllOrders(?int $actorId = null): array
    {
        $stats = ['sales' => 0, 'damaged' => 0, 'skipped' => 0];

        if (! \Config\Database::connect()->tableExists('records')) {
            return $stats;
        }

        $orderIds = \Config\Database::connect()->table('orders')->select('id')->get()->getResultArray();
        foreach ($orderIds as $row) {
            $orderId = (int) ($row['id'] ?? 0);
            if ($orderId <= 0) {
                $stats['skipped']++;
                continue;
            }

            if ($this->syncOrder($orderId, $actorId)) {
                $stats['sales']++;
            } else {
                $stats['skipped']++;
            }

            $order = $this->orderModel->getOrder($orderId);
            if (! $order) {
                continue;
            }

            $returnMeta = parse_return_meta(
                (string) ($order['shipment_notes'] ?? ''),
                (string) ($order['delivery_notes'] ?? '')
            );
            if (! is_array($returnMeta)) {
                continue;
            }

            $before = $this->recordModel
                ->where('record_type', 'inventory')
                ->like('reference_number', trim((string) ($order['reference_number'] ?? '')) . '-DMG-', 'after')
                ->countAllResults();
            $this->syncDamagedItems($orderId, $order, $returnMeta, $actorId);
            $after = $this->recordModel
                ->where('record_type', 'inventory')
                ->like('reference_number', trim((string) ($order['reference_number'] ?? '')) . '-DMG-', 'after')
                ->countAllResults();
            if ($after > $before) {
                $stats['damaged'] += ($after - $before);
            }
        }

        return $stats;
    }

    public function syncOrder(int $orderId, ?int $actorId = null): bool
    {
        if ($orderId <= 0 || ! \Config\Database::connect()->tableExists('records')) {
            return false;
        }

        try {
            $order = $this->orderModel->getOrder($orderId);
            if (! $order) {
                return false;
            }

            $referenceNumber = trim((string) ($order['reference_number'] ?? ''));
            if ($referenceNumber === '') {
                return false;
            }

            helper(['return_refund', 'record', 'input_validation']);

            $placedAt = (string) ($order['created_at'] ?? $order['date'] ?? '');
            $recordDateTs = strtotime($placedAt !== '' ? $placedAt : date('Y-m-d H:i:s'));
            $normalizedDate = $recordDateTs !== false ? date('Y-m-d', $recordDateTs) : date('Y-m-d');

            $deliveryStatus = strtolower((string) ($order['delivery_status'] ?? 'to_pay'));
            $recordStatus = 'pending';
            if ($deliveryStatus === 'return_refund' || is_return_refund_status($deliveryStatus)) {
                $recordStatus = 'return_refund';
            } elseif (in_array($deliveryStatus, ['cancelled', 'failed_delivery'], true)) {
                $recordStatus = 'cancelled';
            } elseif ($deliveryStatus === 'completed') {
                $recordStatus = 'completed';
            }

            $paymentMethod = strtolower((string) ($order['payment_method'] ?? 'cash'));
            if (! in_array($paymentMethod, ['cash', 'card', 'gcash', 'bank_transfer'], true)) {
                $paymentMethod = 'cash';
            }

            $paymentStatus = strtolower((string) ($order['payment_status'] ?? 'unpaid'));
            if (! in_array($paymentStatus, ['paid', 'partial', 'unpaid'], true)) {
                $paymentStatus = 'unpaid';
            }
            if ($recordStatus === 'completed') {
                $paymentStatus = 'paid';
            } elseif ($recordStatus === 'return_refund') {
                $paymentStatus = 'unpaid';
            }

            $notes = trim((string) ($order['notes'] ?? ''));
            if (is_return_refund_status($deliveryStatus)) {
                $returnMeta = parse_return_meta(
                    (string) ($order['shipment_notes'] ?? ''),
                    (string) ($order['delivery_notes'] ?? '')
                );
                $returnLine = 'Return/Refund: ' . return_refund_status_label($deliveryStatus);
                if (is_array($returnMeta) && ! empty($returnMeta['type'])) {
                    $returnLine .= ' (' . return_refund_type_label((string) $returnMeta['type']) . ')';
                }
                if (is_array($returnMeta) && is_array($returnMeta['damaged_items'] ?? null) && $returnMeta['damaged_items'] !== []) {
                    $returnLine .= ' · ' . count($returnMeta['damaged_items']) . ' damaged item line(s) in inventory records';
                }
                if ($notes === '' || stripos($notes, 'Return/Refund:') === false) {
                    $notes = $notes !== '' ? ($notes . "\n" . $returnLine) : $returnLine;
                }
            }

            $createdBy = $actorId ?? (int) ($order['created_by'] ?? 0);
            $createdBy = $createdBy > 0 ? $createdBy : null;

            $payload = [
                'record_type' => 'sales',
                'record_date' => $normalizedDate,
                'reference_number' => sanitize_safe_text($referenceNumber, 'reference'),
                'title' => sanitize_safe_text(trim((string) ($order['title'] ?? 'Sales Order')), 'text'),
                'description' => sanitize_safe_text(
                    trim((string) ($order['description'] ?? 'Auto-synced from Orders module')),
                    'description'
                ),
                'quantity' => max(0, (int) ($order['quantity'] ?? 0)),
                'unit_price' => max(0, (float) ($order['unit_price'] ?? 0)),
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'status' => $recordStatus,
                'notes' => sanitize_safe_text($notes, 'description'),
                'created_by' => $createdBy,
            ];

            $existing = $this->recordModel
                ->where('record_type', 'sales')
                ->where('reference_number', $referenceNumber)
                ->first();

            $this->recordModel->skipValidation(true);
            try {
                if ($existing && isset($existing['id'])) {
                    return (bool) $this->recordModel->update((int) $existing['id'], $payload);
                }

                return (bool) $this->recordModel->insert($payload);
            } finally {
                $this->recordModel->skipValidation(false);
            }
        } catch (\Throwable $e) {
            log_message('error', 'OrderRecordSyncService::syncOrder failed for {id}: {message}', [
                'id' => $orderId,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @param array<string, mixed> $order
     * @param array<string, mixed> $returnMeta
     */
    public function syncDamagedItems(int $orderId, array $order, array $returnMeta, ?int $actorId = null): void
    {
        if ($orderId <= 0 || ! \Config\Database::connect()->tableExists('records')) {
            return;
        }

        try {
            helper(['return_refund', 'record', 'input_validation']);

            $damagedKeys = parse_return_damaged_item_keys($returnMeta['damaged_items'] ?? []);
            if ($damagedKeys === []) {
                return;
            }

            $orderReference = trim((string) ($order['reference_number'] ?? ''));
            if ($orderReference === '') {
                return;
            }

            $damagedLookup = array_fill_keys($damagedKeys, true);
            $recordDate = date('Y-m-d');
            $createdBy = $actorId ?? (int) ($order['created_by'] ?? 0);
            $createdBy = $createdBy > 0 ? $createdBy : null;
            $requestTypeLabel = return_refund_type_label((string) ($returnMeta['type'] ?? 'return_and_refund'));

            foreach ((array) ($order['items'] ?? []) as $line) {
                if (! is_array($line)) {
                    continue;
                }

                $productId = (int) ($line['id'] ?? $line['product_id'] ?? 0);
                if ($productId <= 0) {
                    continue;
                }

                $variantId = isset($line['variant_id']) && (int) $line['variant_id'] > 0
                    ? (int) $line['variant_id']
                    : 0;
                $stockKey = return_item_stock_key($productId, $variantId > 0 ? $variantId : null);
                if (! isset($damagedLookup[$stockKey])) {
                    continue;
                }

                $quantity = max(1, (int) ($line['qty'] ?? $line['quantity'] ?? 1));
                $unitPrice = round((float) ($line['unit_price'] ?? $line['selling_price'] ?? $line['price'] ?? 0), 2);
                $productName = trim((string) ($line['name'] ?? $line['product_name'] ?? 'Product'));
                $recordReference = record_damaged_inventory_reference($orderReference, $productId, $variantId);

                $payload = [
                    'record_type' => 'inventory',
                    'record_date' => $recordDate,
                    'reference_number' => sanitize_safe_text($recordReference, 'reference'),
                    'title' => sanitize_safe_text($productName . ' - Damaged Item', 'description'),
                    'description' => sanitize_safe_text(
                        'Damaged item from ' . $requestTypeLabel
                        . ' (order ' . $orderReference . '). Not returned to sellable stock.',
                        'description'
                    ),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'payment_method' => null,
                    'payment_status' => 'unpaid',
                    'status' => 'return_refund',
                    'notes' => sanitize_safe_text(
                        'Damaged Item - Return/Refund order ' . $orderReference . ' - qty ' . $quantity,
                        'description'
                    ),
                    'created_by' => $createdBy,
                ];

                $existing = $this->recordModel
                    ->where('record_type', 'inventory')
                    ->where('reference_number', $recordReference)
                    ->first();

                $this->recordModel->skipValidation(true);
                try {
                    if ($existing && isset($existing['id'])) {
                        $this->recordModel->update((int) $existing['id'], $payload);
                    } else {
                        $this->recordModel->insert($payload);
                    }
                } finally {
                    $this->recordModel->skipValidation(false);
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'OrderRecordSyncService::syncDamagedItems failed for {id}: {message}', [
                'id' => $orderId,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function syncOrderWithDamaged(int $orderId, ?int $actorId = null): void
    {
        $this->syncOrder($orderId, $actorId);
        $order = $this->orderModel->getOrder($orderId);
        if (! $order) {
            return;
        }

        $returnMeta = parse_return_meta(
            (string) ($order['shipment_notes'] ?? ''),
            (string) ($order['delivery_notes'] ?? '')
        );
        if (is_array($returnMeta)) {
            $this->syncDamagedItems($orderId, $order, $returnMeta, $actorId);
        }
    }
}
