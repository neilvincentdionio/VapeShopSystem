<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = ['name', 'description'];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findByName(string $permissionName): ?array
    {
        $permission = $this->where('name', strtolower(trim($permissionName)))->first();
        return is_array($permission) ? $permission : null;
    }

    public function getByName(string $permissionName): ?array
    {
        return $this->findByName($permissionName);
    }

    public function createIfNotExists(string $name, string $description = ''): int
    {
        $normalized = strtolower(trim($name));
        $existing = $this->findByName($normalized);
        if (is_array($existing)) {
            if ($description !== '') {
                $this->update((int) $existing['id'], ['description' => $description]);
            }

            return (int) $existing['id'];
        }

        $this->insert([
            'name' => $normalized,
            'description' => $description,
        ]);

        return (int) $this->getInsertID();
    }

    /**
     * @param string[] $names
     * @return int[]
     */
    public function idsByNames(array $names): array
    {
        $ids = [];
        foreach ($names as $name) {
            $permission = $this->findByName($name);
            if (is_array($permission)) {
                $ids[] = (int) $permission['id'];
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function getAllWithRoleCounts(): array
    {
        $permissions = $this->orderBy('name', 'ASC')->findAll();

        foreach ($permissions as &$permission) {
            $permission['assigned_roles'] = (int) $this->db->table('role_permissions')
                ->where('permission_id', (int) $permission['id'])
                ->countAllResults();
        }
        unset($permission);

        return $permissions;
    }
}

