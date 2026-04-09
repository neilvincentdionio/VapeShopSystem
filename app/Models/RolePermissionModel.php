<?php

namespace App\Models;

use CodeIgniter\Model;

class RolePermissionModel extends Model
{
    protected $table = 'role_permissions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'role',
        'permission_id'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'role' => 'required|in_list[admin,customer,staff]',
        'permission_id' => 'required|integer'
    ];

    /**
     * Get permissions for a role
     */
    public function getRolePermissions(string $role): array
    {
        return $this->select('permissions.*')
                    ->join('permissions', 'permissions.id = role_permissions.permission_id')
                    ->where('role_permissions.role', $role)
                    ->findAll();
    }

    /**
     * Check if role has specific permission
     */
    public function roleHasPermission(string $role, string $permissionName): bool
    {
        $result = $this->select('permissions.*')
                       ->join('permissions', 'permissions.id = role_permissions.permission_id')
                       ->where('role_permissions.role', $role)
                       ->where('permissions.name', $permissionName)
                       ->first();

        return $result !== null;
    }

    
    /**
     * Assign permission to role
     */
    public function assignPermission(string $role, int $permissionId): bool
    {
        // Check if already assigned
        $existing = $this->where('role', $role)
                         ->where('permission_id', $permissionId)
                         ->first();

        if ($existing) {
            return true; // Already assigned
        }

        return $this->insert([
            'role' => $role,
            'permission_id' => $permissionId
        ]) !== false;
    }

    /**
     * Remove permission from role
     */
    public function removePermission(string $role, int $permissionId): bool
    {
        return $this->where('role', $role)
                   ->where('permission_id', $permissionId)
                   ->delete() > 0;
    }

    /**
     * Remove all permissions for a role
     */
    public function removeAllRolePermissions(string $role): bool
    {
        return $this->where('role', $role)->delete() > 0;
    }
}
