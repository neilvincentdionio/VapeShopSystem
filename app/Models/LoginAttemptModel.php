<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginAttemptModel extends Model
{
    protected $table = 'login_attempts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'email',
        'ip_address',
        'user_agent',
        'success',
        'attempt_time'
    ];

    // Dates
    protected $useTimestamps = false; // Disable automatic timestamps
    protected $dateFormat = 'datetime';
    protected $createdField = 'attempt_time';
    protected $updatedField = null;

    /**
     * Record login attempt
     */
    public function recordAttempt($email, $success = false)
    {
        $request = service('request');
        
        $data = [
            'email' => $email,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent(),
            'success' => $success ? 1 : 0,
            'attempt_time' => date('Y-m-d H:i:s')
        ];

        return $this->insert($data);
    }

    /**
     * Check if IP has too many failed attempts
     */
    public function isIpBlocked($ipAddress, $minutes = 15, $maxAttempts = 10)
    {
        $since = date('Y-m-d H:i:s', strtotime("-$minutes minutes"));
        
        $count = $this->where('ip_address', $ipAddress)
                     ->where('success', 0)
                     ->where('attempt_time >', $since)
                     ->countAllResults();

        return $count >= $maxAttempts;
    }

    /**
     * Get failed attempts count for email in last hour
     */
    public function getFailedAttemptsCount($email, $hours = 1)
    {
        $since = date('Y-m-d H:i:s', strtotime("-$hours hours"));
        
        return $this->where('email', $email)
                   ->where('success', 0)
                   ->where('attempt_time >', $since)
                   ->countAllResults();
    }

    /**
     * Clean up old login attempts
     */
    public function cleanupOldAttempts($days = 30)
    {
        $since = date('Y-m-d H:i:s', strtotime("-$days days"));
        return $this->where('attempt_time <', $since)->delete();
    }
}
