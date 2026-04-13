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
        'jti',
        'token_hash',
        'expires_at',
        'is_revoked',
        'revoked_at',
        'replaced_by_token_hash',
        'last_used_at',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'user_id' => 'required|integer',
        'jti' => 'permit_empty|max_length[64]',
        'token_hash' => 'required|max_length[255]',
        'expires_at' => 'required|valid_date',
        'is_revoked' => 'permit_empty|in_list[0,1]',
    ];

    public function storeRefreshToken(int $userId, string $token, string $jti, int $expiresAtUnix): bool
    {
        $this->cleanupExpiredTokens();

        $data = [
            'user_id' => $userId,
            'jti' => $jti,
            'token_hash' => $this->hashToken($token),
            'expires_at' => date('Y-m-d H:i:s', $expiresAtUnix),
            'is_revoked' => 0,
            'revoked_at' => null,
            'replaced_by_token_hash' => null,
            'last_used_at' => null,
        ];

        return $this->insert($data) !== false;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function validateRefreshToken(int $userId, string $jti, string $token): ?array
    {
        $record = $this->where('user_id', $userId)
            ->where('jti', $jti)
            ->where('is_revoked', 0)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->first();

        if (!is_array($record)) {
            return null;
        }

        $incomingHash = $this->hashToken($token);
        if (!hash_equals((string) $record['token_hash'], $incomingHash)) {
            return null;
        }

        return $record;
    }

    public function rotateRefreshToken(
        int $userId,
        string $oldJti,
        string $oldToken,
        string $newJti,
        string $newToken,
        int $newExpiresAtUnix
    ): bool {
        $existing = $this->validateRefreshToken($userId, $oldJti, $oldToken);
        if ($existing === null) {
            return false;
        }

        $this->db->transStart();

        $this->update((int) $existing['id'], [
            'is_revoked' => 1,
            'revoked_at' => date('Y-m-d H:i:s'),
            'last_used_at' => date('Y-m-d H:i:s'),
            'replaced_by_token_hash' => $this->hashToken($newToken),
        ]);

        $this->insert([
            'user_id' => $userId,
            'jti' => $newJti,
            'token_hash' => $this->hashToken($newToken),
            'expires_at' => date('Y-m-d H:i:s', $newExpiresAtUnix),
            'is_revoked' => 0,
            'revoked_at' => null,
            'replaced_by_token_hash' => null,
            'last_used_at' => null,
        ]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function revokeRefreshToken(int $userId, string $jti, string $token): bool
    {
        $record = $this->validateRefreshToken($userId, $jti, $token);
        if ($record === null) {
            return true;
        }

        return $this->update((int) $record['id'], [
            'is_revoked' => 1,
            'revoked_at' => date('Y-m-d H:i:s'),
            'last_used_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function revokeAllRefreshTokens(int $userId): bool
    {
        return $this->where('user_id', $userId)
            ->where('is_revoked', 0)
            ->set([
                'is_revoked' => 1,
                'revoked_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ])
            ->update();
    }

    public function cleanupExpiredTokens(): int
    {
        return $this->where('expires_at <', date('Y-m-d H:i:s'))
            ->delete();
    }

    private function hashToken(string $token): string
    {
        $pepper = (string) env('REFRESH_TOKEN_PEPPER', env('JWT_SECRET', config('Encryption')->key ?? ''));

        if ($pepper === '') {
            throw new \RuntimeException('Refresh token pepper is not configured.');
        }

        return hash_hmac('sha256', $token, $pepper);
    }
}
