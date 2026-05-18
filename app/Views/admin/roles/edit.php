<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Role - Quick Puff Vape Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f8; color: #1f2937; }
        .container { max-width: 1100px; margin: 1.5rem auto; padding: 0 1.25rem; }
        .panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; }
        .panel-header { padding: 1rem 1.15rem; border-bottom: 1px solid #e5e7eb; }
        .panel-header h1 { font-size: 1.5rem; font-weight: 700; color: #111827; }
        .badge { display: inline-flex; margin-left: 0.4rem; border-radius: 999px; padding: 0.12rem 0.5rem; font-size: 0.68rem; font-weight: 700; background: #dbeafe; color: #1e40af; vertical-align: middle; }
        .form-body { padding: 1rem 1.15rem 1.15rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.4rem; font-size: 0.92rem; font-weight: 600; }
        .form-control { width: 100%; border: 1px solid #d1d5db; border-radius: 10px; padding: 0.6rem 0.75rem; font-size: 0.92rem; }
        .form-control:disabled { background: #f3f4f6; color: #6b7280; }
        textarea.form-control { resize: vertical; min-height: 84px; }
        .permissions-grouped { display: flex; flex-direction: column; gap: 0.85rem; }
        .permission-module-title { font-size: 0.82rem; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.35rem; }
        .permissions-box { border: 1px solid #d1d5db; border-radius: 12px; max-height: 220px; overflow-y: auto; }
        .permission-item { padding: 0.65rem 0.75rem; border-bottom: 1px solid #e5e7eb; display: flex; gap: 0.55rem; align-items: flex-start; }
        .permission-item:last-child { border-bottom: none; }
        .permission-name { font-size: 0.9rem; font-weight: 600; }
        .permission-description { font-size: 0.82rem; color: #6b7280; margin-top: 0.1rem; }
        .actions { display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1rem; }
        .btn { border-radius: 999px; padding: 0.5rem 1rem; font-size: 0.9rem; font-weight: 600; text-decoration: none; cursor: pointer; border: 1px solid transparent; }
        .btn-primary { background: #1f9d55; color: #fff; }
        .btn-secondary { background: #f3f4f6; border-color: #d1d5db; color: #4b5563; }
        .alert-error { margin-bottom: 0.85rem; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; border-radius: 10px; padding: 0.75rem 0.95rem; }
        .help-text { font-size: 0.8rem; color: #6b7280; margin-top: 0.25rem; }
    </style>
    <?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
<?= $this->include('admin/partials/sidebar') ?>
<div class="container">
    <?php
    $errors = session()->getFlashdata('errors');
    $selectedPermissions = old('permissions', $selected_permission_ids ?? []);
    if (!is_array($selectedPermissions)) {
        $selectedPermissions = [];
    }
    $selectedPermissionSet = [];
    foreach ($selectedPermissions as $permissionId) {
        $selectedPermissionSet[(int) $permissionId] = true;
    }
    ?>

    <?php if (is_array($errors) && $errors !== []): ?>
        <div class="alert-error">
            <strong>Please fix the following:</strong>
            <ul style="margin:0.4rem 0 0 1rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="panel">
        <header class="panel-header">
            <h1>
                Edit Role
                <?php if (!empty($is_system)): ?><span class="badge">System Role</span><?php endif; ?>
            </h1>
        </header>

        <form class="form-body" action="<?= site_url('admin/roles/update/' . (int) ($role['id'] ?? 0)) ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="name">Role Name</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    class="form-control"
                    value="<?= esc((string) old('name', (string) ($role['name'] ?? ''))) ?>"
                    <?= !empty($is_system) ? 'disabled' : 'required' ?>
                >
                <?php if (!empty($is_system)): ?>
                    <p class="help-text">System role names cannot be changed.</p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control"><?= esc((string) old('description', (string) ($role['description'] ?? ''))) ?></textarea>
            </div>

            <div class="form-group">
                <label>Permissions</label>
                <?= view('admin/roles/partials/permission_groups', [
                    'permissions_grouped' => $permissions_grouped ?? [],
                    'selectedPermissionSet' => $selectedPermissionSet,
                ]) ?>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?= site_url('admin/roles') ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
</div>
</body>
</html>
