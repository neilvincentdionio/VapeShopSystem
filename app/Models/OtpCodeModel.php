<?php

namespace App\Models;

use CodeIgniter\Model;

class OtpCodeModel extends Model
{
    protected $table = 'otp_codes';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'user_id',
        'email',
        'otp_code',
        'expires_at',
        'is_used',
        'created_at',
        'updated_at',
    ];

    public function createForUser(int $userId, string $email, string $otpCode, string $expiresAt): bool
    {
        // Keep only one active OTP per user: delete old codes
        $this->where('user_id', $userId)->delete();

        return (bool) $this->insert([
            'user_id'     => $userId,
            'email'       => $email,
            'otp_code'    => $otpCode,
            'expires_at'  => $expiresAt,
            'is_used'     => 0,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function getActiveForUser(int $userId): ?array
    {
        $row = $this->where('user_id', $userId)
            ->where('is_used', 0)
            ->orderBy('id', 'DESC')
            ->first();

        return $row ?: null;
    }

    public function markUsed(int $id): void
    {
        $this->update($id, [
            'is_used'    => 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function deleteForUser(int $userId): void
    {
        $this->where('user_id', $userId)->delete();
    }
}

