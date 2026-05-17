<?php

namespace App\Models;

use CodeIgniter\Model;

class CartModel extends Model
{
    protected $table = 'carts';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['user_id'];
    protected $useTimestamps = true;

    public function getOrCreateCartId(int $userId): int
    {
        $row = $this->where('user_id', $userId)->first();
        if (is_array($row)) {
            return (int) $row['id'];
        }

        $this->insert(['user_id' => $userId]);
        return (int) $this->getInsertID();
    }
}
