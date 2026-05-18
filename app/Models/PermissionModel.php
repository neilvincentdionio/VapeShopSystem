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
    protected $allowedFields = ['name', 'description', 'module_name'];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findByName(string $permissionName): ?array
    {
        $permission = $this->where('name', strtolower(trim($permissionName)))->first();
        return is_array($permission) ? $permission : null;
    }

    public function ensureExists(string $name, string $description, string $moduleName = 'General'): int
    {
        $normalized = strtolower(trim($name));
        if ($normalized === '') {
            return 0;
        }

        $existing = $this->findByName($normalized);
        if (is_array($existing)) {
            $update = ['description' => $description, 'updated_at' => date('Y-m-d H:i:s')];
            if ($this->db->fieldExists('module_name', $this->table)) {
                $update['module_name'] = $moduleName;
            }
            $this->update((int) $existing['id'], $update);
            return (int) ($existing['id'] ?? 0);
        }

        $now = date('Y-m-d H:i:s');
        $payload = [
            'name' => $normalized,
            'description' => $description,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        if ($this->db->fieldExists('module_name', $this->table)) {
            $payload['module_name'] = $moduleName;
        }

        $this->insert($payload);

        return (int) $this->getInsertID();
    }
}

