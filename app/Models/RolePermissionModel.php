<?php

namespace App\Models;

use CodeIgniter\Model;

class RolePermissionModel extends Model
{
    protected $table = 'role_permissions';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['role_id', 'permission_id', 'created_at'];
    protected $primaryKey = '';
    protected $useAutoIncrement = false;
    protected $useTimestamps = false;

    /**
     * @return string[]
     */
    public function getPermissionNamesByRoleId(int $roleId): array
    {
        if ($roleId <= 0) {
            return [];
        }

        $rows = $this->select('permissions.name')
            ->join('permissions', 'permissions.id = role_permissions.permission_id', 'inner')
            ->where('role_permissions.role_id', $roleId)
            ->findAll();

        return array_values(array_unique(array_map(static fn ($row) => (string) ($row['name'] ?? ''), $rows)));
    }
}

