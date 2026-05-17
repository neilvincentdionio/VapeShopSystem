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
    protected function encryptData(array $event): array
    {
        if (!isset($event['data']) || !is_array($event['data'])) {
            return $event;
        }

        $fieldsToEncrypt = ['user_id', 'action', 'ip_address', 'user_agent', 'details'];

        foreach ($fieldsToEncrypt as $field) {
            if (!array_key_exists($field, $event['data'])) {
                continue;
            }

            $value = $event['data'][$field];
            if ($value === null || $value === '') {
                continue;
            }

            $event['data'][$field] = base64_encode($this->encrypter->encrypt((string) $value));
        }

        return $event;
    }

    /**
     * Decrypt sensitive data after find
     */
    protected function decryptData(array $event): array
    {
        if (!array_key_exists('data', $event) || $event['data'] === null) {
            return $event;
        }

        if (is_array($event['data']) && isset($event['data'][0]) && is_array($event['data'][0])) {
            foreach ($event['data'] as $key => $record) {
                if (is_array($record)) {
                    $event['data'][$key] = $this->decryptSingleRecord($record);
                }
            }
            return $event;
        }

        if (is_array($event['data'])) {
            $event['data'] = $this->decryptSingleRecord($event['data']);
        }

        return $event;
    }

    /**
     * Decrypt a single record
     */
    private function decryptSingleRecord(array $record): array
    {
        // Decrypt user_id if present
        if (!empty($record['user_id'])) {
            try {
                $decoded = base64_decode((string) $record['user_id'], true);
                if ($decoded !== false) {
                    $record['user_id'] = (int) $this->encrypter->decrypt($decoded);
                }
            } catch (\Exception $e) {
                // Keep legacy/plaintext values readable.
            }
        }

        // Decrypt action if present
        if (!empty($record['action'])) {
            try {
                $decoded = base64_decode((string) $record['action'], true);
                if ($decoded !== false) {
                    $record['action'] = $this->encrypter->decrypt($decoded);
                }
            } catch (\Exception $e) {
                // Keep legacy/plaintext values readable.
            }
        }

        // Decrypt IP address if present
        if (!empty($record['ip_address'])) {
            try {
                $decoded = base64_decode((string) $record['ip_address'], true);
                if ($decoded !== false) {
                    $record['ip_address'] = $this->encrypter->decrypt($decoded);
                }
            } catch (\Exception $e) {
                // Keep legacy/plaintext values readable.
            }
        }

        // Decrypt user agent if present
        if (!empty($record['user_agent'])) {
            try {
                $decoded = base64_decode((string) $record['user_agent'], true);
                if ($decoded !== false) {
                    $record['user_agent'] = $this->encrypter->decrypt($decoded);
                }
            } catch (\Exception $e) {
                // Keep legacy/plaintext values readable.
            }
        }

        // Decrypt details if present
        if (!empty($record['details'])) {
            try {
                $decoded = base64_decode((string) $record['details'], true);
                if ($decoded !== false) {
                    $record['details'] = $this->encrypter->decrypt($decoded);
                }
            } catch (\Exception $e) {
                // Keep legacy/plaintext values readable.
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
        // user_id is encrypted at rest, so filter after decryption.
        $bufferSize = max($limit * 4, 100);
        $rows = $this->orderBy('created_at', 'DESC')
            ->limit($bufferSize)
            ->findAll();

        $filtered = array_values(array_filter(
            $rows,
            static fn(array $row): bool => (int) ($row['user_id'] ?? 0) === $userId
        ));

        return array_slice($filtered, 0, $limit);
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
