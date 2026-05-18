<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Permissions - Quick Puff Vape Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f8; color: #111827; }
        .container { max-width: 900px; margin: 1.5rem auto; padding: 0 1.25rem; }
        .panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; }
        .panel-top { padding: 1rem 1.15rem; border-bottom: 1px solid #eef2f7; display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; }
        .panel-title { font-size: 1.2rem; font-weight: 700; }
        .panel-subtitle { margin-top: 0.25rem; font-size: 0.85rem; color: #6b7280; }
        .badge { display: inline-flex; border-radius: 999px; padding: 0.12rem 0.5rem; font-size: 0.68rem; font-weight: 700; background: #dbeafe; color: #1e40af; margin-left: 0.35rem; }
        .module { padding: 0.85rem 1.15rem; border-bottom: 1px solid #f3f4f6; }
        .module:last-child { border-bottom: none; }
        .module h2 { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em; color: #6b7280; margin-bottom: 0.5rem; }
        .perm { padding: 0.35rem 0; font-size: 0.88rem; }
        .perm-name { font-weight: 600; font-family: ui-monospace, monospace; font-size: 0.82rem; }
        .perm-desc { color: #6b7280; font-size: 0.8rem; margin-top: 0.1rem; }
        .btn { display: inline-flex; align-items: center; border-radius: 999px; padding: 0.45rem 0.85rem; font-size: 0.8rem; font-weight: 600; text-decoration: none; background: #f3f4f6; border: 1px solid #e5e7eb; color: #374151; }
        .empty { padding: 1.25rem; text-align: center; color: #6b7280; }
    </style>
    <?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
<?= $this->include('admin/partials/sidebar') ?>
<div class="container">
    <section class="panel">
        <div class="panel-top">
            <div>
                <h1 class="panel-title">
                    <?= esc((string) ($role['name'] ?? '')) ?>
                    <?php if (!empty($role['is_system'])): ?><span class="badge">System Role</span><?php endif; ?>
                </h1>
                <p class="panel-subtitle"><?= esc((string) ($role['description'] ?? 'No description')) ?> · <?= esc((string) count($permissions)) ?> permissions</p>
            </div>
            <div style="display:flex;gap:0.4rem;flex-wrap:wrap;">
                <a href="<?= site_url('admin/roles/edit/' . (int) ($role['id'] ?? 0)) ?>" class="btn">Edit Role</a>
                <a href="<?= site_url('admin/roles') ?>" class="btn">Back to Roles</a>
            </div>
        </div>

        <?php if (empty($permissions_grouped)): ?>
            <p class="empty">This role has no permissions assigned.</p>
        <?php else: ?>
            <?php foreach ($permissions_grouped as $module => $modulePermissions): ?>
                <article class="module">
                    <h2><?= esc((string) $module) ?></h2>
                    <?php foreach ($modulePermissions as $permission): ?>
                        <div class="perm">
                            <p class="perm-name"><?= esc((string) ($permission['name'] ?? '')) ?></p>
                            <p class="perm-desc"><?= esc((string) ($permission['description'] ?? '-')) ?></p>
                        </div>
                    <?php endforeach; ?>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>
</body>
</html>
