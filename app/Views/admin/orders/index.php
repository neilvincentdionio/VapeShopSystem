<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Quick Puff Vape Shop System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
<?php
// Helper function to get delivery status labels
if (!function_exists('getDeliveryStatusLabel')) {
    function getDeliveryStatusLabel($status) {
        $labels = [
            'to_pay' => 'To Pay',
            'to_ship' => 'Order Placed',
            'to_receive' => 'Out for Delivery',
            'delivered' => 'Delivered (Awaiting Confirm)',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'return_refund' => 'Return/Refund',
            'failed_delivery' => 'Failed Delivery',
            'ready_for_pickup' => 'Rider Assigned',
            'accepted_by_rider' => 'Accepted by Rider',
            'delivered_to_rider' => 'Picked Up'
        ];
        
        return $labels[$status] ?? ucfirst($status);
    }
}

if (!function_exists('extractGcashReference')) {
    function extractGcashReference($notes) {
        $notes = (string) $notes;
        if ($notes === '') {
            return null;
        }

        if (preg_match('/GCASH_REF:([^;\\s]+)/', $notes, $matches)) {
            return $matches[1];
        }

        return null;
    }
}

$ordersList = is_array($orders ?? null) ? $orders : [];
$hasOrders = ! empty($ordersList);
$totalOrders = count($ordersList);
$totalRevenue = array_sum(array_map(
    static fn ($order) => (($order['payment_status'] ?? 'unpaid') === 'paid') ? (float) ($order['total_amount'] ?? 0) : 0.0,
    $ordersList
));
$completedOrders = count(array_filter(
    $ordersList,
    static fn ($order) => ($order['delivery_status'] ?? 'to_pay') === 'completed'
));
$activeDeliveries = count(array_filter(
    $ordersList,
    static fn ($order) => in_array(($order['delivery_status'] ?? 'to_pay'), ['to_ship', 'to_receive'], true)
));
?>

<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --main-font: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        body {
            font-family: var(--main-font);
            background: #ffffff;
            min-height: 100vh;
            position: relative;
            color: #333333;
        }

        .navbar {
            background: #ffffff;
            border-bottom: 1px solid #e0e0e0;
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .navbar-brand {
            font-size: 1.25rem;
            font-weight: 700;
            color: #333333;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .navbar-brand:hover {
            color: #00bcd4;
        }

        .navbar-center {
            flex: 1;
            display: flex;
            justify-content: center;
        }

        .navbar-menu {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: #666666;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            color: #00bcd4;
            background: rgba(0, 188, 212, 0.1);
        }

        .nav-link.active {
            color: #00bcd4;
            background: rgba(0, 188, 212, 0.1);
            font-weight: 600;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00bcd4, #0097a7);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
        }

        .user-name {
            font-weight: 600;
            color: #333333;
            text-decoration: none;
        }

        .user-name:hover {
            color: #00bcd4;
        }

        .badge {
            background: #f0f0f0;
            color: #666666;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .content {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .orders-container {
            width: 100%;
        }

        .page-header {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        .page-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .page-title h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #333333;
            margin-bottom: 0.5rem;
        }

        .page-title p {
            color: #666666;
            font-size: 1rem;
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: rgba(39, 197, 111, 0.1);
            color: #27c56f;
            flex: 0 0 auto;
        }

        .stat-icon.success {
            background: rgba(39, 197, 111, 0.1);
            color: #27c56f;
        }

        .stat-icon.warning {
            background: rgba(255, 152, 0, 0.1);
            color: #ff9800;
        }

        .stat-icon.info {
            background: rgba(0, 188, 212, 0.1);
            color: #00bcd4;
        }

        .stat-content h3 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333333;
            margin-bottom: 0.25rem;
        }

        .stat-content p {
            color: #666666;
            font-size: 0.9rem;
        }

        .data-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .data-card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .data-card-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333333;
        }

        .search-sort-container {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            min-width: 200px;
        }

        .search-box input {
            width: 100%;
            padding: 0.4rem 0.8rem 0.4rem 2.2rem;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.85rem;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .search-box input:focus {
            border-color: #00bcd4;
            box-shadow: 0 0 0 2px rgba(0, 188, 212, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 0.7rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 0.8rem;
        }

        .sort-select {
            padding: 0.4rem 0.8rem;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.85rem;
            background: white;
            cursor: pointer;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .sort-select:focus {
            border-color: #00bcd4;
            box-shadow: 0 0 0 2px rgba(0, 188, 212, 0.1);
        }

        .card-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #e0e0e0;
            color: #666666;
        }

        .btn-outline:hover {
            background: #f8f9fa;
            border-color: #27c56f;
            color: #27c56f;
            transform: translateY(-1px);
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1400px;
        }

        .data-table th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #333333;
            border-bottom: 1px solid #e0e0e0;
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;
        }

        .data-table td:last-child {
            vertical-align: middle;
            width: 210px;
        }

        .data-table tr:hover {
            background: #f8f9fa;
        }

        .order-link {
            color: inherit;
            text-decoration: none;
        }

        .order-link:hover {
            text-decoration: underline;
        }

        .meta-text,
        .muted-text,
        .detail-text,
        .shipping-text {
            color: #666666;
            font-size: 0.85rem;
        }

        .shipping-text {
            max-width: 150px;
            display: inline-block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 0.5rem;
            min-width: 170px;
        }
        
        .order-id {
            font-weight: 700;
            color: #00bcd4;
        }
        
        .customer-info {
            font-size: 0.9rem;
        }
        
        .customer-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }
        
        .customer-email {
            color: #666;
        }
        
        .order-items-preview {
            max-width: 300px;
        }
        
        .item-preview {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.25rem;
        }
        
        .more-items {
            color: #00bcd4;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .order-total {
            font-weight: 700;
            color: #333;
            font-size: 1.1rem;
        }
        
        .order-status {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-to_pay {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-to_ship {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-to_receive {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-completed {
            background: #e8f5e8;
            color: #2e7d2e;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-return_refund {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .status-failed_delivery {
            background: #f8d7da;
            color: #721c24;
        }
        
        .payment-method {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.35rem;
        }

        .payment-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .payment-status.paid {
            background: #e8f5e8;
            color: #2e7d2e;
        }

        .payment-status.unpaid,
        .payment-status.pending,
        .payment-status.partial {
            background: #fff3cd;
            color: #856404;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 1rem;
            color: #ccc;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .btn-checkout {
            background: #27c56f;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }
        
        .btn-checkout:hover {
            background: #219653;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(39, 197, 111, 0.3);
        }
        
        .btn-transit {
            background: #2196f3;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }
        
        .btn-transit:hover {
            background: #1976d2;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(33, 150, 243, 0.3);
        }
        
        .btn-delivered {
            background: #4caf50;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }
        
        .btn-delivered:hover {
            background: #388e3c;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
        }
        
        .btn-delivery {
            background: #ff9800;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-delivery:hover {
            background: #f57c00;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(255, 152, 0, 0.3);
        }
        
        .btn-failed {
            background: #f44336;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }
        
        .btn-failed:hover {
            background: #d32f2f;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(244, 67, 54, 0.3);
        }
        
        .btn-view-proof {
            background: #2196f3;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-view-proof:hover {
            background: #1976d2;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(33, 150, 243, 0.3);
        }

        .action-btn {
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            font-family: var(--main-font);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            width: 100%;
            min-width: 0;
        }

        .action-btn + .action-btn {
            margin-top: .35rem;
        }

        .action-meta {
            margin-top: 0;
        }

        .action-cell {
            display: flex;
            flex-direction: column;
            gap: .35rem;
            align-items: stretch;
        }

        .action-cell .status-badge,
        .action-cell .action-btn,
        .action-cell .action-meta {
            width: 100%;
        }

        .btn-checkout,
        .btn-transit,
        .btn-delivered,
        .btn-delivery,
        .btn-failed,
        .btn-view-proof {
            width: 100%;
            justify-content: center;
            margin-bottom: 0;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-dialog {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }
        
        .modal-content {
            background-color: white;
            margin: auto;
            padding: 2rem;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #333;
        }
        
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            background: none;
            border: none;
        }
        
        .close:hover {
            color: #000;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }
        
        .btn-primary {
            background: #00bcd4;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .page-header-content,
            .data-card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .dashboard-stats {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                min-width: 150px;
            }
            
            .data-table {
                font-size: 0.72rem;
                min-width: 1200px;
            }
            
            .data-table th,
            .data-table td {
                padding: 0.65rem;
            }
            
            .order-items-preview {
                max-width: 100px;
            }
        }
        
        @media (max-width: 1024px) {
            .data-table {
                font-size: 0.78rem;
                min-width: 1280px;
            }
        }
</style>
<?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
    <?= $this->include('admin/partials/sidebar') ?>

    <div class="container">
        <div class="orders-container">
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title">
                        <h1>Orders Management</h1>
                        <p>Manage customer orders, payments, and delivery progress.</p>
                    </div>
                </div>
            </div>
    
    <?php if ($hasOrders): ?>
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $totalOrders ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-content">
                    <h3>&#8369;<?= number_format((float) $totalRevenue, 2) ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $completedOrders ?></h3>
                    <p>Completed Orders</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $activeDeliveries ?></h3>
                    <p>Active Deliveries</p>
                </div>
            </div>
        </div>
        
        <div class="data-card">
            <div class="data-card-header">
                <h3>Orders List</h3>
                <div class="card-actions">
                    <div class="search-sort-container">
                        <div class="search-box">
                            <input type="text" 
                                   id="orderSearch" 
                                   placeholder="Search orders..." 
                                   onkeyup="filterOrders()">
                            <i class="fas fa-search"></i>
                        </div>
                        <select id="sortOptions" onchange="sortOrders()" class="sort-select">
                            <option value="default">Sort by</option>
                            <option value="date-desc">Newest First</option>
                            <option value="date-asc">Oldest First</option>
                            <option value="customer-asc">Customer (A-Z)</option>
                            <option value="customer-desc">Customer (Z-A)</option>
                            <option value="total-desc">Total (High to Low)</option>
                            <option value="total-asc">Total (Low to High)</option>
                            <option value="status-asc">Status (A-Z)</option>
                            <option value="status-desc">Status (Z-A)</option>
                            <option value="filter-cancelled">CANCELLED Orders</option>
                            <option value="filter-pending">PENDING Orders</option>
                            <option value="filter-completed">COMPLETED Orders</option>
                        </select>
                        <button class="btn btn-sm btn-outline" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Delivery Status</th>
                        <th>Tracking Number</th>
                        <th>Shipping Address</th>
                        <th>Address Description</th>
                        <th>Contact Number</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ordersList as $order): ?>
                        <tr>
                            <td>
                                <div class="order-id">
                                    <a href="<?= site_url('admin/order-details/' . $order['id']) ?>" class="order-link">
                                        <?= esc($order['reference_number']) ?>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <?= date('M j, Y', strtotime($order['date'])) ?>
                                <br>
                                <small class="meta-text"><?= date('g:i A', strtotime($order['date'])) ?></small>
                            </td>
                            <td>
                                <?php if ($order['customer']): ?>
                                    <div class="customer-info">
                                        <div class="customer-name"><?= esc($order['customer']['name']) ?></div>
                                        <div class="customer-email"><?= esc($order['customer']['email']) ?></div>
                                    </div>
                                <?php else: ?>
                                    <span class="muted-text">Guest</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="order-items-preview">
                                    <?php if (!empty($order['items'])): ?>
                                        <?php $itemCount = count($order['items']); ?>
                                        <?php for ($i = 0; $i < min(2, $itemCount); $i++): ?>
                                            <div class="item-preview">
                                                <?= esc($order['items'][$i]['name']) ?> &times; <?= (int) $order['items'][$i]['qty'] ?>
                                            </div>
                                        <?php endfor; ?>
                                        <?php if ($itemCount > 2): ?>
                                            <div class="more-items">+<?= $itemCount - 2 ?> more items</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="muted-text">No items</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="order-total">&#8369;<?= number_format((float) $order['total_amount'], 2) ?></div>
                            </td>
                            <td>
                                <div class="payment-method">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <?= esc(ucfirst($order['payment_method'])) ?>
                                </div>
                                <span class="payment-status <?= esc(strtolower((string) ($order['payment_status'] ?? 'unpaid'))) ?>">
                                    <?= esc(ucfirst((string) ($order['payment_status'] ?? 'unpaid'))) ?>
                                </span>
                                <?php $gcashRef = (($order['payment_method'] ?? '') === 'gcash') ? extractGcashReference($order['notes'] ?? '') : null; ?>
                                <?php if ($gcashRef): ?>
                                    <div class="detail-text" style="margin-top:.35rem;">
                                        Ref: <strong><?= esc($gcashRef) ?></strong>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="order-status status-<?= esc($order['status']) ?>">
                                    <?= esc(ucfirst($order['status'])) ?>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $deliveryStatus = $order['delivery_status'] ?? 'to_pay';
                                // Debug: Uncomment next line to see actual status
                                // echo "<small style='color:red;'>DEBUG: " . esc($deliveryStatus) . "</small><br>";
                                ?>
                                <div class="order-status status-<?= esc($deliveryStatus) ?>">
                                    <?= getDeliveryStatusLabel($deliveryStatus) ?>
                                </div>
                            </td>
                            <td>
                                <span class="detail-text">
                                    <?= esc($order['tracking_number'] ?: 'No tracking') ?>
                                </span>
                            </td>
                            <td>
                                <span class="shipping-text" title="<?= esc($order['shipping_address']) ?>">
                                    <?= esc($order['shipping_address'] ?: 'Not provided') ?>
                                </span>
                            </td>
                            <td>
                                <span class="shipping-text" title="<?= esc($order['shipment_notes'] ?? '') ?>">
                                    <?= esc(!empty($order['shipment_notes']) ? $order['shipment_notes'] : 'No description') ?>
                                </span>
                            </td>
                            <td>
                                <span class="detail-text">
                                    <?= esc($order['contact_number'] ?: 'Not provided') ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-cell">
                                <?php 
                                $deliveryStatus = $order['delivery_status'] ?? 'to_pay';
                                // Debug: Show actual status (remove this line after fixing)
                                // echo "<small style='color:red;'>DEBUG: " . esc($deliveryStatus) . "</small><br>";
                                ?>
                                <?php if (!in_array($deliveryStatus, ['completed', 'delivered'], true)): ?>
                                    <button type="button" class="action-btn btn-view-proof" onclick="openOrderDetailsModal(<?= (int) $order['id'] ?>)">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                <?php endif; ?>
                                <?php if ($deliveryStatus === 'to_ship' || $deliveryStatus === 'ready_for_pickup' || $deliveryStatus === 'accepted_by_rider'): ?>
                                    <button class="action-btn btn-checkout" onclick="assignRider(<?= (int) $order['id'] ?>, <?= (int) ($order['assigned_rider_id'] ?? 0) ?>)">
                                        <i class="fas fa-motorcycle"></i> <?= ($deliveryStatus === 'to_ship') ? 'Assign Rider' : 'Reassign Rider' ?>
                                    </button>
                                    <?php if (!empty($order['assigned_rider_name'])): ?>
                                        <div class="muted action-meta">Assigned: <?= esc($order['assigned_rider_name']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($deliveryStatus === 'accepted_by_rider'): ?>
                                        <button class="action-btn btn-delivered" onclick="updateDeliveryStatus(<?= (int) $order['id'] ?>, 'delivered_to_rider')">
                                            <i class="fas fa-box"></i> Mark as Picked Up
                                        </button>
                                    <?php endif; ?>
                                <?php elseif (in_array($deliveryStatus, ['completed', 'delivered'], true)): ?>
                                    <button class="action-btn btn-view-proof" onclick="viewDeliveryProof(<?= $order['id'] ?>)">
                                        <i class="fas fa-image"></i> View Proof
                                    </button>
                                    <?php if ($deliveryStatus === 'delivered'): ?>
                                        <button class="action-btn btn-delivered" onclick="updateDeliveryStatus(<?= (int) $order['id'] ?>, 'completed')">
                                            <i class="fas fa-user-check"></i> Confirm Received
                                        </button>
                                    <?php endif; ?>
                                <?php elseif (in_array($deliveryStatus, ['cancelled', 'return_refund'], true)): ?>
                                    <span class="status-badge" style="background: #f1f3f5; color: #6c757d; border: none;">
                                        <i class="fas fa-info-circle"></i> No further action
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge" style="background: #fff3cd; color: #856404; border: none;">
                                        <i class="fas fa-clock"></i> In Delivery Progress
                                    </span>
                                <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
            <?php else: ?>
                <div class="data-card">
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag"></i>
                        <h3>No Orders Found</h3>
                        <p>There are no orders in the system yet.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

<!-- Delivery Modal -->
<div id="deliveryModal" class="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-shipping-fast"></i> Delivery Information</h3>
                <button class="close" onclick="closeDeliveryModal()">&times;</button>
            </div>
            <form id="deliveryForm">
                <input type="hidden" id="deliveryOrderId" name="order_id">
                
                <div class="form-group">
                    <label for="tracking_number">Tracking Number:</label>
                    <input type="text" id="tracking_number" name="tracking_number" placeholder="Enter tracking number (optional)">
                </div>
                
                <div class="form-group">
                    <label for="shipping_address">Shipping Address:</label>
                    <textarea id="shipping_address" name="shipping_address" placeholder="Enter shipping address" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="contact_number">Contact Number:</label>
                    <input type="text" id="contact_number" name="contact_number" placeholder="Enter contact number" required>
                </div>
                
                <div class="form-group">
                    <label for="delivery_notes">Delivery Notes:</label>
                    <textarea id="delivery_notes" name="delivery_notes" placeholder="Enter delivery notes (optional)"></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeDeliveryModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Save Delivery Info</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="orderDetailsModal" class="modal" style="display:none;">
    <div class="modal-dialog">
        <div class="modal-content" style="max-width:760px;">
            <div class="modal-header">
                <h3><i class="fas fa-receipt"></i> Order Details</h3>
                <button class="close" onclick="closeOrderDetailsModal()">&times;</button>
            </div>
            <div id="orderDetailsBody" class="modal-body"></div>
        </div>
    </div>
</div>

<script>
const availableRiders = <?= json_encode(array_values(array_map(
    static fn ($rider) => ['id' => (int) ($rider['id'] ?? 0), 'name' => (string) ($rider['name'] ?? 'Rider')],
    $riders ?? []
)), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

function openDeliveryModal(orderId) {
    document.getElementById('deliveryOrderId').value = orderId;
    document.getElementById('deliveryModal').style.display = 'block';
    
    // Load existing delivery data if available
    fetch('<?= site_url('orders/get-delivery-info/') ?>' + orderId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('tracking_number').value = data.tracking_number || '';
                document.getElementById('shipping_address').value = data.shipping_address || '';
                document.getElementById('contact_number').value = data.contact_number || '';
            }
        })
        .catch(error => console.error('Error loading delivery info:', error));
}

function closeDeliveryModal() {
    document.getElementById('deliveryModal').style.display = 'none';
    document.getElementById('deliveryForm').reset();
}

// Handle delivery form submission
document.getElementById('deliveryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const orderId = formData.get('order_id');
    
    const deliveryData = {
        order_id: parseInt(orderId),
        tracking_number: formData.get('tracking_number'),
        shipping_address: formData.get('shipping_address'),
        contact_number: formData.get('contact_number'),
        delivery_notes: formData.get('delivery_notes')
    };
    
    fetch('<?= site_url('orders/save-delivery-info') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(deliveryData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Delivery information saved successfully!');
            closeDeliveryModal();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving delivery information.');
    });
});

function updateDeliveryStatus(orderId, newStatus) {
    if (confirm('Are you sure you want to update the delivery status?')) {
        fetch('<?= site_url('orders/update-delivery-status') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                order_id: orderId,
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Delivery status updated successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the status.');
        });
    }
}

function assignRider(orderId, currentRiderId = 0) {
    if (!Array.isArray(availableRiders) || availableRiders.length === 0) {
        alert('No rider accounts available.');
        return;
    }

    const options = availableRiders.map((r) => `${r.id}: ${r.name}`).join('\n');
    const input = prompt(`Enter Rider ID to assign:\n\n${options}`, currentRiderId > 0 ? String(currentRiderId) : '');
    if (!input) {
        return;
    }

    const riderId = parseInt(input, 10);
    if (!Number.isInteger(riderId) || riderId <= 0) {
        alert('Invalid rider ID.');
        return;
    }

    fetch('<?= site_url('dashboard/assignRiderToOrder') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            order_id: orderId,
            rider_id: riderId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Rider assigned successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to assign rider');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while assigning rider');
    });
}

function viewDeliveryProof(orderId) {
    console.log('Viewing delivery proof for order:', orderId); // Debug log
    
    fetch('<?= site_url('dashboard/getDeliveryProof') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            order_id: parseInt(orderId)
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response:', data); // Debug log
        if (data.success) {
            showDeliveryProofModal(data.proof);
        } else {
            alert(data.message || 'Failed to load delivery proof');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while loading delivery proof');
    });
}

function showDeliveryProofModal(proof) {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.style.display = 'block';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Delivery Proof</h3>
                    <span class="close" onclick="closeDeliveryProofModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <div style="text-align: center; margin-bottom: 1rem;">
                        <img src="<?= site_url('uploads/delivery_proofs/') ?>${proof.image}" 
                             style="max-width: 100%; max-height: 400px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" 
                             alt="Delivery Proof">
                    </div>
                    ${proof.notes ? `<div style="margin-top: 1rem;">
                        <strong>Notes:</strong> ${proof.notes}
                    </div>` : ''}
                    <div style="margin-top: 1rem; font-size: 0.9rem; color: #666;">
                        <strong>Submitted:</strong> ${new Date(proof.submitted_at).toLocaleString()}
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function closeDeliveryProofModal() {
    const modal = document.querySelector('.modal');
    if (modal) {
        modal.remove();
    }
}

// Auto-refresh functionality removed as requested

// Store original orders data
let originalOrders = [];

// Initialize orders data when page loads
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('.data-table tbody');
    if (tableBody) {
        const rows = tableBody.querySelectorAll('tr');
        rows.forEach(row => {
            if (!row.querySelector('.empty-state')) {
                originalOrders.push({
                    element: row,
                    orderId: row.querySelector('td:nth-child(1)')?.textContent?.toLowerCase() || '',
                    date: row.querySelector('td:nth-child(2)')?.textContent || '',
                    customer: row.querySelector('td:nth-child(3)')?.textContent?.toLowerCase() || '',
                    total: parseFloat(row.querySelector('td:nth-child(5)')?.textContent?.replace('₱', '').replace(',', '') || 0),
                    status: row.querySelector('td:nth-child(7)')?.textContent?.toLowerCase() || '',
                    deliveryStatus: row.querySelector('td:nth-child(8)')?.textContent?.toLowerCase() || ''
                });
            }
        });
    }
});

function filterOrders() {
    const searchTerm = document.getElementById('orderSearch').value.toLowerCase();
    const tableBody = document.querySelector('.data-table tbody');
    
    if (!tableBody) return;
    
    // Clear current table
    tableBody.innerHTML = '';
    
    // Filter orders
    const filteredOrders = originalOrders.filter(order => {
        return order.orderId.includes(searchTerm) || 
               order.customer.includes(searchTerm) ||
               order.status.includes(searchTerm) ||
               order.deliveryStatus.includes(searchTerm);
    });
    
    // Display filtered orders
    if (filteredOrders.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="12" class="text-center">
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <h3>No orders found</h3>
                        <p>Try adjusting your search criteria.</p>
                    </div>
                </td>
            </tr>
        `;
    } else {
        filteredOrders.forEach(order => {
            tableBody.appendChild(order.element);
        });
    }
}

function sortOrders() {
    const sortValue = document.getElementById('sortOptions').value;
    const tableBody = document.querySelector('.data-table tbody');
    
    if (!tableBody) return;
    
    if (sortValue === 'default') {
        // Reset to original order if default is selected
        tableBody.innerHTML = '';
        originalOrders.forEach(order => {
            tableBody.appendChild(order.element);
        });
        return;
    }
    
    // Always start with all original orders for filtering
    let currentOrders = [...originalOrders];
    
    // Handle specific status filtering
    if (sortValue.startsWith('filter-')) {
        const statusFilter = sortValue.replace('filter-', '').toLowerCase();
        console.log('Filtering by status:', statusFilter);
        console.log('Total orders to check:', currentOrders.length);
        
        const statusOrders = currentOrders.filter(order => {
            // Try multiple selectors to find the status element
            const statusElement = order.element.querySelector('td:nth-child(7) .status-badge') || 
                                 order.element.querySelector('td:nth-child(7) span') ||
                                 order.element.querySelector('td:nth-child(7)');
            
            if (!statusElement) {
                console.log('No status element found for order');
                return false;
            }
            
            // Get status text and trim it
            const statusText = statusElement.textContent.trim().toLowerCase();
            console.log('Order status text:', statusText);
            
            // Check CSS classes if they exist
            const hasStatusClass = statusElement.classList && 
                                  Array.from(statusElement.classList).some(cls => cls.includes(statusFilter));
            
            // Check if status matches by text content
            let matchesByStatus = false;
            if (statusFilter === 'pending') {
                matchesByStatus = ['pending', 'unpaid', 'to_pay'].includes(statusText);
            } else if (statusFilter === 'completed') {
                matchesByStatus = statusText === 'completed';
            } else if (statusFilter === 'cancelled') {
                matchesByStatus = statusText === 'cancelled';
            }
            
            const matches = hasStatusClass || matchesByStatus || statusText === statusFilter;
            console.log('Order matches filter:', matches);
            return matches;
        });
        
        console.log('Filtered orders count:', statusOrders.length);
        
        // Reorder table with filtered status
        tableBody.innerHTML = '';
        if (statusOrders.length === 0) {
            const statusName = statusFilter.toUpperCase();
            tableBody.innerHTML = `
                <tr>
                    <td colspan="12" class="text-center">
                        <div class="empty-state">
                            <i class="fas fa-filter"></i>
                            <h3>No ${statusName} orders found</h3>
                            <p>There are no orders with this status.</p>
                        </div>
                    </td>
                </tr>
            `;
        } else {
            statusOrders.forEach(order => {
                tableBody.appendChild(order.element);
            });
        }
        return;
    }
    
    // Sort orders
    currentOrders.sort((a, b) => {
        switch(sortValue) {
            case 'date-desc':
                return new Date(b.date) - new Date(a.date);
            case 'date-asc':
                return new Date(a.date) - new Date(b.date);
            case 'customer-asc':
                return a.customer.localeCompare(b.customer);
            case 'customer-desc':
                return b.customer.localeCompare(a.customer);
            case 'total-desc':
                return b.total - a.total;
            case 'total-asc':
                return a.total - b.total;
            case 'status-asc':
                return a.status.localeCompare(b.status);
            case 'status-desc':
                return b.status.localeCompare(a.status);
            default:
                return 0;
        }
    });
    
    // Reorder table
    tableBody.innerHTML = '';
    currentOrders.forEach(order => {
        tableBody.appendChild(order.element);
    });
}

function openOrderDetailsModal(orderId) {
    const modal = document.getElementById('orderDetailsModal');
    const body = document.getElementById('orderDetailsBody');
    body.innerHTML = '<p>Loading details...</p>';
    modal.style.display = 'block';

    fetch(`<?= site_url('dashboard/order-details-json') ?>/${orderId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success || !data.order) {
            body.innerHTML = `<p>${data.message || 'Unable to load details.'}</p>`;
            return;
        }

        const o = data.order;
        const items = (o.items || []).map(item => `
            <div style="display:flex;justify-content:space-between;gap:1rem;padding:.55rem 0;border-bottom:1px solid #f0f0f0;">
                <div>${item.name}<div style="font-size:.85rem;color:#666;">Qty: ${item.qty}</div></div>
                <div><strong>₱${(item.unit_price * item.qty).toFixed(2)}</strong></div>
            </div>
        `).join('') || '<p>No items found.</p>';

        body.innerHTML = `
            <div style="display:grid;gap:.7rem;">
                <div><strong>Order:</strong> ${o.reference_number}</div>
                <div><strong>Customer:</strong> ${o.customer_name} ${o.customer_email ? `(${o.customer_email})` : ''}</div>
                <div><strong>Address:</strong> ${o.shipping_address || 'Not provided'}</div>
                <div><strong>Coordinates:</strong> ${o.delivery_latitude && o.delivery_longitude ? `${o.delivery_latitude}, ${o.delivery_longitude}` : 'Not set'}</div>
                <div><strong>Contact:</strong> ${o.contact_number || 'Not provided'}</div>
                <div><strong>Notes:</strong> ${o.shipment_notes || 'None'}</div>
                <div><strong>Status:</strong> ${String(o.delivery_status || '').replaceAll('_', ' ')}</div>
                <div style="margin-top:.35rem;"><strong>Items</strong></div>
                <div>${items}</div>
            </div>
        `;
    })
    .catch(() => {
        body.innerHTML = '<p>An error occurred while loading details.</p>';
    });
}

function closeOrderDetailsModal() {
    document.getElementById('orderDetailsModal').style.display = 'none';
}

(() => {
    const endpoint = '<?= site_url('dashboard/live-update-token') ?>';
    let lastToken = null;
    let inFlight = false;

    async function checkForUpdates() {
        if (document.hidden || inFlight) {
            return;
        }

        inFlight = true;
        try {
            const response = await fetch(endpoint, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                cache: 'no-store'
            });
            if (!response.ok) {
                return;
            }

            const data = await response.json();
            if (!data || !data.success || !data.token) {
                return;
            }

            if (lastToken === null) {
                lastToken = data.token;
                return;
            }

            if (data.token !== lastToken) {
                window.location.reload();
            }
        } catch (error) {
            console.debug('Live update check failed:', error);
        } finally {
            inFlight = false;
        }
    }

    setTimeout(checkForUpdates, 2000);
    setInterval(checkForUpdates, 7000);
    window.addEventListener('focus', checkForUpdates);
})();
</script>

</body>
</html>
