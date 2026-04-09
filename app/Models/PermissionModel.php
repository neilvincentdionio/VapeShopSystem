<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name',
        'description'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|max_length[100]|is_unique[permissions.name,id,{id}]',
        'description' => 'permit_empty|max_length[255]'
    ];

    /**
     * Get permission by name
     */
    public function getByName(string $name): ?array
    {
        return $this->where('name', $name)->first();
    }

    /**
     * Create permission if not exists
     */
    public function createIfNotExists(string $name, ?string $description = null): int
    {
        $existing = $this->getByName($name);
        if ($existing) {
            return $existing['id'];
        }

        $data = [
            'name' => $name,
            'description' => $description
        ];

        return $this->insert($data);
    }
}
