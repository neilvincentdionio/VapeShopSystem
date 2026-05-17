<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetModel extends Model
{
    protected $table = 'password_resets';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'email',
        'token',
        'expires_at',
        'is_used',
        'created_at'
    ];

    // Dates
    protected $useTimestamps = false; // Disable automatic timestamps
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = null;

    /**
     * Create password reset token
     */
    public function createToken($email)
    {
        // Delete any existing unused tokens for this email
        $this->where('email', $email)
             ->where('is_used', 0)
             ->delete();

        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $data = [
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiresAt,
            'is_used' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->insert($data);
        return $token;
    }

    /**
     * Validate reset token
     */
    public function validateToken($email, $token)
    {
        $reset = $this->where('email', $email)
                      ->where('token', $token)
                      ->where('is_used', 0)
                      ->where('expires_at >', date('Y-m-d H:i:s'))
                      ->first();

        return $reset ? $reset : false;
    }

    /**
     * Mark token as used
     */
    public function markTokenAsUsed($email, $token)
    {
        return $this->where('email', $email)
                    ->where('token', $token)
                    ->set('is_used', 1)
                    ->update();
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens()
    {
        return $this->where('expires_at <', date('Y-m-d H:i:s'))
                    ->delete();
    }
}
