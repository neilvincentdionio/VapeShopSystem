<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Encryption\Encryption;

class ActivityLogModel extends Model
{
    protected $table            = 'activity_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'action',
        'action_type',
        'ip_address',
        'user_agent',
        'details',
        'status',
        'created_at',
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['encryptData'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['encryptData'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = ['decryptData'];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected $encrypter;

    public function __construct()
    {
        parent::__construct();
        $this->encrypter = \Config\Services::encrypter();
    }

    /**
     * Encrypt sensitive data before insert
     */
    protected function encryptData(array $data)
    {
        // Encrypt user_id if present
        if (isset($data['user_id'])) {
            $data['user_id'] = base64_encode($this->encrypter->encrypt($data['user_id']));
        }

        // Encrypt action if present
        if (isset($data['action'])) {
            $data['action'] = base64_encode($this->encrypter->encrypt($data['action']));
        }

        // Encrypt IP address if present
        if (isset($data['ip_address'])) {
            $data['ip_address'] = base64_encode($this->encrypter->encrypt($data['ip_address']));
        }

        // Encrypt user agent if present
        if (isset($data['user_agent'])) {
            $data['user_agent'] = base64_encode($this->encrypter->encrypt($data['user_agent']));
        }

        // Encrypt details if present
        if (isset($data['details'])) {
            $data['details'] = base64_encode($this->encrypter->encrypt($data['details']));
        }

        return $data;
    }

    /**
     * Decrypt sensitive data after find
     */
    protected function decryptData(array $data)
    {
        // Handle single result
        if (isset($data['id']) && !isset($data[0])) {
            $data = $this->decryptSingleRecord($data);
        }
        // Handle multiple results
        else if (isset($data[0])) {
            foreach ($data as $key => $record) {
                $data[$key] = $this->decryptSingleRecord($record);
            }
        }

        return $data;
    }

    /**
     * Decrypt a single record
     */
    private function decryptSingleRecord(array $record): array
    {
        // Decrypt user_id if present
        if (!empty($record['user_id'])) {
            try {
                $record['user_id'] = (int) $this->encrypter->decrypt(base64_decode($record['user_id']));
            } catch (\Exception $e) {
                $record['user_id'] = null;
            }
        }

        // Decrypt action if present
        if (!empty($record['action'])) {
            try {
                $record['action'] = $this->encrypter->decrypt(base64_decode($record['action']));
            } catch (\Exception $e) {
                $record['action'] = '[DECRYPT_ERROR]';
            }
        }

        // Decrypt IP address if present
        if (!empty($record['ip_address'])) {
            try {
                $record['ip_address'] = $this->encrypter->decrypt(base64_decode($record['ip_address']));
            } catch (\Exception $e) {
                $record['ip_address'] = '[DECRYPT_ERROR]';
            }
        }

        // Decrypt user agent if present
        if (!empty($record['user_agent'])) {
            try {
                $record['user_agent'] = $this->encrypter->decrypt(base64_decode($record['user_agent']));
            } catch (\Exception $e) {
                $record['user_agent'] = '[DECRYPT_ERROR]';
            }
        }

        // Decrypt details if present
        if (!empty($record['details'])) {
            try {
                $record['details'] = $this->encrypter->decrypt(base64_decode($record['details']));
            } catch (\Exception $e) {
                $record['details'] = '[DECRYPT_ERROR]';
            }
        }

        return $record;
    }

    /**
     * Log an activity
     */
    public function logActivity(int $userId = null, string $action, string $actionType, string $ipAddress = null, string $userAgent = null, string $details = null, string $status = 'success'): bool
    {
        $data = [
            'user_id'    => $userId,
            'action'     => $action,
            'action_type'=> $actionType,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'details'    => $details,
            'status'     => $status,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return $this->insert($data) !== false;
    }

    /**
     * Get logs for a specific user
     */
    public function getUserLogs(int $userId, int $limit = 50): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get logs by action type
     */
    public function getLogsByAction(string $actionType, int $limit = 100): array
    {
        return $this->where('action_type', $actionType)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get failed login attempts
     */
    public function getFailedLogins(int $hours = 24, int $limit = 100): array
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        
        return $this->where('action_type', 'LOGIN_FAILED')
                    ->where('created_at >=', $since)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities(int $limit = 50): array
    {
        return $this->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get activity statistics
     */
    public function getActivityStats(): array
    {
        $stats = [];

        // Count by action type
        $builder = $this->db->table($this->table);
        $stats['by_action_type'] = $builder->select('action_type, COUNT(*) as count')
                                          ->groupBy('action_type')
                                          ->get()
                                          ->getResultArray();

        // Count by status
        $builder = $this->db->table($this->table);
        $stats['by_status'] = $builder->select('status, COUNT(*) as count')
                                      ->groupBy('status')
                                      ->get()
                                      ->getResultArray();

        // Recent 24 hours
        $since = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $builder = $this->db->table($this->table);
        $stats['last_24h'] = $builder->where('created_at >=', $since)
                                     ->countAllResults();

        // Total count
        $stats['total'] = $this->countAllResults();

        return $stats;
    }

    /**
     * Clean up old logs (older than specified days)
     */
    public function cleanupOldLogs(int $days = 90): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $this->where('created_at <', $cutoffDate)->delete();
    }
}
