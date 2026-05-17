<?php
$isEdit = is_array($role);
$action = $isEdit
    ? site_url('admin/roles/update/' . $role['id'])
    : site_url('admin/roles/store');
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
            <h1 class="rbac-card-title"><?= $isEdit ? 'Edit Role' : 'Create New Role' ?></h1>
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
                    <label for="name">Role Name</label>
                    <input type="text" id="name" name="name" required maxlength="80"
                           placeholder="e.g. cashier, Shop Manager, front-desk"
                           value="<?= esc(old('name', $role['name'] ?? '')) ?>">
                    <p class="form-hint">Any letters and numbers are fine. Spaces become underscores (Shop Manager → shop_manager).</p>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"><?= esc(old('description', $role['description'] ?? '')) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="level">Level (higher = more privileges)</label>
                    <input type="number" id="level" name="level" min="1" max="999" required value="<?= esc(old('level', (string) ($role['level'] ?? 100))) ?>">
                </div>
                <div class="form-group">
                    <label>Permissions</label>
                    <div class="permission-list">
                        <?php foreach ($permissions as $permission): ?>
                            <?php $checked = in_array((int) $permission['id'], $assignedPermissionIds, true); ?>
                            <label class="permission-item">
                                <input type="checkbox" name="permission_ids[]" value="<?= (int) $permission['id'] ?>" <?= $checked ? 'checked' : '' ?>>
                                <div>
                                    <strong><?= esc($permission['name']) ?></strong>
                                    <span><?= esc($permission['description'] ?? '') ?></span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="actions">
                    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update Role' : 'Create Role' ?></button>
                    <a href="<?= site_url('admin/roles') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
