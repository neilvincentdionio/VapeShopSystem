<?php

namespace App\Libraries;

use App\Models\PermissionModel;
use App\Models\RoleModel;

class RbacService
{
    public const SYSTEM_ROLES = ['admin', 'customer', 'rider'];

    private PermissionModel $permissionModel;
    private RoleModel $roleModel;

    public function __construct()
    {
        $this->permissionModel = new PermissionModel();
        $this->roleModel = new RoleModel();
    }

    public function tablesAvailable(): bool
    {
        $db = \Config\Database::connect();

        return $db->tableExists('roles')
            && $db->tableExists('permissions')
            && $db->tableExists('role_permissions');
    }

    public function bootstrap(): bool
    {
        $db = \Config\Database::connect();

        try {
            $this->runMigration();

            $db->query("
                CREATE TABLE IF NOT EXISTS roles (
                    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    name VARCHAR(100) NOT NULL,
                    description VARCHAR(255) NULL,
                    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
                    is_system_role TINYINT(1) NOT NULL DEFAULT 0,
                    created_at DATETIME NULL,
                    updated_at DATETIME NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY uk_roles_name (name)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $db->query("
                CREATE TABLE IF NOT EXISTS permissions (
                    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    name VARCHAR(100) NOT NULL,
                    description VARCHAR(255) NULL,
                    module_name VARCHAR(100) NULL,
                    created_at DATETIME NULL,
                    updated_at DATETIME NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY uk_permissions_name (name)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $db->query("
                CREATE TABLE IF NOT EXISTS role_permissions (
                    role_id INT(11) UNSIGNED NOT NULL,
                    permission_id INT(11) UNSIGNED NOT NULL,
                    created_at DATETIME NULL,
                    PRIMARY KEY (role_id, permission_id),
                    KEY idx_rp_role_id (role_id),
                    KEY idx_rp_permission_id (permission_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            if (!$db->tableExists('user_roles') && $db->tableExists('users') && $db->tableExists('roles')) {
                $db->query("
                    CREATE TABLE user_roles (
                        user_id INT(11) UNSIGNED NOT NULL,
                        role_id INT(11) UNSIGNED NOT NULL,
                        assigned_at DATETIME NULL,
                        assigned_by INT(11) UNSIGNED NULL,
                        PRIMARY KEY (user_id, role_id),
                        KEY idx_user_roles_user_id (user_id),
                        KEY idx_user_roles_role_id (role_id),
                        CONSTRAINT fk_ur_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
                        CONSTRAINT fk_ur_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE ON UPDATE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            } elseif (!$db->tableExists('user_roles')) {
                $db->query("
                    CREATE TABLE IF NOT EXISTS user_roles (
                        user_id INT(11) UNSIGNED NOT NULL,
                        role_id INT(11) UNSIGNED NOT NULL,
                        assigned_at DATETIME NULL,
                        assigned_by INT(11) UNSIGNED NULL,
                        PRIMARY KEY (user_id, role_id),
                        KEY idx_user_roles_user_id (user_id),
                        KEY idx_user_roles_role_id (role_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }

            $this->migrateSchemaColumns();
            $this->syncPermissionsCatalog();
            $this->ensureSystemRoles();
            $this->syncSystemRolePermissions();
            $this->syncUserRoleLinks();

            return $this->tablesAvailable();
        } catch (\Throwable $e) {
            log_message('error', 'RBAC bootstrap failed: {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function runMigration(): void
    {
        if (!class_exists(\App\Database\Migrations\UpgradeRbacSchema::class)) {
            return;
        }

        $migration = new \App\Database\Migrations\UpgradeRbacSchema();
        $migration->up();
    }

    public function migrateSchemaColumns(): void
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('roles')) {
            return;
        }

        if (!$db->fieldExists('status', 'roles')) {
            $db->query("ALTER TABLE roles ADD COLUMN status ENUM('active','inactive') NOT NULL DEFAULT 'active' AFTER description");
        }
        if (!$db->fieldExists('is_system_role', 'roles')) {
            $db->query('ALTER TABLE roles ADD COLUMN is_system_role TINYINT(1) NOT NULL DEFAULT 0 AFTER status');
        }
        if ($db->tableExists('permissions') && !$db->fieldExists('module_name', 'permissions')) {
            $db->query('ALTER TABLE permissions ADD COLUMN module_name VARCHAR(100) NULL AFTER description');
        }
        if ($db->tableExists('users') && !$db->fieldExists('role_id', 'users')) {
            $db->query('ALTER TABLE users ADD COLUMN role_id INT UNSIGNED NULL DEFAULT NULL AFTER `role`');
        }

        foreach (self::SYSTEM_ROLES as $roleName) {
            $payload = ['is_system_role' => 1];
            if ($roleName === 'admin') {
                $payload['status'] = 'active';
            }
            $db->table('roles')->where('name', $roleName)->update($payload);
        }

        if ($db->tableExists('activity_logs')) {
            $db->query('ALTER TABLE activity_logs MODIFY action_type VARCHAR(64) NOT NULL');
        }

        if ($db->tableExists('user_addresses')) {
            if (!$db->fieldExists('delivery_latitude', 'user_addresses')) {
                $db->query('ALTER TABLE user_addresses ADD COLUMN delivery_latitude DECIMAL(10,7) NULL AFTER postal_code');
            }
            if (!$db->fieldExists('delivery_longitude', 'user_addresses')) {
                $db->query('ALTER TABLE user_addresses ADD COLUMN delivery_longitude DECIMAL(10,7) NULL AFTER delivery_latitude');
            }
        }

        if (!$db->tableExists('shop_settings')) {
            $db->query("CREATE TABLE shop_settings (
                id INT UNSIGNED NOT NULL,
                shop_name VARCHAR(150) NOT NULL,
                shop_address TEXT NULL,
                shop_latitude DECIMAL(10,7) NULL,
                shop_longitude DECIMAL(10,7) NULL,
                shop_phone VARCHAR(30) NULL,
                updated_by INT UNSIGNED NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                CONSTRAINT fk_shop_settings_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $now = date('Y-m-d H:i:s');
            $db->table('shop_settings')->insert([
                'id'             => 1,
                'shop_name'      => 'Quick Puff Vape Shop',
                'shop_address'   => 'Bula, General Santos City, South Cotabato, Philippines',
                'shop_latitude'  => 6.1352000,
                'shop_longitude' => 125.2179000,
                'shop_phone'     => null,
                'updated_by'     => null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }

    /**
     * Ensures the admin system role stays active (recovery after accidental deactivation).
     */
    public function ensureAdminRoleActive(): void
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('roles') || !$db->fieldExists('status', 'roles')) {
            return;
        }

        $db->table('roles')
            ->where('name', 'admin')
            ->update([
                'status' => 'active',
                'is_system_role' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * Full recovery: reactivate admin, restore all permissions, relink admin users.
     */
    public function repairAdminAccess(): void
    {
        if (!$this->tablesAvailable()) {
            $this->bootstrap();
        }

        $this->migrateSchemaColumns();
        $this->syncPermissionsCatalog();
        $this->ensureSystemRoles();
        $this->ensureAdminRoleActive();
        $this->syncSystemRolePermissions();
        $this->syncAdminUserRoleIds();
        $this->refreshAdminSessionPermissions();
    }

    private function syncAdminUserRoleIds(): void
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('users') || !$db->fieldExists('role_id', 'users')) {
            return;
        }

        $adminRole = $this->roleModel->findByName('admin');
        if (!is_array($adminRole)) {
            return;
        }

        $roleId = (int) ($adminRole['id'] ?? 0);
        if ($roleId <= 0) {
            return;
        }

        $db->table('users')
            ->where('role', 'admin')
            ->update(['role_id' => $roleId]);
    }

    private function refreshAdminSessionPermissions(): void
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return;
        }

        if (strtolower((string) $session->get('user_role')) !== 'admin') {
            return;
        }

        $userId = (int) ($session->get('user_id') ?? 0);
        if ($userId <= 0) {
            return;
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);
        if (!is_array($user)) {
            return;
        }

        $adminRole = $this->roleModel->findByName('admin');
        if (is_array($adminRole)) {
            $session->set('user_role_id', (int) ($adminRole['id'] ?? 0));
        }

        $session->set('user_permissions', $userModel->getPermissionNamesForUser($userId, $user));
    }

    public function canDeactivateRole(array $role): bool
    {
        return !$this->isSystemRole($role);
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getPermissionCatalogByModule(): array
    {
        return [
            'Product Management' => [
                'view_products' => 'View products',
                'create_products' => 'Create products',
                'update_products' => 'Update products',
                'delete_products' => 'Delete products',
                'products.read' => 'View product list and details',
                'products.write' => 'Create new products',
                'products.update' => 'Update product information',
                'products.delete' => 'Delete products',
            ],
            'Order Management' => [
                'manage_orders' => 'Manage orders',
                'orders.read' => 'View order list and details',
                'orders.write' => 'Create new orders',
                'orders.update' => 'Update order status',
                'orders.delete' => 'Delete orders',
            ],
            'User Management' => [
                'manage_users' => 'Manage user accounts and access',
                'users.read' => 'View user list and details',
                'users.write' => 'Create new users',
                'users.update' => 'Update user information',
                'users.delete' => 'Delete users',
            ],
            'Delivery Management' => [
                'manage_orders' => 'Manage delivery assignments',
            ],
            'Reports' => [
                'reports.read' => 'View reports',
                'reports.write' => 'Generate reports',
                'view_reports' => 'View reports dashboard',
            ],
            'Settings' => [
                'manage_backups' => 'Manage system backups',
                'backup.read' => 'View backup status',
                'backup.write' => 'Create backups',
                'backup.delete' => 'Delete backups',
                'system.read' => 'View system settings',
                'system.update' => 'Update system settings',
                'dashboard.view' => 'View admin dashboard',
                'dashboard.read' => 'View dashboard',
                'view_dashboard' => 'View dashboard',
                'activity_logs.view' => 'View activity logs',
                'activity_logs.manage' => 'Manage activity logs and sessions',
                'audit.read' => 'View audit logs',
                'manage_records' => 'Manage records',
                'view_own_profile' => 'View own profile',
                'read' => 'API read access',
            ],
        ];
    }

    public function syncPermissionsCatalog(): void
    {
        foreach ($this->getPermissionCatalogByModule() as $module => $permissions) {
            foreach ($permissions as $name => $description) {
                $this->permissionModel->ensureExists($name, $description, $module);
            }
        }
    }

    public function ensureSystemRoles(): void
    {
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $definitions = [
            'admin' => 'System administrator',
            'customer' => 'Store customer',
            'rider' => 'Delivery rider',
        ];

        foreach ($definitions as $name => $description) {
            $existing = $db->table('roles')->where('name', $name)->get()->getRowArray();
            if (!is_array($existing)) {
                $db->table('roles')->insert([
                    'name' => $name,
                    'description' => $description,
                    'status' => 'active',
                    'is_system_role' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                continue;
            }

            $update = [
                'description' => $description,
                'is_system_role' => 1,
                'updated_at' => $now,
            ];
            if ($name === 'admin') {
                $update['status'] = 'active';
            }
            $db->table('roles')->where('id', (int) $existing['id'])->update($update);
        }
    }

    /**
     * @return array<string, string[]|string>
     */
    public function getSystemRolePermissionMap(): array
    {
        return [
            'admin' => '__all__',
            'customer' => ['read', 'view_dashboard', 'dashboard.view', 'dashboard.read', 'view_own_profile', 'view_products', 'orders.read'],
            'rider' => ['read', 'view_dashboard', 'dashboard.view', 'orders.read', 'orders.update', 'manage_orders'],
        ];
    }

    public function syncSystemRolePermissions(): void
    {
        $db = \Config\Database::connect();
        $permissions = $this->permissionModel->findAll();
        $byName = [];
        foreach ($permissions as $row) {
            $byName[strtolower((string) ($row['name'] ?? ''))] = (int) ($row['id'] ?? 0);
        }

        $now = date('Y-m-d H:i:s');
        foreach ($this->getSystemRolePermissionMap() as $roleName => $names) {
            $role = $this->roleModel->findByName($roleName);
            if (!is_array($role)) {
                continue;
            }
            $roleId = (int) ($role['id'] ?? 0);
            if ($roleId <= 0) {
                continue;
            }

            if ($names === '__all__') {
                $names = array_keys($byName);
            }

            foreach ($names as $permissionName) {
                $permissionId = (int) ($byName[strtolower($permissionName)] ?? 0);
                if ($permissionId <= 0) {
                    continue;
                }

                $exists = $db->table('role_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $permissionId)
                    ->countAllResults();
                if ($exists === 0) {
                    $db->table('role_permissions')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                        'created_at' => $now,
                    ]);
                }
            }
        }
    }

    public function syncUserRoleLinks(): void
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('users')) {
            return;
        }

        $userFields = 'id, role';
        if ($db->fieldExists('role_id', 'users')) {
            $userFields .= ', role_id';
        }
        $users = $db->table('users')->select($userFields)->get()->getResultArray();
        $now = date('Y-m-d H:i:s');

        foreach ($users as $user) {
            $userId = (int) ($user['id'] ?? 0);
            $roleName = strtolower(trim((string) ($user['role'] ?? '')));
            if ($userId <= 0 || $roleName === '') {
                continue;
            }

            $role = $this->roleModel->findByName($roleName);
            if (!is_array($role)) {
                continue;
            }

            $roleId = (int) ($role['id'] ?? 0);
            if ($roleId <= 0) {
                continue;
            }

            if ($db->fieldExists('role_id', 'users') && (int) ($user['role_id'] ?? 0) !== $roleId) {
                $db->table('users')->where('id', $userId)->update(['role_id' => $roleId]);
            }

            if ($db->tableExists('user_roles')) {
                $link = $db->table('user_roles')->where('user_id', $userId)->where('role_id', $roleId)->get()->getRowArray();
                if (!is_array($link)) {
                    $db->table('user_roles')->insert([
                        'user_id' => $userId,
                        'role_id' => $roleId,
                        'assigned_at' => $now,
                    ]);
                }
            }
        }
    }

    public function isSystemRole(array $role): bool
    {
        if ((int) ($role['is_system_role'] ?? 0) === 1) {
            return true;
        }

        return in_array(strtolower((string) ($role['name'] ?? '')), self::SYSTEM_ROLES, true);
    }

    public function isRoleActive(array $role): bool
    {
        return strtolower((string) ($role['status'] ?? 'active')) === 'active';
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getPermissionsGrouped(): array
    {
        $rows = $this->permissionModel->orderBy('module_name', 'ASC')->orderBy('name', 'ASC')->findAll();
        $grouped = [];
        foreach ($rows as $row) {
            $module = trim((string) ($row['module_name'] ?? ''));
            if ($module === '') {
                $module = 'General';
            }
            $grouped[$module][] = $row;
        }

        return $grouped;
    }

    public function logAudit(int $userId, string $action, int $resourceId, array $details = []): void
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('audit_logs')) {
            return;
        }

        $request = service('request');
        $userAgent = $request->getUserAgent();

        $db->table('audit_logs')->insert([
            'user_id' => $userId > 0 ? $userId : null,
            'action' => $action,
            'resource_type' => 'role',
            'resource_id' => $resourceId,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $userAgent ? $userAgent->getAgentString() : null,
            'details' => json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status' => 'success',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
