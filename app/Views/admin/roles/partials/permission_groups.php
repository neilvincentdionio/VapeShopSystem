<?php
$permissionsGrouped = $permissions_grouped ?? [];
$selectedPermissionSet = $selectedPermissionSet ?? [];
?>
<div class="permissions-grouped">
    <?php if ($permissionsGrouped === []): ?>
        <p class="permission-empty">No permissions found.</p>
    <?php else: ?>
        <?php foreach ($permissionsGrouped as $module => $modulePermissions): ?>
            <div class="permission-module">
                <h3 class="permission-module-title"><?= esc((string) $module) ?></h3>
                <div class="permissions-box">
                    <?php foreach ($modulePermissions as $permission): ?>
                        <?php $permissionId = (int) ($permission['id'] ?? 0); ?>
                        <label class="permission-item" for="permission_<?= $permissionId ?>">
                            <input
                                id="permission_<?= $permissionId ?>"
                                type="checkbox"
                                name="permissions[]"
                                value="<?= $permissionId ?>"
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
