<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - Quick Puff</title>
    <?= $this->include('admin/partials/rbac_styles') ?>
    <?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body class="rbac-page">
<?= $this->include('admin/partials/sidebar') ?>
<div class="rbac-container">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="flash flash-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <div class="rbac-card">
        <div class="rbac-card-header">
            <div>
                <h1 class="rbac-card-title">Role: <?= esc($role['name']) ?></h1>
                <p class="rbac-card-sub"><?= count($permissionNames) ?> permissions · <?= (int) $userCount ?> users</p>
            </div>
        </div>
        <div class="rbac-card-body rbac-padded detail-grid">
            <div class="detail-box">
                <div class="detail-label">Name</div>
                <div class="detail-value"><?= esc($role['name']) ?></div>
                <div class="detail-label" style="margin-top:1rem;">Description</div>
                <div class="detail-value"><?= esc($role['description'] ?? '—') ?></div>
                <div class="detail-label" style="margin-top:1rem;">Level</div>
                <div class="detail-value"><span class="level-badge"><?= (int) ($role['level'] ?? 100) ?></span></div>
            </div>
            <div class="detail-box">
                <div class="detail-label">Assigned Permissions</div>
                <?php if ($permissionNames === []): ?>
                    <p style="color:#6b7280;margin-top:.5rem;">No permissions assigned.</p>
                <?php else: ?>
                    <?php foreach ($permissionNames as $name): ?>
                        <span class="perm-chip"><?= esc($name) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="rbac-card-body rbac-padded" style="border-top:1px solid #eef2f7;">
            <div class="actions">
                <a href="<?= site_url('admin/roles/edit/' . $role['id']) ?>" class="btn btn-primary">Edit Role</a>
                <a href="<?= site_url('admin/roles') ?>" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
