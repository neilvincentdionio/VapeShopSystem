<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\PermissionModel;
use App\Models\RolePermissionModel;
use App\Models\UserRoleModel;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $permissionModel = new PermissionModel();
        $rolePermissionModel = new RolePermissionModel();
        $userRoleModel = new UserRoleModel();
        
        // Add new permissions if they don't exist
        $newPermissions = [
            'users.read' => 'View user list and details',
            'users.write' => 'Create new users',
            'users.update' => 'Update user information',
            'users.delete' => 'Delete users',
            'products.read' => 'View product list and details',
            'products.write' => 'Create new products',
            'products.update' => 'Update product information',
            'products.delete' => 'Delete products',
            'orders.read' => 'View order list and details',
            'orders.write' => 'Create new orders',
            'orders.update' => 'Update order status',
            'orders.delete' => 'Delete orders',
            'dashboard.read' => 'View dashboard',
            'reports.read' => 'View reports',
            'reports.write' => 'Generate reports',
            'backup.read' => 'View backup status',
            'backup.write' => 'Create backups',
            'backup.delete' => 'Delete backups',
            'system.read' => 'View system settings',
            'system.update' => 'Update system settings',
            'audit.read' => 'View audit logs'
        ];
        
        foreach ($newPermissions as $name => $description) {
            $permissionModel->createIfNotExists($name, $description);
        }
        
        // Get all permissions
        $allPermissions = $permissionModel->findAll();
        
        // Admin gets all permissions
        foreach ($allPermissions as $permission) {
            $rolePermissionModel->assignPermission('admin', $permission['id']);
        }
        
        // Staff gets limited permissions
        $staffPermissions = [
            'view_dashboard', 'manage_records', 'view_reports',
            'products.read', 'products.update',
            'orders.read', 'orders.update'
        ];
        
        foreach ($staffPermissions as $permissionName) {
            $permission = $permissionModel->getByName($permissionName);
            if ($permission) {
                $rolePermissionModel->assignPermission('staff', $permission['id']);
            }
        }
        
        // Customer gets minimal permissions
        $customerPermissions = [
            'view_dashboard', 'view_own_profile'
        ];
        
        foreach ($customerPermissions as $permissionName) {
            $permission = $permissionModel->getByName($permissionName);
            if ($permission) {
                $rolePermissionModel->assignPermission('customer', $permission['id']);
            }
        }
        
        // Create user roles for existing users
        $userModel = new \App\Models\UserModel();
        $users = $userModel->findAll();
        
        foreach ($users as $user) {
            $userRoleModel->assignRole($user['id'], $user['role'], 1); // Assigned by admin (ID=1)
        }
        
        echo "Permission system initialized successfully.\n";
    }
}
