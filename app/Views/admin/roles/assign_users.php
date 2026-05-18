<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Users - Quick Puff Vape Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f8; color: #111827; }
        .container { max-width: 900px; margin: 1.5rem auto; padding: 0 1.25rem; }
        .panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; }
        .panel-top { padding: 1rem 1.15rem; border-bottom: 1px solid #eef2f7; }
        .panel-title { font-size: 1.2rem; font-weight: 700; }
        .panel-subtitle { margin-top: 0.25rem; font-size: 0.85rem; color: #6b7280; }
        .form-body { padding: 1rem 1.15rem 1.15rem; }
        .users-box { border: 1px solid #d1d5db; border-radius: 12px; max-height: 420px; overflow-y: auto; }
        .user-item { padding: 0.7rem 0.85rem; border-bottom: 1px solid #e5e7eb; display: flex; gap: 0.55rem; align-items: flex-start; }
        .user-item:last-child { border-bottom: none; }
        .user-meta { font-size: 0.8rem; color: #6b7280; display: block; margin-top: 0.1rem; }
        .actions { display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1rem; }
        .btn { border-radius: 999px; padding: 0.5rem 1rem; font-size: 0.88rem; font-weight: 600; text-decoration: none; cursor: pointer; border: 1px solid transparent; }
        .btn-primary { background: #1f9d55; color: #fff; }
        .btn-secondary { background: #f3f4f6; border-color: #d1d5db; color: #4b5563; }
        .alert { margin-bottom: 0.85rem; border-radius: 10px; padding: 0.75rem 0.95rem; font-size: 0.88rem; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    </style>
    <?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
<?= $this->include('admin/partials/sidebar') ?>
<div class="container">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <section class="panel">
        <div class="panel-top">
            <h1 class="panel-title">Assign Users</h1>
            <p class="panel-subtitle">Role: <strong><?= esc((string) ($role['name'] ?? '')) ?></strong></p>
        </div>

        <form class="form-body" method="post" action="<?= site_url('admin/roles/assign-users/' . (int) ($role['id'] ?? 0)) ?>">
            <?= csrf_field() ?>
            <div class="users-box">
                <?php if (empty($users)): ?>
                    <p class="user-item">No users found.</p>
                <?php else: ?>
                    <?php
                    $assignedSet = [];
                    foreach ($assigned_user_ids ?? [] as $uid) {
                        $assignedSet[(int) $uid] = true;
                    }
                    ?>
                    <?php foreach ($users as $user): ?>
                        <?php $userId = (int) ($user['id'] ?? 0); ?>
                        <label class="user-item" for="assign_user_<?= $userId ?>">
                            <input id="assign_user_<?= $userId ?>" type="checkbox" name="user_ids[]" value="<?= $userId ?>" <?= isset($assignedSet[$userId]) ? 'checked' : '' ?>>
                            <span>
                                <strong><?= esc((string) ($user['name'] ?? '')) ?></strong>
                                <span class="user-meta"><?= esc((string) ($user['email'] ?? '')) ?> · current: <?= esc((string) ($user['role'] ?? '-')) ?></span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="actions">
                <button type="submit" class="btn btn-primary">Save Assignments</button>
                <a href="<?= site_url('admin/roles') ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
</div>
</body>
</html>
