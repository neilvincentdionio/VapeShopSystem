<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Roles - Quick Puff Vape Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f8; color: #111827; }
        .container { max-width: 1240px; margin: 1.5rem auto; padding: 0 1.25rem; }
        .stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.85rem; margin-bottom: 1rem; }
        .stat-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 0.9rem 1rem; }
        .stat-label { font-size: 0.75rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.04em; }
        .stat-value { margin-top: 0.25rem; font-size: 1.45rem; font-weight: 700; color: #111827; }
        .panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; }
        .panel-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; padding: 1rem 1.15rem; border-bottom: 1px solid #eef2f7; flex-wrap: wrap; }
        .panel-title { font-size: 1.1rem; font-weight: 700; }
        .panel-subtitle { margin-top: 0.2rem; font-size: 0.8rem; color: #6b7280; }
        .filters { display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; }
        .filters input, .filters select { border: 1px solid #d1d5db; border-radius: 8px; padding: 0.45rem 0.65rem; font-size: 0.85rem; }
        .filters input { min-width: 200px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; border: 1px solid transparent; border-radius: 999px; padding: 0.4rem 0.8rem; font-size: 0.76rem; font-weight: 600; text-decoration: none; cursor: pointer; white-space: nowrap; }
        .btn-create { background: #1f9d55; color: #fff; }
        .btn-view, .btn-edit, .btn-assign { background: #f3f4f6; border-color: #e5e7eb; color: #374151; }
        .btn-warn { background: #fef3c7; border-color: #fde68a; color: #92400e; }
        .btn-delete { background: #ef4444; color: #fff; }
        .table { width: 100%; border-collapse: collapse; }
        .table th { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.04em; color: #6b7280; text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eef2f7; }
        .table td { padding: 0.75rem 1rem; border-bottom: 1px solid #f3f4f6; font-size: 0.88rem; vertical-align: middle; }
        .table tr:last-child td { border-bottom: none; }
        .role-name { font-weight: 700; display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap; }
        .badge { display: inline-flex; align-items: center; border-radius: 999px; padding: 0.12rem 0.5rem; font-size: 0.68rem; font-weight: 700; }
        .badge-system { background: #dbeafe; color: #1e40af; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }
        .actions { display: flex; justify-content: flex-end; gap: 0.35rem; flex-wrap: wrap; }
        .alert { margin-bottom: 0.85rem; border-radius: 10px; padding: 0.75rem 0.95rem; font-size: 0.88rem; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .empty { text-align: center; color: #6b7280; padding: 1.5rem; }
        @media (max-width: 900px) { .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    </style>
    <?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
<?= $this->include('admin/partials/sidebar') ?>
<div class="container">
    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

    <section class="stats">
        <article class="stat-card"><p class="stat-label">Total Roles</p><p class="stat-value"><?= esc((string) ($stats['total'] ?? 0)) ?></p></article>
        <article class="stat-card"><p class="stat-label">Active Roles</p><p class="stat-value"><?= esc((string) ($stats['active'] ?? 0)) ?></p></article>
        <article class="stat-card"><p class="stat-label">System Roles</p><p class="stat-value"><?= esc((string) ($stats['system'] ?? 0)) ?></p></article>
        <article class="stat-card"><p class="stat-label">Custom Roles</p><p class="stat-value"><?= esc((string) ($stats['custom'] ?? 0)) ?></p></article>
    </section>

    <section class="panel">
        <div class="panel-top">
            <div>
                <h1 class="panel-title">System Roles</h1>
                <p class="panel-subtitle">Manage access through permissions (no role levels)</p>
            </div>
            <a href="<?= site_url('admin/roles/create') ?>" class="btn btn-create">+ Create Role</a>
        </div>

        <form class="filters" method="get" action="<?= site_url('admin/roles') ?>" style="padding: 0.85rem 1rem; border-bottom: 1px solid #eef2f7;">
            <input type="text" name="q" value="<?= esc((string) ($filters['q'] ?? '')) ?>" placeholder="Search role name...">
            <select name="status">
                <option value="">All status</option>
                <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
            <select name="type">
                <option value="">All types</option>
                <option value="system" <?= ($filters['type'] ?? '') === 'system' ? 'selected' : '' ?>>System</option>
                <option value="custom" <?= ($filters['type'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom</option>
            </select>
            <button type="submit" class="btn btn-view">Filter</button>
            <a href="<?= site_url('admin/roles') ?>" class="btn btn-view">Reset</a>
        </form>

        <?php if (empty($roles)): ?>
            <p class="empty">No roles found.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Role Name</th>
                        <th>Description</th>
                        <th>Permissions</th>
                        <th>Users</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $role): ?>
                        <?php $roleId = (int) ($role['id'] ?? 0); ?>
                        <tr>
                            <td>
                                <div class="role-name">
                                    <?= esc((string) ($role['name'] ?? '')) ?>
                                    <?php if (!empty($role['is_system'])): ?><span class="badge badge-system">System Role</span><?php endif; ?>
                                </div>
                            </td>
                            <td><?= esc((string) ($role['description'] ?? '-')) ?></td>
                            <td><?= esc((string) ((int) ($role['permission_count'] ?? 0))) ?></td>
                            <td><?= esc((string) ((int) ($role['user_count'] ?? 0))) ?></td>
                            <td><span class="badge <?= ($role['status_label'] ?? '') === 'Inactive' ? 'badge-inactive' : 'badge-active' ?>"><?= esc((string) ($role['status_label'] ?? 'Active')) ?></span></td>
                            <td>
                                <div class="actions">
                                    <a href="<?= site_url('admin/roles/permissions/' . $roleId) ?>" class="btn btn-view">View Permissions</a>
                                    <a href="<?= site_url('admin/roles/assign-users/' . $roleId) ?>" class="btn btn-assign">Assign Users</a>
                                    <a href="<?= site_url('admin/roles/edit/' . $roleId) ?>" class="btn btn-edit">Edit Role</a>
                                    <?php if (empty($role['is_system'])): ?>
                                        <form action="<?= site_url('admin/roles/toggle/' . $roleId) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-warn"><?= ($role['status_label'] ?? '') === 'Inactive' ? 'Activate' : 'Deactivate' ?></button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if (empty($role['is_system'])): ?>
                                        <form action="<?= site_url('admin/roles/delete/' . $roleId) ?>" method="post" onsubmit="return confirm('Delete this custom role permanently?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-delete">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>
</body>
</html>
