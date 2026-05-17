<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'action',
        'resource_type',
        'resource_id',
        'ip_address',
        'user_agent',
        'details',
        'status',
        'created_at'
    ];

    // Dates
    protected $useTimestamps = false; // Manual timestamp for audit logs
    protected $dateFormat = 'datetime';

    // Validation
    protected $validationRules = [
        'user_id' => 'permit_empty|integer',
        'action' => 'required|max_length[100]',
        'resource_type' => 'permit_empty|max_length[50]',
        'resource_id' => 'permit_empty|integer',
        'ip_address' => 'permit_empty|max_length[45]',
        'user_agent' => 'permit_empty|max_length[255]',
        'details' => 'permit_empty',
        'status' => 'permit_empty|in_list[success,failed,warning]'
    ];

    /**
     * Log an audit event
     */
    public function logEvent(array $data): bool
    {
        $logData = [
            'user_id' => $data['user_id'] ?? null,
            'action' => $data['action'],
            'resource_type' => $data['resource_type'] ?? null,
            'resource_id' => $data['resource_id'] ?? null,
            'ip_address' => $data['ip_address'] ?? $this->request->getIPAddress(),
            'user_agent' => $data['user_agent'] ?? $this->request->getUserAgent(),
            'details' => is_string($data['details'] ?? null) ? $data['details'] : json_encode($data['details'] ?? []),
            'status' => $data['status'] ?? 'success',
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->insert($logData) !== false;
    }

    /**
     * Log login attempt
     */
    public function logLoginAttempt(string $email, bool $success, ?string $reason = null): bool
    {
        return $this->logEvent([
            'action' => 'login_attempt',
            'resource_type' => 'auth',
            'details' => [
                'email' => $email,
                'success' => $success,
                'reason' => $reason
            ],
            'status' => $success ? 'success' : 'failed'
        ]);
    }

    /**
     * Log MFA events
     */
    public function logMfaEvent(int $userId, string $action, bool $success, ?string $details = null): bool
    {
        return $this->logEvent([
            'user_id' => $userId,
            'action' => 'mfa_' . $action,
            'resource_type' => 'mfa',
            'details' => $details,
            'status' => $success ? 'success' : 'failed'
        ]);
    }

    /**
     * Log password reset
     */
    public function logPasswordReset(string $email, bool $success, ?string $token = null): bool
    {
        return $this->logEvent([
            'action' => 'password_reset',
            'resource_type' => 'auth',
            'details' => [
                'email' => $email,
                'success' => $success,
                'token_requested' => $token !== null
            ],
            'status' => $success ? 'success' : 'failed'
        ]);
    }

    /**
     * Log user management actions
     */
    public function logUserAction(int $adminId, int $targetUserId, string $action, array $details = []): bool
    {
        return $this->logEvent([
            'user_id' => $adminId,
            'action' => 'user_' . $action,
            'resource_type' => 'user',
            'resource_id' => $targetUserId,
            'details' => array_merge(['target_user_id' => $targetUserId], $details),
            'status' => 'success'
        ]);
    }

    /**
     * Log backup actions
     */
    public function logBackupAction(int $userId, string $action, ?string $filename = null, bool $success = true): bool
    {
        return $this->logEvent([
            'user_id' => $userId,
            'action' => 'backup_' . $action,
            'resource_type' => 'backup',
            'details' => [
                'filename' => $filename,
                'success' => $success
            ],
            'status' => $success ? 'success' : 'failed'
        ]);
    }

    /**
     * Log role changes
     */
    public function logRoleChange(int $adminId, int $userId, string $oldRole, string $newRole): bool
    {
        return $this->logEvent([
            'user_id' => $adminId,
            'action' => 'role_change',
            'resource_type' => 'user',
            'resource_id' => $userId,
            'details' => [
                'target_user_id' => $userId,
                'old_role' => $oldRole,
                'new_role' => $newRole
            ],
            'status' => 'success'
        ]);
    }

    /**
     * Get audit logs with filtering
     */
    public function getAuditLogs(array $filters = []): array
    {
        $builder = $this->select('audit_logs.*, users.name as user_name, users.email as user_email')
                         ->join('users', 'users.id = audit_logs.user_id', 'left')
                         ->orderBy('audit_logs.created_at', 'DESC');

        // Apply filters
        if (!empty($filters['user_id'])) {
            $builder->where('audit_logs.user_id', $filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $builder->like('audit_logs.action', $filters['action']);
        }

        if (!empty($filters['resource_type'])) {
            $builder->where('audit_logs.resource_type', $filters['resource_type']);
        }

        if (!empty($filters['status'])) {
            $builder->where('audit_logs.status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('audit_logs.created_at >=', $filters['date_from'] . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $builder->where('audit_logs.created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        return $builder->findAll();
    }

    /**
     * Get audit statistics
     */
    public function getAuditStats(): array
    {
        $stats = [];

        // Total logs
        $stats['total_logs'] = $this->countAllResults();

        // Logs by status
        $stats['by_status'] = $this->select('status, COUNT(*) as count')
                                ->groupBy('status')
                                ->findAll();

        // Logs by action type
        $stats['by_action'] = $this->select('action, COUNT(*) as count')
                               ->groupBy('action')
                               ->orderBy('count', 'DESC')
                               ->limit(10)
                               ->findAll();

        // Recent failed attempts
        $stats['recent_failures'] = $this->select('audit_logs.*, users.name as user_name')
                                      ->join('users', 'users.id = audit_logs.user_id', 'left')
                                      ->where('audit_logs.status', 'failed')
                                      ->orderBy('audit_logs.created_at', 'DESC')
                                      ->limit(5)
                                      ->findAll();

        // Today's activity
        $stats['today_activity'] = $this->where('DATE(created_at)', date('Y-m-d'))
                                       ->countAllResults();

        return $stats;
    }

    /**
     * Clean up old audit logs (keep last 90 days)
     */
    public function cleanupOldLogs(): int
    {
        return $this->where('created_at <', date('Y-m-d H:i:s', strtotime('-90 days')))
                   ->delete();
    }
}
