<?php

namespace App\Models;

use CodeIgniter\Model;

class RolePermissionModel extends Model
{
    protected $table = 'role_permissions';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['role_id', 'permission_id', 'created_at'];
    protected $primaryKey = 'role_id';
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

        $rows = $this->db->table($this->table . ' rp')
            ->select('p.name')
            ->join('permissions p', 'p.id = rp.permission_id', 'inner')
            ->where('rp.role_id', $roleId)
            ->get()
            ->getResultArray();

        return array_values(array_unique(array_map(static fn ($row) => (string) ($row['name'] ?? ''), $rows)));
    }

    public function assignPermission(string|int $role, int $permissionId): void
    {
        $roleId = is_int($role) ? $role : (new RoleModel())->getIdByName((string) $role);
        if ($roleId === null || $permissionId <= 0) {
            return;
        }

        $exists = $this->db->table($this->table)
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->countAllResults();

        if ($exists > 0) {
            return;
        }

        $this->db->table($this->table)->insert([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param int[] $permissionIds
     */
    public function syncPermissions(string|int $role, array $permissionIds): void
    {
        $roleId = is_int($role) ? $role : (new RoleModel())->getIdByName((string) $role);
        if ($roleId === null) {
            return;
        }

        $this->db->table($this->table)->where('role_id', $roleId)->delete();

        foreach (array_unique(array_filter(array_map('intval', $permissionIds))) as $permissionId) {
            if ($permissionId > 0) {
                $this->assignPermission($roleId, $permissionId);
            }
        }
    }

    /**
     * @return int[]
     */
    public function getPermissionIdsForRole(int $roleId): array
    {
        if ($roleId <= 0) {
            return [];
        }

        $rows = $this->db->table($this->table)
            ->select('permission_id')
            ->where('role_id', $roleId)
            ->get()
            ->getResultArray();

        return array_values(array_unique(array_map(static fn ($row) => (int) ($row['permission_id'] ?? 0), $rows)));
    }
}

