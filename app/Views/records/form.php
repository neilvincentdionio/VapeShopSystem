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
            background: url('<?= base_url('assets/img/smokebg.jpg') ?>') center/cover no-repeat;
            min-height: 100vh; position: relative; color: #fff;
        }
        body::before { content: ''; position: absolute; inset: 0; background: rgba(0,0,0,.58); z-index: 1; }
        .navbar {
            position: sticky; top: 0; z-index: 20;
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.2);
            padding: 1rem 2rem;
            backdrop-filter: blur(18px);
        }
        .navbar-content { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; gap: 1.2rem; }
        .navbar-brand {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            flex: 0 0 auto;
        }
        .navbar-center { flex: 1 1 auto; display: flex; justify-content: center; min-width: 0; }
        .navbar-menu { display: flex; align-items: center; gap: .75rem; flex-wrap: nowrap; }
        .navbar-menu a, .nav-dropdown-btn {
            color: #fff;
            text-decoration: none;
            padding: .5rem 1rem;
            border-radius: 5px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-family: inherit;
            font-size: .95rem;
            transition: background-color .3s;
        }
        .navbar-menu a:hover, .nav-link.active, .nav-dropdown-btn:hover { background-color: rgba(255,255,255,.2); }
        .nav-dropdown { position: relative; }
        .nav-dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: .5rem;
            min-width: 220px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 12px;
            overflow: hidden;
            z-index: 50;
        }
        .nav-dropdown:hover .nav-dropdown-content { display: block; }
        .nav-dropdown-content a { display: block; }
        .nav-right { display: flex; align-items: center; gap: .8rem; flex: 0 0 auto; }
        .user-info { display: flex; align-items: center; gap: .55rem; color: #fff; }
        .user-name {
            max-width: 170px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700;
        }
        .badge {
            border: 1px solid rgba(255,255,255,.3);
            padding: .2rem .5rem;
            border-radius: 999px;
            font-size: .75rem;
        }
        .btn-danger {
            background-color: #dc3545;
            color: #fff;
            border-radius: 5px;
            padding: .5rem .8rem;
            text-decoration: none;
        }
        .btn-danger:hover { background-color: #c82333; }
        .nav-link { padding: .45rem .75rem; border-radius: 6px; }
        }
        .container { max-width: 760px; margin: 2rem auto; padding: 0 1rem; position: relative; z-index: 2; }
        .panel {
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 16px;
            padding: 1.2rem;
            backdrop-filter: blur(18px);
        }
        .field { margin-top: .8rem; }
        .field label { display: block; margin-bottom: .35rem; font-size: .9rem; }
        .field input, .field select {
            width: 100%;
            padding: .6rem .7rem;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,.3);
            background: rgba(255,255,255,.15);
            color: #fff;
            font-family: inherit;
        }
        .field select option { color: #000; }
        .error-list { margin-top: .8rem; color: #ffd2d2; }
        .actions { margin-top: 1rem; display: flex; gap: .6rem; flex-wrap: wrap; }
        .btn { text-decoration: none; padding: .55rem .8rem; border-radius: 8px; color: #fff; border: none; cursor: pointer; font-family: inherit; }
        .btn-primary { background: #2f6fed; }
        .btn-muted { background: rgba(255,255,255,.2); }
        @media (max-width: 768px) {
            .navbar-content { flex-direction: column; align-items: stretch; gap: .75rem; }
            .navbar-center { justify-content: flex-start; }
            .navbar-menu { flex-wrap: wrap; }
            .nav-right { justify-content: space-between; }
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
                    <a href="<?= site_url('records') ?>" class="nav-link active">Records</a>
                    <a href="<?= site_url('dashboard/profile') ?>" class="nav-link">Profile</a>
                    <?php if ($user_role === 'admin'): ?>
                        <a href="<?= site_url('dashboard/settings') ?>" class="nav-link">Settings</a>
                    <?php endif; ?>
                    <div class="nav-dropdown">
                        <button class="nav-dropdown-btn">Quick Actions</button>
                        <div class="nav-dropdown-content">
                            <a href="<?= site_url('records/create') ?>">Add Record</a>
                            <a href="<?= site_url('records') ?>">Manage Records</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="nav-right">
                <div class="user-info">
                    <div class="user-avatar"><?= strtoupper(substr($user_name, 0, 1)) ?></div>
                    <span class="user-name"><?= htmlspecialchars($user_name) ?></span>
                    <span class="badge"><?= htmlspecialchars(ucfirst($user_role)) ?></span>
                    <?php if (!empty($user_shop_name)): ?>
                        <span class="badge"><?= htmlspecialchars($user_shop_name) ?></span>
                    <?php endif; ?>
                </div>
                <a href="<?= site_url('auth/logout') ?>" class="btn-danger" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="panel">
            <h2><?= $is_edit ? 'Edit Record' : 'Add New Record' ?></h2>
            <?php if (!empty($user_shop_name)): ?><p style="margin-top:.4rem;">Shop: <?= htmlspecialchars($user_shop_name) ?></p><?php endif; ?>

            <?php if (!empty($errors)): ?>
                <ul class="error-list">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form method="post" action="<?= $is_edit ? site_url('records/update/' . $record['id']) : site_url('records/store') ?>">
                <?= csrf_field() ?>
                <div class="field">
                    <label>Record Type</label>
                    <?php $currentType = old('record_type', $record['record_type'] ?? 'sales'); ?>
                    <select name="record_type" required>
                        <option value="sales" <?= $currentType === 'sales' ? 'selected' : '' ?>>Sales</option>
                        <option value="purchase" <?= $currentType === 'purchase' ? 'selected' : '' ?>>Purchase</option>
                        <option value="inventory" <?= $currentType === 'inventory' ? 'selected' : '' ?>>Inventory</option>
                        <option value="expense" <?= $currentType === 'expense' ? 'selected' : '' ?>>Expense</option>
                    </select>
                </div>
                <div class="field">
                    <label>Reference Number</label>
                    <input type="text" name="reference_number" value="<?= old('reference_number', $record['reference_number'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label>Title</label>
                    <input type="text" name="title" value="<?= old('title', $record['title'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label>Description</label>
                    <input type="text" name="description" value="<?= old('description', $record['description'] ?? '') ?>">
                </div>
                <div class="field">
                    <label>Quantity</label>
                    <input type="number" name="quantity" min="0" value="<?= old('quantity', $record['quantity'] ?? 0) ?>" required>
                </div>
                <div class="field">
                    <label>Unit Price</label>
                    <input type="number" name="unit_price" step="0.01" min="0" value="<?= old('unit_price', $record['unit_price'] ?? '0.00') ?>" required>
                </div>
                <div class="field">
                    <label>Payment Method</label>
                    <?php $currentMethod = old('payment_method', $record['payment_method'] ?? 'cash'); ?>
                    <select name="payment_method">
                        <option value="cash" <?= $currentMethod === 'cash' ? 'selected' : '' ?>>Cash</option>
                        <option value="card" <?= $currentMethod === 'card' ? 'selected' : '' ?>>Card</option>
                        <option value="gcash" <?= $currentMethod === 'gcash' ? 'selected' : '' ?>>GCash</option>
                        <option value="bank_transfer" <?= $currentMethod === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                    </select>
                </div>
                <div class="field">
                    <label>Payment Status</label>
                    <?php $currentPaymentStatus = old('payment_status', $record['payment_status'] ?? 'unpaid'); ?>
                    <select name="payment_status">
                        <option value="paid" <?= $currentPaymentStatus === 'paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="partial" <?= $currentPaymentStatus === 'partial' ? 'selected' : '' ?>>Partial</option>
                        <option value="unpaid" <?= $currentPaymentStatus === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                    </select>
                </div>
                <div class="field">
                    <label>Record Date</label>
                    <input type="date" name="record_date" value="<?= old('record_date', !empty($record['record_date']) ? date('Y-m-d', strtotime($record['record_date'])) : date('Y-m-d')) ?>" required>
                </div>
                <div class="field">
                    <label>Status</label>
                    <?php $currentStatus = old('status', $record['status'] ?? 'pending'); ?>
                    <select name="status" required>
                        <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="completed" <?= $currentStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $currentStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="field">
                    <label>Notes</label>
                    <input type="text" name="notes" value="<?= old('notes', $record['notes'] ?? '') ?>">
                </div>
                <div class="actions">
                    <button type="submit" class="btn btn-primary"><?= $is_edit ? 'Update Record' : 'Save Record' ?></button>
                    <a href="<?= site_url('records') ?>" class="btn btn-muted">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
