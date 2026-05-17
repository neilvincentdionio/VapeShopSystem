<?php

namespace App\Database\Seeds;

use App\Models\PermissionModel;
use App\Models\RoleModel;
use App\Models\RolePermissionModel;
use App\Models\UserModel;
use CodeIgniter\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $roleModel = new RoleModel();
        $permissionModel = new PermissionModel();
        $rolePermissionModel = new RolePermissionModel();
        $userModel = new UserModel();

        $roles = [
            'admin' => ['description' => 'Shop administrator', 'level' => 300],
            'staff' => ['description' => 'Staff member', 'level' => 200],
            'rider' => ['description' => 'Delivery rider', 'level' => 150],
            'customer' => ['description' => 'Customer account', 'level' => 100],
        ];

        foreach ($roles as $name => $meta) {
            $roleModel->createIfNotExists($name, $meta['description'], $meta['level']);
        }

        $permissions = [
            'dashboard.view' => 'View admin dashboard',
            'manage_users' => 'Manage user accounts',
            'view_products' => 'View products',
            'create_products' => 'Create products',
            'update_products' => 'Update products',
            'delete_products' => 'Delete products',
            'manage_orders' => 'Manage orders',
            'manage_records' => 'Manage records',
            'manage_backups' => 'Manage database backups',
            'activity_logs.view' => 'View activity logs',
            'activity_logs.manage' => 'Manage activity logs and sessions',
            'roles.manage' => 'Manage roles',
            'permissions.manage' => 'Manage permissions',
            'read' => 'API read access',
        ];

        foreach ($permissions as $name => $description) {
            $permissionModel->createIfNotExists($name, $description);
        }

        $allPermissionIds = [];
        foreach ($permissionModel->findAll() as $permission) {
            $allPermissionIds[] = (int) $permission['id'];
        }

        $rolePermissionModel->syncPermissions('admin', $allPermissionIds);

        $rolePermissionModel->syncPermissions('staff', $permissionModel->idsByNames([
            'dashboard.view',
            'view_products',
            'create_products',
            'update_products',
            'manage_orders',
            'manage_records',
            'activity_logs.view',
        ]));

        $rolePermissionModel->syncPermissions('rider', $permissionModel->idsByNames([
            'dashboard.view',
            'manage_orders',
            'read',
        ]));

        $rolePermissionModel->syncPermissions('customer', $permissionModel->idsByNames([
            'dashboard.view',
            'read',
        ]));

        foreach ($userModel->findAll() as $user) {
            $roleName = strtolower(trim((string) ($user['role'] ?? 'customer')));
            if ($roleName === '') {
                $roleName = 'customer';
            }

            $roleId = $roleModel->getIdByName($roleName);
            if ($roleId === null) {
                continue;
            }

            $userModel->update((int) $user['id'], [
                'role' => $roleName,
                'role_id' => $roleId,
            ]);
        }

        echo "RBAC roles and permissions initialized.\n";
    }
}
