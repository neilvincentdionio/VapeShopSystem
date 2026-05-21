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
        'record_date',
        'reference_number',
        'title',
        'description',
        'quantity',
        'unit_price',
        'payment_method',
        'payment_status',
        'status',
        'notes',
        'created_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $afterFind = ['decorateRecords'];

    protected $validationRules = [
        'record_type' => 'required|in_list[purchase,inventory,expense,sales]',
        'record_date' => 'required|valid_date[Y-m-d]',
        'reference_number' => 'required|min_length[3]|max_length[100]',
        'title' => 'required|min_length[3]|max_length[255]',
        'description' => 'permit_empty|max_length[1000]',
        'quantity' => 'required|integer|greater_than_equal_to[0]',
        'unit_price' => 'required|decimal|greater_than_equal_to[0]',
        'payment_method' => 'permit_empty|in_list[cash,card,gcash,bank_transfer]',
        'payment_status' => 'permit_empty|in_list[paid,partial,unpaid]',
        'status' => 'required|in_list[pending,completed,cancelled,return_refund]',
        'notes' => 'permit_empty|max_length[1000]',
    ];

    protected function decorateRecords(array $data): array
    {
        if (! isset($data['data']) || $data['data'] === null) {
            return $data;
        }

        if ($this->isSingleRow($data['data'])) {
            $data['data'] = $this->decorateRecordRow($data['data']);
            return $data;
        }

        if (is_array($data['data'])) {
            foreach ($data['data'] as &$row) {
                if (is_array($row)) {
                    $row = $this->decorateRecordRow($row);
                }
            }
            unset($row);
        }

        return $data;
    }

    private function isSingleRow($data): bool
    {
        return is_array($data) && ! isset($data[0]) && array_key_exists('id', $data);
    }

    private function decorateRecordRow(array $row): array
    {
        $row['date'] = $row['record_date'] ?? null;
        $row['total_amount'] = round(((float) ($row['quantity'] ?? 0)) * ((float) ($row['unit_price'] ?? 0)), 2);
        if (($row['status'] ?? '') === 'completed') {
            $row['payment_status'] = 'paid';
        } elseif (($row['status'] ?? '') === 'return_refund') {
            $row['payment_status'] = 'unpaid';
        }
        return $row;
    }
}
