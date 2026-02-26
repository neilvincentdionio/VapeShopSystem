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
        .navbar-content { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; gap: 1.2rem; align-items: center; }
        .navbar-brand {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            flex: 0 0 auto;
        }
        .navbar-center { flex: 1 1 auto; display: flex; justify-content: center; min-width: 0; }
        .navbar-menu { display: flex; gap: .75rem; align-items: center; flex-wrap: nowrap; }
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
        .nav-link { padding: .45rem .75rem; border-radius: 6px; }
        .navbar-menu a:hover, .nav-link.active, .nav-dropdown-btn:hover { background: rgba(255,255,255,.2); }
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

        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; position: relative; z-index: 2; }
        .panel {
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 16px;
            padding: 1rem;
            backdrop-filter: blur(18px);
            margin-bottom: 1rem;
        }
        .row { display: flex; gap: .6rem; flex-wrap: wrap; align-items: center; }
        input, select, button {
            font-family: inherit;
            border: 1px solid rgba(255,255,255,.3);
            background: rgba(255,255,255,.15);
            color: #fff;
            border-radius: 8px;
            padding: .55rem .7rem;
        }
        select option { color: #000; }
        .btn { text-decoration: none; display: inline-block; padding: .55rem .75rem; border-radius: 8px; color: #fff; border: none; cursor: pointer; }
        .btn-primary { background: #2f6fed; }
        .btn-success { background: #1f9d55; }
        .btn-warning { background: #d48806; }
        .btn-danger { background: #dc3545; }
        .btn-muted { background: rgba(255,255,255,.2); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: .7rem; border-bottom: 1px solid rgba(255,255,255,.15); text-align: left; font-size: .92rem; }
        th { color: #f3f3f3; }
        .status-active { color: #8ff0b2; font-weight: 600; }
        .status-inactive { color: #ffc3c3; font-weight: 600; }
        .alert { padding: .8rem; border-radius: 8px; margin-bottom: 1rem; }
        .alert-success { background: rgba(46, 204, 113, .25); }
        .alert-error { background: rgba(231, 76, 60, .25); }
        .actions { display: flex; gap: .4rem; flex-wrap: wrap; }
        .pagination-wrap { margin-top: 1rem; }
        .pagination-wrap ul { list-style: none; display: flex; gap: .35rem; flex-wrap: wrap; }
        .pagination-wrap a, .pagination-wrap span {
            color: #fff; text-decoration: none; padding: .35rem .6rem; border: 1px solid rgba(255,255,255,.3); border-radius: 6px;
        }
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
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= htmlspecialchars(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error"><?= htmlspecialchars(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <div class="panel">
            <div class="row" style="justify-content: space-between;">
                <h2>Records Module</h2>
                <?php if (!empty($user_shop_name)): ?><span>Shop: <?= htmlspecialchars($user_shop_name) ?></span><?php endif; ?>
                <a href="<?= site_url('records/create') ?>" class="btn btn-success">Add Record</a>
            </div>
        </div>

        <div class="panel">
            <form action="<?= site_url('records') ?>" method="get" class="row">
                <input type="text" name="q" placeholder="Search reference, title, description..." value="<?= htmlspecialchars($search) ?>">
                <select name="record_type">
                    <option value="">All Types</option>
                    <?php foreach ($record_types as $type): ?>
                        <option value="<?= htmlspecialchars($type['record_type']) ?>" <?= $record_type === $type['record_type'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($type['record_type'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="status">
                    <option value="">All Status</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="<?= site_url('records') ?>" class="btn btn-muted">Reset</a>
            </form>
        </div>

        <div class="panel" style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Reference</th>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($records)): ?>
                        <?php foreach ($records as $item): ?>
                            <tr>
                                <td><?= (int) $item['id'] ?></td>
                                <td><?= htmlspecialchars((string) ($item['reference_number'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(ucfirst((string) ($item['record_type'] ?? ''))) ?></td>
                                <td><?= htmlspecialchars((string) ($item['title'] ?? '')) ?></td>
                                <td><?= (int) ($item['quantity'] ?? 0) ?></td>
                                <td>$<?= number_format((float) ($item['unit_price'] ?? 0), 2) ?></td>
                                <td>$<?= number_format((float) ($item['total_amount'] ?? 0), 2) ?></td>
                                <td><?= htmlspecialchars(ucfirst((string) ($item['payment_status'] ?? 'unpaid'))) ?></td>
                                <td class="<?= ($item['status'] ?? '') === 'completed' ? 'status-active' : 'status-inactive' ?>"><?= htmlspecialchars(ucfirst((string) ($item['status'] ?? 'pending'))) ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="<?= site_url('records/edit/' . $item['id']) ?>" class="btn btn-warning">Edit</a>
                                        <?php if (in_array($user_role, ['admin', 'seller'], true)): ?>
                                            <form action="<?= site_url('records/delete/' . $item['id']) ?>" method="post" onsubmit="return confirm('Delete this record?')" style="display:inline;">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="pagination-wrap">
                <?= $pager->links() ?>
            </div>
        </div>
    </div>
</body>
</html>
