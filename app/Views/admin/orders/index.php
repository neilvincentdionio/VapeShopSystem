<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - E-Commerce Vape Shop System</title>
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
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .orders-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .orders-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin: 0;
        }
        
        .orders-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
        }
        
        .orders-table {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: left;
            font-weight: 700;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .table td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;
        }
        
        .table tr:hover {
            background: #f8f9fa;
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
        }
        
        .btn-checkout:hover {
            background: #219653;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(39, 197, 111, 0.3);
        }
        
        .btn-completed {
            background: #e8f5e8;
            color: #2e7d2e;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid #c8e6c9;
        }
        
        @media (max-width: 768px) {
            .orders-container {
                padding: 1rem;
            }
            
            .orders-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .orders-stats {
                grid-template-columns: 1fr;
            }
            
            .table {
                font-size: 0.8rem;
            }
            
            .table th,
            .table td {
                padding: 0.5rem;
            }
            
            .order-items-preview {
                max-width: 150px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <a href="<?= site_url('dashboard') ?>" class="navbar-brand">E-Commerce Vape Shop</a>

            <div class="navbar-center">
                <div class="navbar-menu">
                    <a href="<?= site_url('dashboard') ?>" class="nav-link">Dashboard</a>
                    <a href="<?= site_url('products') ?>" class="nav-link">Products</a>
                    <a href="<?= site_url('orders') ?>" class="nav-link active">Orders</a>
                    <a href="<?= site_url('records') ?>" class="nav-link">Records</a>
                    <a href="<?= site_url('user-management') ?>" class="nav-link">User Management</a>
                    <a href="<?= site_url('dashboard/settings') ?>" class="nav-link">Settings</a>
                </div>
            </div>

            <div class="nav-right">
                <div class="user-info">
                    <div class="user-avatar"><?= strtoupper(substr(session()->get('user_name') ?? 'A', 0, 1)) ?></div>
                    <a href="<?= site_url('dashboard/profile') ?>" class="user-name user-profile-link">
                        <?= esc(session()->get('user_name') ?? 'Administrator') ?>
                    </a>
                    <span class="badge"><?= esc(strtoupper(session()->get('user_role') ?? 'admin')) ?></span>
                    <?php if (!empty(session()->get('user_shop_name'))): ?>
                        <span class="badge"><?= esc(session()->get('user_shop_name')) ?></span>
                    <?php endif; ?>
                </div>
                <a href="<?= site_url('auth/logout') ?>" class="btn-danger" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </nav>

<div class="orders-container">
    <div class="orders-header">
        <h1>Orders Management</h1>
    </div>
    
    <?php if (isset($orders) && !empty($orders)): ?>
        <!-- Statistics -->
        <div class="orders-stats">
            <div class="stat-card">
                <div class="stat-label">Total Orders</div>
                <div class="stat-value"><?= count($orders) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">₱<?= number_format(array_sum(array_column($orders, 'total_amount')), 2) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Completed Orders</div>
                <div class="stat-value"><?= count(array_filter($orders, fn($o) => $o['status'] === 'completed')) ?></div>
            </div>
        </div>
        
        <!-- Orders Table -->
        <div class="orders-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <div class="order-id"><?= esc($order['reference_number']) ?></div>
                            </td>
                            <td>
                                <?= date('M j, Y', strtotime($order['date'])) ?>
                                <br>
                                <small style="color: #666;"><?= date('g:i A', strtotime($order['date'])) ?></small>
                            </td>
                            <td>
                                <?php if ($order['customer']): ?>
                                    <div class="customer-info">
                                        <div class="customer-name"><?= esc($order['customer']['name']) ?></div>
                                        <div class="customer-email"><?= esc($order['customer']['email']) ?></div>
                                    </div>
                                <?php else: ?>
                                    <span style="color: #999;">Guest</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="order-items-preview">
                                    <?php if (!empty($order['items'])): ?>
                                        <?php $itemCount = count($order['items']); ?>
                                        <?php for ($i = 0; $i < min(2, $itemCount); $i++): ?>
                                            <div class="item-preview">
                                                <?= esc($order['items'][$i]['name']) ?> × <?= (int) $order['items'][$i]['qty'] ?>
                                            </div>
                                        <?php endfor; ?>
                                        <?php if ($itemCount > 2): ?>
                                            <div class="more-items">+<?= $itemCount - 2 ?> more items</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: #999;">No items</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="order-total">₱<?= number_format((float) $order['total_amount'], 2) ?></div>
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
                                <?php if ($order['status'] === 'pending'): ?>
                                    <button class="btn-checkout" onclick="processAdminCheckout(<?= $order['id'] ?>)">
                                        <i class="fas fa-cash-register"></i>
                                        Checkout
                                    </button>
                                <?php elseif ($order['status'] === 'completed'): ?>
                                    <span class="btn-completed">
                                        <i class="fas fa-check"></i>
                                        Completed
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-shopping-bag"></i>
            <h3>No Orders Found</h3>
            <p>There are no orders in the system yet.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function processAdminCheckout(orderId) {
    window.location.href = '<?= site_url('orders/checkout/') ?>' + orderId;
}
</script>

</body>
</html>
