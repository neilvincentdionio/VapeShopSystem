<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Role - Quick Puff Vape Shop</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f3f4f8;
            color: #1f2937;
        }

        .container {
            max-width: 1100px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .panel {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .panel-header {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .panel-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #111827;
        }

        .form-body {
            padding: 1.4rem 1.5rem 1.6rem;
        }

        .form-group {
            margin-bottom: 1.1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.45rem;
            font-size: 0.95rem;
            color: #111827;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 0.65rem 0.8rem;
            font-size: 0.95rem;
            color: #111827;
            background: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.16);
            outline: none;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 84px;
        }

        .help-text {
            margin-top: 0.4rem;
            font-size: 0.82rem;
            color: #6b7280;
        }

        .permissions-grouped { display: flex; flex-direction: column; gap: 0.85rem; }
        .permission-module-title { font-size: 0.82rem; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.35rem; }
        .permissions-box {
            border: 1px solid #d1d5db;
            border-radius: 12px;
            max-height: 220px;
            overflow-y: auto;
            background: #ffffff;
        }
        .users-box { border: 1px solid #d1d5db; border-radius: 12px; max-height: 200px; overflow-y: auto; background: #fff; }
        .user-item { padding: 0.65rem 0.8rem; border-bottom: 1px solid #e5e7eb; display: flex; gap: 0.55rem; align-items: center; }
        .user-item:last-child { border-bottom: none; }
        .user-meta { font-size: 0.82rem; color: #6b7280; }

        .permission-item {
            padding: 0.7rem 0.8rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
        }

        .permission-item:last-child {
            border-bottom: none;
        }

        .permission-item input[type="checkbox"] {
            margin-top: 0.2rem;
        }

        .permission-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: #111827;
        }

        .permission-description {
            margin-top: 0.15rem;
            font-size: 0.86rem;
            color: #6b7280;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.6rem;
            margin-top: 1.35rem;
        }

        .btn {
            border: 1px solid transparent;
            border-radius: 999px;
            padding: 0.55rem 1.2rem;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: #1f9d55;
            border-color: #1f9d55;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #178347;
            border-color: #178347;
        }

        .btn-secondary {
            background: #f3f4f6;
            border-color: #d1d5db;
            color: #4b5563;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .alert {
            margin-bottom: 1rem;
            border-radius: 10px;
            padding: 0.85rem 1rem;
            font-size: 0.92rem;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .validation-list {
            margin-top: 0.4rem;
            margin-left: 1rem;
        }

        .validation-list li {
            margin: 0.2rem 0;
        }
    </style>
    <?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
<?= $this->include('admin/partials/sidebar') ?>
<div class="container">
    <?php
    $errors = session()->getFlashdata('errors');
    $selectedPermissions = old('permissions', []);
    if (!is_array($selectedPermissions)) {
        $selectedPermissions = [];
    }
    $selectedPermissionSet = [];
    foreach ($selectedPermissions as $permissionId) {
        $selectedPermissionSet[(int) $permissionId] = true;
    }
    $selectedUsers = old('user_ids', []);
    if (!is_array($selectedUsers)) {
        $selectedUsers = [];
    }
    $selectedUserSet = [];
    foreach ($selectedUsers as $userId) {
        $selectedUserSet[(int) $userId] = true;
    }
    ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?php if (is_array($errors) && $errors !== []): ?>
        <div class="alert alert-error">
            <strong>Please fix the following:</strong>
            <ul class="validation-list">
                <?php foreach ($errors as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="panel">
        <header class="panel-header">
            <h1>Create New Role</h1>
        </header>

        <form class="form-body" action="<?= site_url('admin/roles/store') ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name">Role Name</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    class="form-control"
                    value="<?= esc((string) old('name')) ?>"
                    placeholder="e.g. inventory_manager"
                    required
                >
                <p class="help-text">Letters, numbers, spaces, dots, underscores, and hyphens are allowed.</p>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea
                    id="description"
                    name="description"
                    class="form-control"
                    placeholder="Describe what this role can do..."
                ><?= esc((string) old('description')) ?></textarea>
            </div>

            <div class="form-group">
                <label>Permissions</label>
                <?= view('admin/roles/partials/permission_groups', [
                    'permissions_grouped' => $permissions_grouped ?? [],
                    'selectedPermissionSet' => $selectedPermissionSet,
                ]) ?>
            </div>

            <div class="form-group">
                <label>Assign Users (optional)</label>
                <p class="help-text">Select users to assign this role immediately after creation.</p>
                <div class="users-box">
                    <?php if (empty($users)): ?>
                        <div class="user-item"><span class="user-meta">No users available.</span></div>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <?php $userId = (int) ($user['id'] ?? 0); ?>
                            <label class="user-item" for="user_<?= $userId ?>">
                                <input id="user_<?= $userId ?>" type="checkbox" name="user_ids[]" value="<?= $userId ?>" <?= isset($selectedUserSet[$userId]) ? 'checked' : '' ?>>
                                <span>
                                    <strong><?= esc((string) ($user['name'] ?? '')) ?></strong>
                                    <span class="user-meta"><?= esc((string) ($user['email'] ?? '')) ?> · <?= esc((string) ($user['role'] ?? '')) ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Create Role</button>
                <a href="<?= site_url('admin/roles') ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
</div>
</body>
</html>
