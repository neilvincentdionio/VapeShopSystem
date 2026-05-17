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
                <h1 class="rbac-card-title">System Roles</h1>
                <p class="rbac-card-sub"><?= count($roles) ?> roles defined</p>
            </div>
            <a href="<?= site_url('admin/roles/create') ?>" class="btn btn-primary">+ Create Role</a>
        </div>
        <div class="rbac-card-body">
            <div class="rbac-table-wrap">
                <table class="rbac-table">
                    <thead>
                        <tr>
                            <th class="col-name">Name</th>
                            <th class="col-desc">Description</th>
                            <th class="col-level">Level</th>
                            <th class="col-count">Permissions</th>
                            <th class="col-count">Users</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($roles === []): ?>
                        <tr><td colspan="6" style="padding:1.25rem;">No roles found. Run the Permission seeder to initialize RBAC.</td></tr>
                    <?php else: ?>
                        <?php foreach ($roles as $role): ?>
                            <tr>
                                <td class="col-name"><strong><?= esc($role['name']) ?></strong></td>
                                <td class="col-desc"><?= esc($role['description'] ?? '') ?></td>
                                <td class="col-level"><span class="level-badge"><?= (int) ($role['level'] ?? 100) ?></span></td>
                                <td class="col-count"><?= (int) ($role['permission_count'] ?? 0) ?></td>
                                <td class="col-count"><?= (int) ($role['user_count'] ?? 0) ?></td>
                                <td class="col-actions">
                                    <div class="actions">
                                        <a class="btn btn-link btn-sm" href="<?= site_url('admin/roles/' . $role['id']) ?>">View</a>
                                        <a class="btn btn-link btn-sm" href="<?= site_url('admin/roles/edit/' . $role['id']) ?>">Edit</a>
                                        <form method="post" action="<?= site_url('admin/roles/delete/' . $role['id']) ?>" onsubmit="return confirm('Delete this role?');">
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
