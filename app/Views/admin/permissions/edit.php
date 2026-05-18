<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Permission - Quick Puff Vape Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f8; color: #1f2937; }
        .container { max-width: 900px; margin: 2rem auto; padding: 0 1.5rem; }
        .panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 18px; overflow: hidden; }
        .panel-header { padding: 1.2rem 1.5rem; border-bottom: 1px solid #e5e7eb; }
        .panel-header h1 { font-size: 1.7rem; font-weight: 700; color: #111827; }
        .form-body { padding: 1.4rem 1.5rem 1.6rem; }
        .form-group { margin-bottom: 1.1rem; }
        .form-group label { display: block; margin-bottom: 0.45rem; font-size: 0.95rem; color: #111827; font-weight: 600; }
        .form-control { width: 100%; border: 1px solid #d1d5db; border-radius: 10px; padding: 0.65rem 0.8rem; font-size: 0.95rem; }
        .help-text { margin-top: 0.4rem; font-size: 0.82rem; color: #6b7280; }
        .actions { display: flex; justify-content: flex-end; gap: 0.6rem; margin-top: 1.35rem; }
        .btn { border: 1px solid transparent; border-radius: 999px; padding: 0.55rem 1.2rem; font-size: 0.95rem; font-weight: 600; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #1f9d55; border-color: #1f9d55; color: #fff; }
        .btn-secondary { background: #f3f4f6; border-color: #d1d5db; color: #4b5563; }
        .alert { margin-bottom: 1rem; border-radius: 10px; padding: 0.85rem 1rem; font-size: 0.92rem; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .validation-list { margin-top: 0.4rem; margin-left: 1rem; }
    </style>
    <?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
<?= $this->include('admin/partials/sidebar') ?>
<div class="container">
    <?php $errors = session()->getFlashdata('errors'); ?>
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
            <h1>Edit Permission</h1>
        </header>
        <form class="form-body" action="<?= site_url('admin/permissions/update/' . (int) ($permission['id'] ?? 0)) ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="name">Permission Name</label>
                <input id="name" type="text" name="name" class="form-control" value="<?= esc((string) old('name', (string) ($permission['name'] ?? ''))) ?>" required>
                <p class="help-text">Use lowercase letters, numbers, dots, underscores, or hyphens.</p>
            </div>
            <div class="form-group">
                <label for="module_name">Module</label>
                <select id="module_name" name="module_name" class="form-control">
                    <option value="">General</option>
                    <?php foreach (($modules ?? []) as $module): ?>
                        <?php $currentModule = (string) old('module_name', (string) ($permission['module_name'] ?? '')); ?>
                        <option value="<?= esc((string) $module) ?>" <?= $currentModule === (string) $module ? 'selected' : '' ?>><?= esc((string) $module) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control"><?= esc((string) old('description', (string) ($permission['description'] ?? ''))) ?></textarea>
            </div>
            <div class="actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?= site_url('admin/permissions') ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
</div>
</body>
</html>
