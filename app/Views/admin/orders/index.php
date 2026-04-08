<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - E-Commerce Vape Shop System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
<?php
// Helper function to get delivery status labels
if (!function_exists('getDeliveryStatusLabel')) {
    function getDeliveryStatusLabel($status) {
        $labels = [
            'to_pay' => 'To Pay',
            'to_ship' => 'To Ship',
            'to_receive' => 'To Receive',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'return_refund' => 'Return/Refund',
            'failed_delivery' => 'Failed Delivery'
        ];
        
        return $labels[$status] ?? ucfirst($status);
    }
}

$ordersList = is_array($orders ?? null) ? $orders : [];
$hasOrders = ! empty($ordersList);
$totalOrders = count($ordersList);
$totalRevenue = array_sum(array_map(static fn ($order) => (float) ($order['total_amount'] ?? 0), $ordersList));
$completedOrders = count(array_filter(
    $ordersList,
    static fn ($order) => ($order['status'] ?? '') === 'completed'
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

        .btn-checkout,
        .btn-transit,
        .btn-delivered,
        .btn-delivery,
        .btn-failed {
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
                    <button class="btn btn-sm btn-outline" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
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
                            </td>
                            <td>
                                <div class="order-status status-<?= esc($order['status']) ?>">
                                    <?= esc(ucfirst($order['status'])) ?>
                                </div>
                            </td>
                            <td>
                                <div class="order-status status-<?= esc($order['delivery_status'] ?? 'to_pay') ?>">
                                    <?= getDeliveryStatusLabel($order['delivery_status'] ?? 'to_pay') ?>
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
                                <span class="detail-text">
                                    <?= esc($order['contact_number'] ?: 'Not provided') ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if (($order['delivery_status'] ?? 'to_pay') === 'to_pay'): ?>
                                        <button class="btn-checkout" onclick="updateDeliveryStatus(<?= $order['id'] ?>, 'to_ship')">
                                            <i class="fas fa-box"></i>
                                            Preparing Package
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if (($order['delivery_status'] ?? 'to_pay') === 'to_ship'): ?>
                                        <button class="btn-transit" onclick="updateDeliveryStatus(<?= $order['id'] ?>, 'to_receive')">
                                            <i class="fas fa-truck"></i>
                                            Package in Transit
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if (($order['delivery_status'] ?? 'to_pay') === 'to_receive'): ?>
                                        <button class="btn-delivered" onclick="updateDeliveryStatus(<?= $order['id'] ?>, 'completed')">
                                            <i class="fas fa-check-circle"></i>
                                            Package Delivered
                                        </button>
                                        <button class="btn-failed" onclick="updateDeliveryStatus(<?= $order['id'] ?>, 'failed_delivery')">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Failed Delivery
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if (($order['delivery_status'] ?? 'to_pay') === 'failed_delivery'): ?>
                                        <button class="btn-transit" onclick="updateDeliveryStatus(<?= $order['id'] ?>, 'to_receive')">
                                            <i class="fas fa-redo"></i>
                                            Retry Delivery
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if (in_array($order['delivery_status'] ?? 'to_pay', ['to_ship', 'to_receive', 'failed_delivery'])): ?>
                                        <button class="btn-delivery" onclick="openDeliveryModal(<?= $order['id'] ?>)">
                                            <i class="fas fa-shipping-fast"></i>
                                            Delivery Info
                                        </button>
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

<script>
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
</script>

</body>
</html>
