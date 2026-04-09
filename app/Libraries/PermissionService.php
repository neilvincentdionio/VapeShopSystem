<?php

namespace App\Libraries;

use App\Models\PermissionModel;
use App\Models\RolePermissionModel;
use App\Models\UserRoleModel;

class PermissionService
{
    protected $permissionModel;
    protected $rolePermissionModel;
    protected $userRoleModel;

    public function __construct()
    {
        $this->permissionModel = new PermissionModel();
        $this->rolePermissionModel = new RolePermissionModel();
        $this->userRoleModel = new UserRoleModel();
    }

    /**
     * Check if user has specific permission
     */
    public function userHasPermission(int $userId, string $permissionName): bool
    {
        $userRoles = $this->userRoleModel->getUserRoles($userId);
        
        foreach ($userRoles as $userRole) {
            if ($this->rolePermissionModel->roleHasPermission($userRole['role'], $permissionName)) {
                return true;
            }
        }

        return false;
    }

    
    /**
     * Check if role has specific permission
     */
    public function roleHasPermission(string $role, string $permissionName): bool
    {
        return $this->rolePermissionModel->roleHasPermission($role, $permissionName);
    }

    
    /**
     * Get all permissions for a user
     */
    public function getUserPermissions(int $userId): array
    {
        $userRoles = $this->userRoleModel->getUserRoles($userId);
        $permissions = [];

        foreach ($userRoles as $userRole) {
            $rolePermissions = $this->rolePermissionModel->getRolePermissions($userRole['role']);
            foreach ($rolePermissions as $permission) {
                if (!isset($permissions[$permission['id']])) {
                    $permissions[$permission['id']] = $permission;
                }
            }
        }

        return array_values($permissions);
    }

    /**
     * Get all permissions for a role
     */
    public function getRolePermissions(string $role): array
    {
        return $this->rolePermissionModel->getRolePermissions($role);
    }

    /**
     * Assign permission to role
     */
    public function assignPermissionToRole(string $role, string $permissionName): bool
    {
        $permission = $this->permissionModel->getByName($permissionName);
        if (!$permission) {
            return false;
        }

        return $this->rolePermissionModel->assignPermission($role, $permission['id']);
    }

    /**
     * Remove permission from role
     */
    public function removePermissionFromRole(string $role, string $permissionName): bool
    {
        $permission = $this->permissionModel->getByName($permissionName);
        if (!$permission) {
            return false;
        }

        return $this->rolePermissionModel->removePermission($role, $permission['id']);
    }

    /**
     * Create permission
     */
    public function createPermission(string $name, ?string $description = null): int
    {
        return $this->permissionModel->createIfNotExists($name, $description);
    }

    /**
     * Initialize default permissions
     */
    public function initializeDefaultPermissions(): void
    {
        // Create permissions using existing structure
        $this->createPermission('users.read', 'View user list and details');
        $this->createPermission('users.write', 'Create new users');
        $this->createPermission('users.update', 'Update user information');
        $this->createPermission('users.delete', 'Delete users');

        // Product management permissions
        $this->createPermission('products.read', 'View product list and details');
        $this->createPermission('products.write', 'Create new products');
        $this->createPermission('products.update', 'Update product information');
        $this->createPermission('products.delete', 'Delete products');

        // Order management permissions
        $this->createPermission('orders.read', 'View order list and details');
        $this->createPermission('orders.write', 'Create new orders');
        $this->createPermission('orders.update', 'Update order status');
        $this->createPermission('orders.delete', 'Delete orders');

        // Dashboard permissions
        $this->createPermission('dashboard.read', 'View dashboard');

        // Reports permissions
        $this->createPermission('reports.read', 'View reports');
        $this->createPermission('reports.write', 'Generate reports');

        // Backup permissions
        $this->createPermission('backup.read', 'View backup status');
        $this->createPermission('backup.write', 'Create backups');
        $this->createPermission('backup.delete', 'Delete backups');

        // System permissions
        $this->createPermission('system.read', 'View system settings');
        $this->createPermission('system.update', 'Update system settings');

        // Audit permissions
        $this->createPermission('audit.read', 'View audit logs');
    }

    /**
     * Initialize default role permissions
     */
    public function initializeDefaultRolePermissions(): void
    {
        // Admin gets all permissions
        $allPermissions = $this->permissionModel->findAll();
        foreach ($allPermissions as $permission) {
            $this->rolePermissionModel->assignPermission('admin', $permission['id']);
        }

        // Staff gets limited permissions
        $staffPermissions = [
            'products.read', 'products.update',
            'orders.read', 'orders.update',
            'dashboard.read',
            'reports.read',
            'audit.read'
        ];

        foreach ($staffPermissions as $permissionName) {
            $this->assignPermissionToRole('staff', $permissionName);
        }

        // Customer gets minimal permissions
        $customerPermissions = [
            'dashboard.read',
            'orders.read', 'orders.write'
        ];

        foreach ($customerPermissions as $permissionName) {
            $this->assignPermissionToRole('customer', $permissionName);
        }
    }
}
