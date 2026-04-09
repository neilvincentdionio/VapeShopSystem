<?php

namespace App\Models;

use CodeIgniter\Model;

class RefreshTokenModel extends Model
{
    protected $table = 'refresh_tokens';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'token_hash',
        'expires_at',
        'is_revoked',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer',
        'token_hash' => 'required|max_length[255]',
        'expires_at' => 'required|valid_date',
        'is_revoked' => 'permit_empty|in_list[0,1]'
    ];

    /**
     * Store refresh token
     */
    public function storeRefreshToken(int $userId, string $token): bool
    {
        // Hash the token for secure storage
        $tokenHash = hash('sha256', $token);
        
        // Clean up old tokens for this user
        $this->where('user_id', $userId)
             ->where('expires_at <', date('Y-m-d H:i:s'))
             ->delete();

        $data = [
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => date('Y-m-d H:i:s', time() + 7 * 24 * 60 * 60), // 7 days
            'is_revoked' => 0
        ];

        return $this->insert($data) !== false;
    }

    /**
     * Validate refresh token
     */
    public function validateRefreshToken(int $userId, string $token): bool
    {
        $tokenHash = hash('sha256', $token);
        
        $record = $this->where('user_id', $userId)
                      ->where('token_hash', $tokenHash)
                      ->where('is_revoked', 0)
                      ->where('expires_at >', date('Y-m-d H:i:s'))
                      ->first();

        return $record !== null;
    }

    /**
     * Revoke refresh token
     */
    public function revokeRefreshToken(int $userId, string $token): bool
    {
        $tokenHash = hash('sha256', $token);
        
        return $this->where('user_id', $userId)
                   ->where('token_hash', $tokenHash)
                   ->set(['is_revoked' => 1])
                   ->update();
    }

    /**
     * Revoke all refresh tokens for user
     */
    public function revokeAllRefreshTokens(int $userId): bool
    {
        return $this->where('user_id', $userId)
                   ->set(['is_revoked' => 1])
                   ->update();
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens(): int
    {
        return $this->where('expires_at <', date('Y-m-d H:i:s'))
                   ->delete();
    }
}
