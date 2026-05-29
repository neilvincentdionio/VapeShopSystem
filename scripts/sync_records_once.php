<?php

/**
 * One-off CLI: sync all orders into records (sales + damaged lines).
 * Usage: php scripts/sync_records_once.php
 */

define('FCPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

require dirname(__DIR__) . '/vendor/autoload.php';

$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';

CodeIgniter\Boot::bootSpark($paths);

$orderModel = new \App\Models\OrderModel();
$recordModel = new \App\Models\RecordModel();
$service = new \App\Services\OrderRecordSyncService($orderModel, $recordModel);

$stats = $service->syncAllOrders(1);

$order = $orderModel->getOrder(1);
$meta = parse_return_meta((string) ($order['delivery_notes'] ?? ''), '');
$before = $recordModel->where('record_type', 'inventory')->countAllResults();
if ($order && is_array($meta)) {
    echo 'Damaged keys: ' . implode(', ', parse_return_damaged_item_keys($meta['damaged_items'] ?? [])) . PHP_EOL;
    $service->syncDamagedItems(1, $order, $meta, 1);
    if ($recordModel->errors()) {
        echo 'Damaged insert errors:' . PHP_EOL;
        print_r($recordModel->errors());
    }
}
$after = $recordModel->where('record_type', 'inventory')->countAllResults();

echo 'Sales synced: ' . $stats['sales'] . PHP_EOL;
echo 'Damaged lines synced (reported): ' . $stats['damaged'] . PHP_EOL;
echo 'Damaged inventory rows now: ' . $after . ' (added ' . ($after - $before) . ')' . PHP_EOL;
echo 'Skipped: ' . $stats['skipped'] . PHP_EOL;
echo 'Total records: ' . $recordModel->countAllResults() . PHP_EOL;
