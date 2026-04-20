<?php

namespace App\Models;

use CodeIgniter\Model;

class UserSessionModel extends Model
{
    protected $table            = 'user_sessions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'session_id',
        'user_id',
        'ip_address',
        'user_agent',
        'login_time',
        'last_activity',
        'logout_time',
        'status',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Create a new session record for user login
     */
    public function createSession(int $userId, string $sessionId, string $ipAddress = null, string $userAgent = null): int
    {
        $data = [
            'session_id'    => $sessionId,
            'user_id'       => $userId,
            'ip_address'    => $ipAddress,
            'user_agent'    => $userAgent,
            'login_time'    => date('Y-m-d H:i:s'),
            'last_activity' => date('Y-m-d H:i:s'),
            'status'        => 'active',
        ];

        $this->insert($data);
        return $this->getInsertID();
    }

    /**
     * Update session activity timestamp
     */
    public function updateActivity(string $sessionId): bool
    {
        return $this->where('session_id', $sessionId)
                    ->set([
                        'last_activity' => date('Y-m-d H:i:s')
                    ])
                    ->update();
    }

    /**
     * End a session (logout)
     */
    public function endSession(string $sessionId): bool
    {
        return $this->where('session_id', $sessionId)
                    ->set([
                        'status'     => 'inactive',
                        'logout_time' => date('Y-m-d H:i:s')
                    ])
                    ->update();
    }

    /**
     * Expire inactive sessions
     */
    public function expireInactiveSessions(int $timeoutMinutes = 15): int
    {
        $timeoutTime = date('Y-m-d H:i:s', strtotime("-{$timeoutMinutes} minutes"));
        
        return $this->where('last_activity <', $timeoutTime)
                    ->where('status', 'active')
                    ->set([
                        'status'     => 'expired',
                        'logout_time' => date('Y-m-d H:i:s')
                    ])
                    ->update();
    }

    /**
     * Get active sessions for a user
     */
    public function getActiveSessions(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->where('status', 'active')
                    ->orderBy('last_activity', 'DESC')
                    ->findAll();
    }

    /**
     * Get all sessions for a user
     */
    public function getUserSessions(int $userId, int $limit = 50): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('login_time', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get session by session ID
     */
    public function getSessionBySessionId(string $sessionId): ?array
    {
        return $this->where('session_id', $sessionId)->first();
    }

    /**
     * Get active session count
     */
    public function getActiveSessionCount(): int
    {
        return $this->where('status', 'active')->countAllResults();
    }

    /**
     * Get all active sessions with user info
     */
    public function getAllActiveSessions(): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('user_sessions');
        $builder->select('user_sessions.*, users.name, users.email')
                ->join('users', 'users.id = user_sessions.user_id', 'left')
                ->where('user_sessions.status', 'active')
                ->orderBy('user_sessions.last_activity', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get session statistics
     */
    public function getSessionStats(): array
    {
        return [
            'active'   => $this->where('status', 'active')->countAllResults(),
            'inactive' => $this->where('status', 'inactive')->countAllResults(),
            'expired'  => $this->where('status', 'expired')->countAllResults(),
            'total'    => $this->countAllResults(),
        ];
    }

    /**
     * Clean up old sessions (older than specified days)
     */
    public function cleanupOldSessions(int $days = 30): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $this->where('login_time <', $cutoffDate)
                    ->delete();
    }
}
