<?php

namespace App\Models;

use CodeIgniter\Model;

class RecordModel extends Model
{
    protected $table = 'records';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'title',
        'description'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation Rules
    protected $validationRules = [
        'title' => [
            'label' => 'Title',
            'rules' => 'required|min_length[3]|max_length[255]|alpha_numeric_space',
            'errors' => [
                'required' => 'The title field is required.',
                'min_length' => 'The title must be at least 3 characters long.',
                'max_length' => 'The title cannot exceed 255 characters.',
                'alpha_numeric_space' => 'The title may only contain alphanumeric characters and spaces.'
            ]
        ],
        'description' => [
            'label' => 'Description',
            'rules' => 'max_length[1000]',
            'errors' => [
                'max_length' => 'The description cannot exceed 1000 characters.'
            ]
        ]
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get records with optional search functionality
     */
    public function getRecords($search = null, $limit = 10, $offset = 0)
    {
        $builder = $this->builder();
        
        if ($search) {
            $builder->groupStart()
                    ->like('title', $search)
                    ->orLike('description', $search)
                    ->groupEnd();
        }
        
        $builder->orderBy('created_at', 'DESC');
        
        return [
            'records' => $builder->get($limit, $offset)->getResultArray(),
            'total' => $builder->countAllResults(false)
        ];
    }

    /**
     * Get single record by ID
     */
    public function getRecord($id)
    {
        return $this->find($id);
    }

    /**
     * Create new record
     */
    public function createRecord($data)
    {
        return $this->insert($data);
    }

    /**
     * Update existing record
     */
    public function updateRecord($id, $data)
    {
        return $this->update($id, $data);
    }

    /**
     * Delete record
     */
    public function deleteRecord($id)
    {
        return $this->delete($id);
    }

    /**
     * Count total records
     */
    public function countRecords($search = null)
    {
        $builder = $this->builder();
        
        if ($search) {
            $builder->groupStart()
                    ->like('title', $search)
                    ->orLike('description', $search)
                    ->groupEnd();
        }
        
        return $builder->countAllResults();
    }
}
