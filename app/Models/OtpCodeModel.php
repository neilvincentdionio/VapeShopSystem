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
        'otp_code',
        'expiry_time',
        'attempts',
        'created_at',
    ];

    public function createForUser(int $userId, string $email, string $otpCode, string $expiresAt): bool
    {
        // Keep only one active OTP per user: delete old codes
        $this->where('user_id', $userId)->delete();

        return (bool) $this->insert([
            'user_id'     => $userId,
            'otp_code'    => $otpCode,
            'expiry_time' => $expiresAt,
            'attempts'    => 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function getActiveForUser(int $userId): ?array
    {
        $row = $this->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->first();

        if ($row === null) {
            return null;
        }

        // Normalize legacy column names expected by the auth flow.
        $row['expires_at'] = $row['expiry_time'] ?? null;
        $row['is_used'] = 0;

        return $row ?: null;
    }

    public function markUsed(int $id): void
    {
        // The current table schema does not track used OTPs,
        // so removing the code is the safest equivalent behavior.
        $this->delete($id);
    }

    public function deleteForUser(int $userId): void
    {
        $this->where('user_id', $userId)->delete();
    }
}

