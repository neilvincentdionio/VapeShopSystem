<?php

namespace App\Models;

use CodeIgniter\Model;

class RecordModel extends Model
{
    protected $table = 'records';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = [
        'record_type',
        'shop_name',
        'reference_number',
        'title',
        'description',
        'quantity',
        'unit_price',
        'total_amount',
        'payment_method',
        'payment_status',
        'record_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'record_type' => 'required|in_list[sales,purchase,inventory,expense]',
        'reference_number' => 'required|min_length[3]|max_length[100]',
        'title' => 'required|min_length[3]|max_length[255]',
        'description' => 'permit_empty|max_length[1000]',
        'quantity' => 'required|integer|greater_than_equal_to[0]',
        'unit_price' => 'required|decimal|greater_than_equal_to[0]',
        'total_amount' => 'required|decimal|greater_than_equal_to[0]',
        'payment_method' => 'permit_empty|in_list[cash,card,gcash,bank_transfer]',
        'payment_status' => 'permit_empty|in_list[paid,partial,unpaid]',
        'record_date' => 'required|valid_date[Y-m-d]',
        'status' => 'required|in_list[pending,completed,cancelled]',
        'notes' => 'permit_empty|max_length[1000]',
    ];
}
