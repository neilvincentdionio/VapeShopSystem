<?php
$isEdit = is_array($permission);
$action = $isEdit
    ? site_url('admin/permissions/update/' . $permission['id'])
    : site_url('admin/permissions/store');
?>
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
    <div class="rbac-card rbac-form-card" style="max-width:760px;">
        <div class="rbac-card-header">
            <h1 class="rbac-card-title"><?= $isEdit ? 'Edit Permission' : 'Create New Permission' ?></h1>
        </div>
        <div class="rbac-card-body">
            <?php if (session('errors')): ?>
                <div class="errors-list">
                    <?php foreach (session('errors') as $error): ?>
                        <div><?= esc($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= $action ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="name">Permission Name (format: resource.action)</label>
                    <input type="text" id="name" name="name" required placeholder="e.g. products.view"
                           value="<?= esc(old('name', $permission['name'] ?? '')) ?>">
                    <p class="form-hint">Use lowercase letters, numbers, dots, and underscores only.</p>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"><?= esc(old('description', $permission['description'] ?? '')) ?></textarea>
                </div>
                <div class="form-group">
                    <label>Assign to Roles</label>
                    <div class="permission-list">
                        <?php foreach ($roles as $role): ?>
                            <?php $checked = in_array((int) $role['id'], $assignedRoleIds, true); ?>
                            <label class="permission-item">
                                <input type="checkbox" name="role_ids[]" value="<?= (int) $role['id'] ?>" <?= $checked ? 'checked' : '' ?>>
                                <div>
                                    <strong><?= esc($role['name']) ?></strong>
                                    <span>Level <?= (int) ($role['level'] ?? 100) ?> — <?= esc($role['description'] ?? '') ?></span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="actions">
                    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update Permission' : 'Create Permission' ?></button>
                    <a href="<?= site_url('admin/permissions') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
