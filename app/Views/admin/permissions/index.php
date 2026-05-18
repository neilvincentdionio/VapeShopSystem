<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Permissions - Quick Puff Vape Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f8; color: #111827; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1.5rem; }
        .panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; }
        .panel-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; padding: 1rem 1.25rem 0.9rem; border-bottom: 1px solid #eef2f7; }
        .panel-title { font-size: 1.1rem; font-weight: 700; color: #111827; }
        .panel-subtitle { margin-top: 0.2rem; font-size: 0.8rem; color: #6b7280; }
        .btn { display: inline-flex; align-items: center; justify-content: center; border: 1px solid transparent; border-radius: 999px; padding: 0.4rem 0.85rem; font-size: 0.78rem; font-weight: 600; text-decoration: none; cursor: pointer; line-height: 1.2; transition: all 0.2s ease; }
        .btn-create { background: #1f9d55; border-color: #1f9d55; color: #fff; }
        .btn-create:hover { background: #177f45; border-color: #177f45; }
        .btn-view, .btn-edit { background: #f3f4f6; border-color: #e5e7eb; color: #4b5563; }
        .btn-view:hover, .btn-edit:hover { background: #e5e7eb; }
        .btn-delete { background: #ef4444; border-color: #ef4444; color: #fff; }
        .btn-delete:hover { background: #dc2626; border-color: #dc2626; }
        .table { width: 100%; border-collapse: collapse; }
        .table thead th { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.04em; color: #6b7280; text-align: left; padding: 0.8rem 1.25rem; border-bottom: 1px solid #eef2f7; font-weight: 700; }
        .table tbody td { padding: 0.82rem 1.25rem; border-bottom: 1px solid #f3f4f6; font-size: 0.9rem; color: #374151; vertical-align: middle; }
        .table tbody tr:last-child td { border-bottom: none; }
        .perm-name { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.86rem; font-weight: 600; color: #111827; }
        .actions { display: flex; justify-content: flex-end; gap: 0.4rem; }
        .empty-state { text-align: center; color: #6b7280; padding: 1.8rem; font-size: 0.92rem; }
        .alert { margin-bottom: 0.9rem; border-radius: 10px; padding: 0.75rem 0.95rem; font-size: 0.9rem; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
    <?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
<?= $this->include('admin/partials/sidebar') ?>
<div class="container">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <section class="panel">
        <header class="panel-header">
            <div>
                <h1 class="panel-title">System Permissions</h1>
                <p class="panel-subtitle"><?= esc((string) count($permissions)) ?> permissions defined</p>
            </div>
            <a href="<?= site_url('admin/permissions/create') ?>" class="btn btn-create">+ Create Permission</a>
        </header>

        <?php if (empty($permissions_grouped ?? [])): ?>
            <p class="empty-state">No permissions found. Create your first permission.</p>
        <?php else: ?>
            <?php foreach ($permissions_grouped as $module => $modulePermissions): ?>
                <div style="padding:0.75rem 1.25rem 0.35rem;font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:#6b7280;border-top:1px solid #eef2f7;">
                    <?= esc((string) $module) ?>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Assigned Roles</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($modulePermissions as $permission): ?>
                            <?php $permissionId = (int) ($permission['id'] ?? 0); ?>
                            <tr>
                                <td class="perm-name"><?= esc((string) ($permission['name'] ?? '')) ?></td>
                                <td><?= esc((string) ($permission['description'] ?? '-')) ?></td>
                                <td><?= esc((string) ((int) ($permission['role_count'] ?? 0))) ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="<?= site_url('admin/permissions/view/' . $permissionId) ?>" class="btn btn-view">View</a>
                                        <a href="<?= site_url('admin/permissions/edit/' . $permissionId) ?>" class="btn btn-edit">Edit</a>
                                        <form action="<?= site_url('admin/permissions/delete/' . $permissionId) ?>" method="post" onsubmit="return confirm('Delete this permission?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>
</body>
</html>
