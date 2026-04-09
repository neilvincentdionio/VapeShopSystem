<?php

namespace App\Models;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $table = 'user_roles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'role',
        'assigned_by',
        'assigned_at',
        'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer',
        'role' => 'required|in_list[admin,customer,staff]',
        'assigned_by' => 'permit_empty|integer',
        'is_active' => 'permit_empty|in_list[0,1]'
    ];

    /**
     * Get user roles
     */
    public function getUserRoles(int $userId): array
    {
        return $this->where('user_id', $userId)
                   ->where('is_active', 1)
                   ->findAll();
    }

    /**
     * Get user primary role (first active role)
     */
    public function getUserPrimaryRole(int $userId): ?string
    {
        $role = $this->where('user_id', $userId)
                     ->where('is_active', 1)
                     ->first();

        return $role ? $role['role'] : null;
    }

    /**
     * Assign role to user
     */
    public function assignRole(int $userId, string $role, ?int $assignedBy = null): bool
    {
        // Check if role already assigned
        $existing = $this->where('user_id', $userId)
                         ->where('role', $role)
                         ->first();

        if ($existing) {
            // Reactivate if inactive
            if (!$existing['is_active']) {
                return $this->update($existing['id'], ['is_active' => 1]);
            }
            return true; // Already assigned and active
        }

        return $this->insert([
            'user_id' => $userId,
            'role' => $role,
            'assigned_by' => $assignedBy,
            'assigned_at' => date('Y-m-d H:i:s'),
            'is_active' => 1
        ]) !== false;
    }

    /**
     * Remove role from user (deactivate)
     */
    public function removeRole(int $userId, string $role): bool
    {
        return $this->where('user_id', $userId)
                   ->where('role', $role)
                   ->set(['is_active' => 0])
                   ->update();
    }

    /**
     * Remove all roles for user
     */
    public function removeAllRoles(int $userId): bool
    {
        return $this->where('user_id', $userId)
                   ->set(['is_active' => 0])
                   ->update();
    }

    /**
     * Get users with specific role
     */
    public function getUsersWithRole(string $role): array
    {
        return $this->select('users.*, user_roles.assigned_at, user_roles.assigned_by')
                   ->join('users', 'users.id = user_roles.user_id')
                   ->where('user_roles.role', $role)
                   ->where('user_roles.is_active', 1)
                   ->findAll();
    }

    /**
     * Check if user has role
     */
    public function userHasRole(int $userId, string $role): bool
    {
        $result = $this->where('user_id', $userId)
                       ->where('role', $role)
                       ->where('is_active', 1)
                       ->first();

        return $result !== null;
    }
}
