<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderReviewModel extends Model
{
    protected $table = 'order_reviews';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = [
        'order_id',
        'customer_id',
        'rating',
        'review_text',
    ];

    public function getCustomerReviewForOrder(int $orderId, int $customerId): ?array
    {
        $row = $this->where('order_id', $orderId)
            ->where('customer_id', $customerId)
            ->first();

        return $row ?: null;
    }
}

