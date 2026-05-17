<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - Quick Puff</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <?= $this->include('admin/partials/rbac_styles') ?>
    <?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body class="rbac-page">
<?= $this->include('admin/partials/sidebar') ?>
<div class="rbac-container">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="flash flash-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="flash flash-error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="rbac-card">
        <div class="rbac-card-header">
            <div>
                <h1 class="rbac-card-title">System Permissions</h1>
                <p class="rbac-card-sub"><?= count($permissions) ?> permissions defined</p>
            </div>
            <a href="<?= site_url('admin/permissions/create') ?>" class="btn btn-primary">+ Create Permission</a>
        </div>
        <div class="rbac-card-body">
            <div class="rbac-table-wrap">
            <table class="rbac-table rbac-table-permissions">
                <thead>
                    <tr>
                        <th class="col-name">Name</th>
                        <th class="col-desc">Description</th>
                        <th class="col-count">Assigned Roles</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($permissions === []): ?>
                    <tr><td colspan="4" style="padding:1.25rem;">No permissions found.</td></tr>
                <?php else: ?>
                    <?php foreach ($permissions as $permission): ?>
                        <tr>
                            <td class="col-name"><strong><?= esc($permission['name']) ?></strong></td>
                            <td class="col-desc"><?= esc($permission['description'] ?? '') ?></td>
                            <td class="col-count"><?= (int) ($permission['assigned_roles'] ?? 0) ?></td>
                            <td class="col-actions">
                                <div class="actions">
                                    <a class="btn btn-link btn-sm" href="<?= site_url('admin/permissions/' . $permission['id']) ?>">View</a>
                                    <a class="btn btn-link btn-sm" href="<?= site_url('admin/permissions/edit/' . $permission['id']) ?>">Edit</a>
                                    <form method="post" action="<?= site_url('admin/permissions/delete/' . $permission['id']) ?>" onsubmit="return confirm('Delete this permission?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
