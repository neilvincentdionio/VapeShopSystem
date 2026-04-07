<?php
/**
 * Quick Test Script for Shopee-Like Delivery Process
 * Run this script to verify your setup
 */

// Simple test without full CodeIgniter bootstrap
echo "=== Shopee-Like Delivery Process Test ===\n\n";

// Test 1: Database Migration
echo "1. Testing Database Migration...\n";
try {
    // Check if database config exists
    if (file_exists('app/Config/Database.php')) {
        echo "✅ Database config: EXISTS\n";
        
        // Try to connect to database
        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $db = 'vape_shop'; // Update with your database name
        
        $conn = new mysqli($host, $user, $pass, $db);
        
        if ($conn->connect_error) {
            echo "❌ Database connection: FAILED - " . $conn->connect_error . "\n";
        } else {
            echo "✅ Database connection: PASSED\n";
            
            // Check if required columns exist
            $result = $conn->query("DESCRIBE records");
            $columns = [];
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
            
            $requiredFields = ['delivery_status', 'tracking_number', 'shipped_at', 'delivered_at', 'shipping_address', 'contact_number'];
            $missingFields = [];
            
            foreach ($requiredFields as $field) {
                if (!in_array($field, $columns)) {
                    $missingFields[] = $field;
                }
            }
            
            if (empty($missingFields)) {
                echo "✅ Database migration: PASSED - All required columns exist\n";
            } else {
                echo "❌ Database migration: FAILED - Missing fields: " . implode(', ', $missingFields) . "\n";
            }
        }
        $conn->close();
    } else {
        echo "❌ Database config: MISSING\n";
    }
} catch (Exception $e) {
    echo "❌ Database test: FAILED - " . $e->getMessage() . "\n";
}

// Test 2: Model Files
echo "\n2. Testing Model Files...\n";
$modelFile = 'app/Models/RecordModel.php';
if (file_exists($modelFile)) {
    $content = file_get_contents($modelFile);
    
    $requiredMethods = ['getOrdersByDeliveryStatus', 'updateDeliveryStatus', 'getOrderStatusCounts'];
    
    foreach ($requiredMethods as $method) {
        if (strpos($content, "function $method") !== false) {
            echo "✅ $method method: EXISTS\n";
        } else {
            echo "❌ $method method: MISSING\n";
        }
    }
    
    // Check for delivery_status in allowedFields
    if (strpos($content, "'delivery_status'") !== false) {
        echo "✅ delivery_status in allowedFields: FOUND\n";
    } else {
        echo "❌ delivery_status in allowedFields: MISSING\n";
    }
    
} else {
    echo "❌ RecordModel.php: MISSING\n";
}

// Test 3: Controller Files
echo "\n3. Testing Controller Files...\n";
$controllerFile = 'app/Controllers/Dashboard.php';
if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    
    $requiredMethods = ['customerOrders', 'customerOrderAction', 'updateDeliveryStatus', 'adminOrders'];
    
    foreach ($requiredMethods as $method) {
        if (strpos($content, "function $method") !== false) {
            echo "✅ $method method: EXISTS\n";
        } else {
            echo "❌ $method method: MISSING\n";
        }
    }
    
} else {
    echo "❌ Dashboard.php: MISSING\n";
}

// Test 4: View Files
echo "\n4. Testing View Files...\n";
$viewFiles = [
    'customer/orders.php' => 'app/Views/customer/orders.php',
    'admin/orders/index.php' => 'app/Views/admin/orders/index.php'
];

foreach ($viewFiles as $name => $path) {
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Check for helper function
        if (strpos($content, 'getDeliveryStatusLabel') !== false) {
            echo "✅ $name view: EXISTS and has helper function\n";
        } else {
            echo "⚠️  $name view: EXISTS but missing helper function\n";
        }
        
        // Check for Shopee-like elements
        if (strpos($content, 'shopee-tabs') !== false) {
            echo "✅ $name view: Has Shopee-like tabs\n";
        }
        
    } else {
        echo "❌ $name view: MISSING\n";
    }
}

// Test 5: Routes
echo "\n5. Testing Routes...\n";
$routesFile = 'app/Config/Routes.php';
if (file_exists($routesFile)) {
    $content = file_get_contents($routesFile);
    
    $requiredRoutes = [
        '/customer/orders' => 'customer order page',
        '/customer/orders/(:num)/(:alpha)' => 'customer order actions',
        '/orders' => 'admin orders page',
        '/orders/update-delivery-status' => 'admin delivery status update'
    ];
    
    foreach ($requiredRoutes as $route => $description) {
        if (strpos($content, $route) !== false) {
            echo "✅ Route $route: CONFIGURED\n";
        } else {
            echo "❌ Route $route: MISSING\n";
        }
    }
    
} else {
    echo "❌ Routes.php: MISSING\n";
}

// Test 6: Migration File
echo "\n6. Testing Migration File...\n";
$migrationFile = 'app/Database/Migrations/2026-03-24-120000_AddDeliveryTrackingToRecords.php';
if (file_exists($migrationFile)) {
    echo "✅ Migration file: EXISTS\n";
    
    $content = file_get_contents($migrationFile);
    if (strpos($content, 'delivery_status') !== false) {
        echo "✅ Migration has delivery_status field\n";
    }
    if (strpos($content, 'tracking_number') !== false) {
        echo "✅ Migration has tracking_number field\n";
    }
} else {
    echo "❌ Migration file: MISSING\n";
}

echo "\n=== Test Complete ===\n";
echo "\nNext Steps:\n";
echo "1. Fix any FAILED items above\n";
echo "2. Ensure database migration has been run: php spark migrate\n";
echo "3. Create test accounts (admin & customer)\n";
echo "4. Create test products\n";
echo "5. Run through the testing scenarios in TESTING_GUIDE.md\n";
echo "6. Test the complete order flow from creation to completion\n";

?>
