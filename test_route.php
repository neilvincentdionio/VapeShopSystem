<?php
// Quick test to check if we can access the route
require_once 'vendor/autoload.php';

// Initialize CodeIgniter
$app = new CodeIgniter\CodeIgniter();
$app->initialize();

// Test the route
echo "Testing route access...\n";
echo "Base URL: " . site_url() . "\n";
echo "Test URL: " . site_url('test-order-action/1/pay') . "\n";
echo "Original URL: " . site_url('customer/orders/1/pay') . "\n";

// Check if there are any orders
$db = \Config\Database::connect();
$result = $db->query("SELECT id, reference_number, delivery_status FROM records WHERE record_type = 'sales' LIMIT 5");
$orders = $result->getResultArray();

echo "\nExisting orders:\n";
foreach ($orders as $order) {
    echo "ID: {$order['id']}, Ref: {$order['reference_number']}, Status: {$order['delivery_status']}\n";
}

?>
