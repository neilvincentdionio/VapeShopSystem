<?php
$permissionsGrouped = $permissions_grouped ?? [];
$selectedPermissionSet = $selectedPermissionSet ?? [];
?>
<style>
    .permission-module-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.35rem;
    }

    .permission-module-select-all {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.78rem;
        color: #4b5563;
        font-weight: 600;
        user-select: none;
    }

    .permission-module-select-all input[type="checkbox"] {
        margin: 0;
    }
</style>
<div class="permissions-grouped">
    <?php if ($permissionsGrouped === []): ?>
        <p class="permission-empty">No permissions found.</p>
    <?php else: ?>
        <?php foreach ($permissionsGrouped as $module => $modulePermissions): ?>
            <?php
            $moduleKey = strtolower((string) $module);
            $moduleKey = preg_replace('/[^a-z0-9]+/i', '-', $moduleKey) ?? '';
            $moduleKey = trim($moduleKey, '-');
            if ($moduleKey === '') {
                $moduleKey = 'module-' . md5((string) $module);
            }
            ?>
            <div class="permission-module">
                <div class="permission-module-header">
                    <h3 class="permission-module-title"><?= esc((string) $module) ?></h3>
                    <label class="permission-module-select-all" for="select_all_<?= esc($moduleKey) ?>">
                        <input
                            id="select_all_<?= esc($moduleKey) ?>"
                            type="checkbox"
                            data-select-all-module="<?= esc($moduleKey) ?>"
                        >
                        Select All
                    </label>
                </div>
                <div class="permissions-box">
                    <?php foreach ($modulePermissions as $permission): ?>
                        <?php $permissionId = (int) ($permission['id'] ?? 0); ?>
                        <label class="permission-item" for="permission_<?= $permissionId ?>">
                            <input
                                id="permission_<?= $permissionId ?>"
                                type="checkbox"
                                name="permissions[]"
                                value="<?= $permissionId ?>"
                                data-module-permission="<?= esc($moduleKey) ?>"
                                <?= isset($selectedPermissionSet[$permissionId]) ? 'checked' : '' ?>
                            >
                            <span>
                                <span class="permission-name"><?= esc((string) ($permission['name'] ?? '')) ?></span>
                                <span class="permission-description"><?= esc((string) ($permission['description'] ?? 'No description')) ?></span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<script>
(() => {
    if (window.__permission_module_select_all_bound__) {
        return;
    }
    window.__permission_module_select_all_bound__ = true;

    const modulePermissionInputs = () => Array.from(document.querySelectorAll('input[data-module-permission]'));
    const moduleSelectAllInputs = () => Array.from(document.querySelectorAll('input[data-select-all-module]'));

    const syncModuleToggle = (moduleKey) => {
        const selectAll = document.querySelector(`input[data-select-all-module="${moduleKey}"]`);
        const items = Array.from(document.querySelectorAll(`input[data-module-permission="${moduleKey}"]`));
        if (!selectAll || items.length === 0) {
            return;
        }
        const checkedCount = items.filter((item) => item.checked).length;
        selectAll.checked = checkedCount > 0 && checkedCount === items.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < items.length;
    };

    const syncAllModules = () => {
        moduleSelectAllInputs().forEach((toggle) => {
            const moduleKey = String(toggle.dataset.selectAllModule || '').trim();
            if (moduleKey !== '') {
                syncModuleToggle(moduleKey);
            }
        });
    };

    document.addEventListener('change', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLInputElement)) {
            return;
        }

        if (target.matches('input[data-select-all-module]')) {
            const moduleKey = String(target.dataset.selectAllModule || '').trim();
            if (moduleKey === '') {
                return;
            }
            const items = Array.from(document.querySelectorAll(`input[data-module-permission="${moduleKey}"]`));
            items.forEach((item) => {
                item.checked = target.checked;
            });
            syncModuleToggle(moduleKey);
            return;
        }

        if (target.matches('input[data-module-permission]')) {
            const moduleKey = String(target.dataset.modulePermission || '').trim();
            if (moduleKey !== '') {
                syncModuleToggle(moduleKey);
            }
        }
    });

    syncAllModules();
})();
</script>
