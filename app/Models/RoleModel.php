<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = ['name', 'description', 'level'];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findByName(string $roleName): ?array
    {
        $role = $this->where('name', strtolower(trim($roleName)))->first();
        return is_array($role) ? $role : null;
    }

    public function getIdByName(string $roleName): ?int
    {
        $role = $this->findByName($roleName);

        return is_array($role) ? (int) $role['id'] : null;
    }

    public function createIfNotExists(string $name, string $description = '', int $level = 100): int
    {
        $normalized = strtolower(trim($name));
        $existing = $this->findByName($normalized);
        if (is_array($existing)) {
            $this->update((int) $existing['id'], [
                'description' => $description !== '' ? $description : $existing['description'],
                'level' => $level,
            ]);

            return (int) $existing['id'];
        }

        $this->insert([
            'name' => $normalized,
            'description' => $description,
            'level' => $level,
        ]);

        return (int) $this->getInsertID();
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function getAllWithCounts(): array
    {
        $roles = $this->orderBy('level', 'DESC')->findAll();
        $db = $this->db;

        foreach ($roles as &$role) {
            $roleId = (int) $role['id'];
            $role['permission_count'] = (int) $db->table('role_permissions')
                ->where('role_id', $roleId)
                ->countAllResults();
            $role['user_count'] = (int) $db->table('users')
                ->where('role_id', $roleId)
                ->countAllResults();
            if ($role['user_count'] === 0) {
                $role['user_count'] = (int) $db->table('users')
                    ->where('role', $role['name'])
                    ->countAllResults();
            }
        }
        unset($role);

        return $roles;
    }
}

