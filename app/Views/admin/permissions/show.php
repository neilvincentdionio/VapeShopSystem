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
    <div class="rbac-card">
        <div class="rbac-card-header">
            <div>
                <h1 class="rbac-card-title"><?= esc($permission['name']) ?></h1>
                <p class="rbac-card-sub"><?= count($roles) ?> roles assigned</p>
            </div>
        </div>
        <div class="rbac-card-body detail-grid">
            <div class="detail-box">
                <div class="detail-label">Name</div>
                <div class="detail-value"><?= esc($permission['name']) ?></div>
                <div class="detail-label" style="margin-top:1rem;">Description</div>
                <div class="detail-value"><?= esc($permission['description'] ?? '—') ?></div>
            </div>
            <div class="detail-box">
                <div class="detail-label">Assigned Roles</div>
                <?php if ($roles === []): ?>
                    <p style="color:#6b7280;margin-top:.5rem;">Not assigned to any role.</p>
                <?php else: ?>
                    <?php foreach ($roles as $role): ?>
                        <span class="perm-chip"><?= esc($role['name']) ?> (<?= (int) ($role['level'] ?? 100) ?>)</span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="rbac-card-body" style="border-top:1px solid #eef2f7;padding-top:1rem;">
            <div class="actions">
                <a href="<?= site_url('admin/permissions/edit/' . $permission['id']) ?>" class="btn btn-primary">Edit</a>
                <a href="<?= site_url('admin/permissions') ?>" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
