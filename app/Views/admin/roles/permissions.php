<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Permissions - Quick Puff Vape Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f8; color: #111827; }
        .container { max-width: 1080px; margin: 1.5rem auto; padding: 0 1.25rem; }
        .panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; }
        .panel-top { padding: 1.1rem 1.25rem; border-bottom: 1px solid #eef2f7; display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; }
        .panel-title { font-size: 1.45rem; font-weight: 700; display: flex; align-items: center; flex-wrap: wrap; gap: .45rem; }
        .panel-subtitle { margin-top: 0.35rem; font-size: 0.95rem; color: #4b5563; }
        .badge { display: inline-flex; border-radius: 999px; padding: 0.2rem 0.62rem; font-size: 0.74rem; font-weight: 700; background: #dbeafe; color: #1e40af; }
        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: .75rem; padding: 1rem 1.25rem; border-bottom: 1px solid #eef2f7; background: #fafbfc; }
        .stat-card { border: 1px solid #e5e7eb; border-radius: 10px; background: #fff; padding: .75rem .85rem; }
        .stat-label { font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; margin-bottom: .25rem; }
        .stat-value { font-size: 1.2rem; font-weight: 700; color: #111827; }
        .modules-wrap { padding: .95rem 1.25rem 1.25rem; display: grid; gap: .9rem; }
        .module { border: 1px solid #e8edf3; border-radius: 12px; overflow: hidden; background: #fff; }
        .module-head { padding: .7rem .95rem; display: flex; align-items: center; justify-content: space-between; gap: .75rem; background: #f8fafc; border-bottom: 1px solid #eef2f7; }
        .module h2 { font-size: 0.92rem; text-transform: uppercase; letter-spacing: 0.04em; color: #334155; margin: 0; }
        .module-count { font-size: .78rem; color: #475569; font-weight: 600; background: #e2e8f0; border-radius: 999px; padding: .16rem .55rem; }
        .module-body { padding: .2rem .95rem .45rem; }
        .perm { padding: 0.55rem 0; border-bottom: 1px dashed #edf2f7; }
        .perm:last-child { border-bottom: none; }
        .perm-name { font-weight: 700; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size: 0.95rem; color: #0f172a; }
        .perm-desc { color: #4b5563; font-size: 0.88rem; margin-top: 0.18rem; line-height: 1.35; }
        .btn { display: inline-flex; align-items: center; border-radius: 999px; padding: 0.5rem 0.95rem; font-size: 0.88rem; font-weight: 600; text-decoration: none; background: #f3f4f6; border: 1px solid #e5e7eb; color: #374151; }
        .btn:hover { background: #e5e7eb; }
        .btn-primary { background: #10b981; border-color: #10b981; color: #fff; }
        .btn-primary:hover { background: #059669; border-color: #059669; }
        .empty { padding: 1.25rem; text-align: center; color: #6b7280; }
        @media (max-width: 768px) {
            .container { padding: 0 .85rem; }
            .panel-title { font-size: 1.2rem; }
        }
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
                <a href="<?= site_url('admin/roles/edit/' . (int) ($role['id'] ?? 0)) ?>" class="btn btn-primary">Edit Role</a>
                <a href="<?= site_url('admin/roles') ?>" class="btn">Back to Roles</a>
            </div>
        </div>
        <div class="stats-row">
            <div class="stat-card">
                <p class="stat-label">Assigned Users</p>
                <p class="stat-value"><?= esc((string) ((int) ($role['user_count'] ?? 0))) ?></p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Total Permissions</p>
                <p class="stat-value"><?= esc((string) count($permissions)) ?></p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Modules Covered</p>
                <p class="stat-value"><?= esc((string) count($permissions_grouped ?? [])) ?></p>
            </div>
        </div>

        <?php if (empty($permissions_grouped)): ?>
            <p class="empty">This role has no permissions assigned.</p>
        <?php else: ?>
            <div class="modules-wrap">
            <?php foreach ($permissions_grouped as $module => $modulePermissions): ?>
                <article class="module">
                    <div class="module-head">
                        <h2><?= esc((string) $module) ?></h2>
                        <span class="module-count"><?= esc((string) count($modulePermissions)) ?> permission<?= count($modulePermissions) === 1 ? '' : 's' ?></span>
                    </div>
                    <div class="module-body">
                        <?php foreach ($modulePermissions as $permission): ?>
                            <div class="perm">
                                <p class="perm-name"><?= esc((string) ($permission['name'] ?? '')) ?></p>
                                <p class="perm-desc"><?= esc((string) ($permission['description'] ?? '-')) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
</body>
</html>
