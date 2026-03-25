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
        'date',
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
        'delivery_status',
        'tracking_number',
        'shipped_at',
        'delivered_at',
        'shipping_address',
        'contact_number',
        'notes',
        'created_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'record_type' => 'required|in_list[sales,purchase,inventory,expense]',
        'date' => 'required|valid_date[Y-m-d]',
        'reference_number' => 'required|min_length[3]|max_length[100]',
        'title' => 'required|min_length[3]|max_length[255]',
        'description' => 'permit_empty|max_length[1000]',
        'quantity' => 'required|integer|greater_than_equal_to[0]',
        'unit_price' => 'required|decimal|greater_than_equal_to[0]',
        'total_amount' => 'required|decimal|greater_than_equal_to[0]',
        'payment_method' => 'permit_empty|in_list[cash,card,gcash,bank_transfer]',
        'payment_status' => 'permit_empty|in_list[paid,partial,unpaid,pending]',
        'record_date' => 'required|valid_date[Y-m-d]',
        'status' => 'required|in_list[pending,completed,cancelled]',
        'delivery_status' => 'permit_empty|in_list[to_pay,to_ship,to_receive,completed,cancelled,return_refund]',
        'tracking_number' => 'permit_empty|max_length[100]',
        'shipping_address' => 'permit_empty|max_length[500]',
        'contact_number' => 'permit_empty|max_length[20]',
        'notes' => 'permit_empty|max_length[1000]',
    ];
    
    /**
     * Get orders by delivery status for a specific customer
     */
    public function getOrdersByDeliveryStatus($userId, $deliveryStatus = null)
    {
        $builder = $this->where('record_type', 'sales')
                        ->where('created_by', $userId)
                        ->orderBy('created_at', 'DESC');
        
        if ($deliveryStatus && $deliveryStatus !== 'all') {
            $builder->where('delivery_status', $deliveryStatus);
        }
        
        return $builder->findAll();
    }
    
    /**
     * Update delivery status
     */
    public function updateDeliveryStatus($orderId, $status, $additionalData = [])
    {
        $updateData = ['delivery_status' => $status];
        
        // Add timestamps based on status
        switch ($status) {
            case 'to_ship':
                $updateData['shipped_at'] = date('Y-m-d H:i:s');
                break;
            case 'completed':
                $updateData['delivered_at'] = date('Y-m-d H:i:s');
                break;
        }
        
        // Merge additional data
        $updateData = array_merge($updateData, $additionalData);
        
        return $this->update($orderId, $updateData);
    }
    
    /**
     * Get order counts by status for dashboard
     */
    public function getOrderStatusCounts($userId)
    {
        $builder = $this->select('delivery_status, COUNT(*) as count')
                        ->where('record_type', 'sales')
                        ->where('created_by', $userId)
                        ->groupBy('delivery_status');
        
        $results = $builder->findAll();
        
        $counts = [
            'all' => 0,
            'to_pay' => 0,
            'to_ship' => 0,
            'to_receive' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'return_refund' => 0
        ];
        
        foreach ($results as $result) {
            $status = $result['delivery_status'];
            if (isset($counts[$status])) {
                $counts[$status] = (int) $result['count'];
            }
            $counts['all'] += (int) $result['count'];
        }
        
        return $counts;
    }
}
