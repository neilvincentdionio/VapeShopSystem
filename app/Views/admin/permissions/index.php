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
        .panel-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; padding: 1.1rem 1.3rem 1rem; border-bottom: 1px solid #eef2f7; }
        .panel-title { font-size: 1.35rem; font-weight: 700; color: #111827; }
        .panel-subtitle { margin-top: 0.28rem; font-size: 0.92rem; color: #4b5563; }
        .btn { display: inline-flex; align-items: center; justify-content: center; border: 1px solid transparent; border-radius: 999px; padding: 0.46rem 0.95rem; font-size: 0.84rem; font-weight: 600; text-decoration: none; cursor: pointer; line-height: 1.2; transition: all 0.2s ease; }
        .btn-create { background: #1f9d55; border-color: #1f9d55; color: #fff; }
        .btn-create:hover { background: #177f45; border-color: #177f45; }
        .btn-view, .btn-edit { background: #f3f4f6; border-color: #e5e7eb; color: #4b5563; }
        .btn-view:hover, .btn-edit:hover { background: #e5e7eb; }
        .btn-delete { background: #ef4444; border-color: #ef4444; color: #fff; }
        .btn-delete:hover { background: #dc2626; border-color: #dc2626; }
        .module-card { margin: 0.95rem 1.1rem 1.05rem; border: 1px solid #e8edf3; border-radius: 12px; overflow: hidden; background: #fff; }
        .module-header { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: 0.78rem 1rem; background: #f8fafc; border-bottom: 1px solid #eef2f7; }
        .module-title { font-size: 0.86rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #475569; }
        .module-count { font-size: .78rem; color: #475569; font-weight: 600; background: #e2e8f0; border-radius: 999px; padding: .16rem .55rem; }
        .table-wrap { overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; min-width: 760px; table-layout: fixed; }
        .table col.col-name { width: 22%; }
        .table col.col-desc { width: 36%; }
        .table col.col-roles { width: 14%; }
        .table col.col-actions { width: 28%; }
        .table thead th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: #6b7280; text-align: left; padding: 0.85rem 1rem; border-bottom: 1px solid #eef2f7; font-weight: 700; }
        .table thead th.col-roles { text-align: center; }
        .table thead th.col-actions { text-align: right; }
        .table tbody td { padding: 0.88rem 1rem; border-bottom: 1px solid #f3f4f6; font-size: 0.94rem; color: #374151; vertical-align: middle; }
        .table tbody td.col-roles { text-align: center; }
        .table tbody tr:last-child td { border-bottom: none; }
        .perm-name { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.92rem; font-weight: 700; color: #111827; }
        .perm-desc { font-size: 0.9rem; color: #475569; line-height: 1.35; word-wrap: break-word; overflow-wrap: anywhere; }
        .role-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 2.1rem; padding: .2rem .55rem; border-radius: 999px; font-size: .82rem; font-weight: 700; background: #e0f2fe; color: #0369a1; }
        .actions { display: flex; justify-content: flex-end; gap: 0.4rem; }
        .empty-state { text-align: center; color: #6b7280; padding: 1.8rem; font-size: 0.92rem; }
        .alert { margin-bottom: 0.9rem; border-radius: 10px; padding: 0.75rem 0.95rem; font-size: 0.9rem; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        @media (max-width: 768px) {
            .container { padding: 0 .85rem; }
            .panel-header { flex-direction: column; align-items: stretch; }
            .panel-title { font-size: 1.18rem; }
            .module-card { margin: .85rem .75rem; }
            .actions { justify-content: flex-start; flex-wrap: wrap; }
        }
    </style>
    <?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
<?= $this->include('admin/partials/sidebar') ?>
<div class="container">
    <?php
    $pageHeaderTitle = 'Permissions Management';
    $pageHeaderSubtitle = 'Control action-level access that can be assigned to roles.';
    ?>
    <?= $this->include('admin/partials/page_header') ?>

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
                <section class="module-card">
                    <header class="module-header">
                        <span class="module-title"><?= esc((string) $module) ?></span>
                        <span class="module-count"><?= esc((string) count($modulePermissions)) ?> permission<?= count($modulePermissions) === 1 ? '' : 's' ?></span>
                    </header>
                    <div class="table-wrap">
                        <table class="table">
                            <colgroup>
                                <col class="col-name">
                                <col class="col-desc">
                                <col class="col-roles">
                                <col class="col-actions">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th class="col-roles">Assigned Roles</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modulePermissions as $permission): ?>
                                    <?php $permissionId = (int) ($permission['id'] ?? 0); ?>
                                    <tr>
                                        <td class="perm-name"><?= esc((string) ($permission['name'] ?? '')) ?></td>
                                        <td class="perm-desc"><?= esc((string) ($permission['description'] ?? '-')) ?></td>
                                        <td class="col-roles"><span class="role-badge"><?= esc((string) ((int) ($permission['role_count'] ?? 0))) ?></span></td>
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
                    </div>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>
</body>
</html>
