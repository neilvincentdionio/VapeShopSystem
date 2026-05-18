<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Permission - Quick Puff Vape Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f8; color: #111827; }
        .container { max-width: 1080px; margin: 1.6rem auto; padding: 0 1.25rem; }
        .panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; }
        .panel-header { padding: 1.05rem 1.25rem; border-bottom: 1px solid #eef2f7; }
        .panel-header h1 { font-size: 1.45rem; font-weight: 700; }
        .panel-body { padding: 1.15rem 1.25rem 1.25rem; }
        .meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.9rem 1.2rem; margin-bottom: 1rem; }
        .meta-item { border: 1px solid #e8edf3; border-radius: 10px; padding: 0.78rem 0.95rem; background: #fff; }
        .meta-label { font-size: 0.76rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.28rem; }
        .meta-value { font-size: 1.02rem; color: #111827; font-weight: 700; }
        .perm-name { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.98rem; }
        .roles-title { font-size: 0.9rem; color: #475569; margin-bottom: 0.55rem; text-transform: uppercase; letter-spacing: 0.04em; font-weight: 700; }
        .roles-list { border: 1px solid #e8edf3; border-radius: 10px; overflow: hidden; background: #fff; }
        .role-item { padding: 0.76rem 0.95rem; border-bottom: 1px solid #eef2f7; }
        .role-item:last-child { border-bottom: none; }
        .role-name { font-size: 0.98rem; font-weight: 700; color: #111827; }
        .role-description { font-size: 0.88rem; color: #4b5563; margin-top: 0.16rem; line-height: 1.35; }
        .role-chip { display: inline-flex; align-items: center; justify-content: center; min-width: 2rem; padding: .2rem .5rem; border-radius: 999px; font-size: .82rem; font-weight: 700; background: #dbeafe; color: #1e40af; }
        .actions { margin-top: 1rem; display: flex; justify-content: flex-end; gap: 0.55rem; }
        .btn { border: 1px solid transparent; border-radius: 999px; padding: 0.5rem 1rem; font-size: 0.88rem; font-weight: 600; text-decoration: none; }
        .btn-primary { background: #1f9d55; border-color: #1f9d55; color: #fff; }
        .btn-secondary { background: #f3f4f6; border-color: #d1d5db; color: #4b5563; }
        .btn-secondary:hover { background: #e5e7eb; }
        .btn-primary:hover { background: #177f45; border-color: #177f45; }
        @media (max-width: 768px) {
            .container { padding: 0 .85rem; }
            .meta { grid-template-columns: 1fr; }
            .panel-header h1 { font-size: 1.2rem; }
            .actions { justify-content: flex-start; }
        }
    </style>
    <?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
<?= $this->include('admin/partials/sidebar') ?>
<div class="container">
    <section class="panel">
        <header class="panel-header">
            <h1>Permission Details</h1>
        </header>
        <div class="panel-body">
            <div class="meta">
                <div class="meta-item">
                    <p class="meta-label">Permission Name</p>
                    <p class="meta-value perm-name"><?= esc((string) ($permission['name'] ?? '-')) ?></p>
                </div>
                <div class="meta-item">
                    <p class="meta-label">Assigned Roles</p>
                    <p class="meta-value"><span class="role-chip"><?= esc((string) count($roles)) ?></span></p>
                </div>
                <div class="meta-item" style="grid-column: 1 / -1;">
                    <p class="meta-label">Description</p>
                    <p class="meta-value"><?= esc((string) ($permission['description'] ?? '-')) ?></p>
                </div>
            </div>

            <p class="roles-title">Roles with this permission</p>
            <div class="roles-list">
                <?php if (empty($roles)): ?>
                    <div class="role-item">
                        <p class="role-description">No roles are assigned to this permission yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($roles as $role): ?>
                        <div class="role-item">
                            <p class="role-name"><?= esc((string) ($role['name'] ?? '')) ?></p>
                            <p class="role-description"><?= esc((string) ($role['description'] ?? 'No description')) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="actions">
                <a href="<?= site_url('admin/permissions/edit/' . (int) ($permission['id'] ?? 0)) ?>" class="btn btn-primary">Edit Permission</a>
                <a href="<?= site_url('admin/permissions') ?>" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </section>
</div>
</body>
</html>
