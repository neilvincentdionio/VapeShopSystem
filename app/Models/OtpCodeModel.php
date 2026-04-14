<?php

namespace App\Models;

use CodeIgniter\Model;

class OtpCodeModel extends Model
{
    protected $table = 'otp_codes';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;

    protected $allowedFields = [
        'user_id',
        'email',
        'otp_hash',
        'otp_code',
        'challenge_token_hash',
        'expires_at',
        'expiry_time',
        'attempts',
        'max_attempts',
        'is_used',
        'used_at',
        'last_sent_at',
        'invalidated_at',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    /** @var array<string,bool> */
    private array $columnExistsCache = [];

    public function __construct(?\CodeIgniter\Database\ConnectionInterface $db = null, ?\CodeIgniter\Validation\ValidationInterface $validation = null)
    {
        parent::__construct($db, $validation);

        $hasCreatedAt = $this->hasColumn('created_at');
        $hasUpdatedAt = $this->hasColumn('updated_at');

        $this->useTimestamps = $hasCreatedAt || $hasUpdatedAt;
        $this->createdField = $hasCreatedAt ? 'created_at' : '';
        $this->updatedField = $hasUpdatedAt ? 'updated_at' : '';
    }

    public function createForUser(
        int $userId,
        string $email,
        string $otpHash,
        string $plainOtp,
        string $expiresAt,
        ?string $challengeTokenHash = null,
        int $maxAttempts = 3
    ): bool {
        $data = [
            'user_id' => $userId,
            'expiry_time' => $expiresAt,
            'attempts' => 0,
        ];

        // Legacy schema stores the OTP directly in otp_code because otp_hash does not exist.
        if ($this->hasColumn('otp_hash')) {
            $data['otp_code'] = substr(hash('sha256', $otpHash . microtime(true)), 0, 6);
        } else {
            $data['otp_code'] = $plainOtp;
        }

        if ($this->hasColumn('email')) {
            $data['email'] = $email;
        }

        if ($this->hasColumn('otp_hash')) {
            $data['otp_hash'] = $otpHash;
        }

        if ($this->hasColumn('challenge_token_hash')) {
            $data['challenge_token_hash'] = $challengeTokenHash;
        }

        if ($this->hasColumn('expires_at')) {
            $data['expires_at'] = $expiresAt;
        }

        if ($this->hasColumn('max_attempts')) {
            $data['max_attempts'] = max(1, $maxAttempts);
        }

        if ($this->hasColumn('is_used')) {
            $data['is_used'] = 0;
        }

        if ($this->hasColumn('used_at')) {
            $data['used_at'] = null;
        }

        if ($this->hasColumn('last_sent_at')) {
            $data['last_sent_at'] = date('Y-m-d H:i:s');
        }

        if ($this->hasColumn('invalidated_at')) {
            $data['invalidated_at'] = null;
        }

        return $this->insert($data) !== false;
    }

    public function getLatestPendingForUser(int $userId): ?array
    {
        $builder = $this->where('user_id', $userId);

        if ($this->hasColumn('is_used')) {
            $builder = $builder->where('is_used', 0);
        }

        if ($this->hasColumn('invalidated_at')) {
            $builder = $builder->where('invalidated_at', null);
        }

        $row = $builder->orderBy('id', 'DESC')->first();

        return $this->normalizeRow($row);
    }

    public function getPendingByChallengeHash(string $challengeTokenHash): ?array
    {
        if (!$this->hasColumn('challenge_token_hash')) {
            return null;
        }

        $row = $this->where('challenge_token_hash', $challengeTokenHash)
            ->orderBy('id', 'DESC')
            ->first();

        if ($this->hasColumn('is_used') && is_array($row) && (int) ($row['is_used'] ?? 0) !== 0) {
            return null;
        }

        if ($this->hasColumn('invalidated_at') && is_array($row) && !empty($row['invalidated_at'])) {
            return null;
        }

        return $this->normalizeRow($row);
    }

    public function invalidateActiveForUser(int $userId): void
    {
        if (!$this->hasColumn('invalidated_at')) {
            $this->deleteForUser($userId);
            return;
        }

        $builder = $this->where('user_id', $userId);

        if ($this->hasColumn('is_used')) {
            $builder = $builder->where('is_used', 0);
        }

        $builder = $builder->where('invalidated_at', null)
            ->set('invalidated_at', date('Y-m-d H:i:s'));

        if ($this->hasColumn('updated_at')) {
            $builder->set('updated_at', date('Y-m-d H:i:s'));
        }

        $builder->update();
    }

    public function invalidateById(int $id): void
    {
        if (!$this->hasColumn('invalidated_at')) {
            $this->delete($id);
            return;
        }

        $this->update($id, [
            'invalidated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function incrementAttempts(int $id): void
    {
        $builder = $this->builder();
        $builder->where('id', $id)->set('attempts', 'attempts + 1', false);
        if ($this->hasColumn('updated_at')) {
            $builder->set('updated_at', date('Y-m-d H:i:s'));
        }
        $builder->update();
    }

    public function markUsed(int $id): void
    {
        if (!$this->hasColumn('is_used')) {
            $this->delete($id);
            return;
        }

        $payload = [
            'is_used' => 1,
        ];

        if ($this->hasColumn('used_at')) {
            $payload['used_at'] = date('Y-m-d H:i:s');
        }

        $this->update($id, $payload);
    }

    public function getResendCooldownRemainingForUser(int $userId, int $cooldownSeconds): int
    {
        $record = $this->getLatestPendingForUser($userId);
        if ($record === null) {
            return 0;
        }

        return $this->getCooldownRemainingFromRecord($record, $cooldownSeconds);
    }

    public function getResendCooldownRemainingForChallenge(string $challengeTokenHash, int $cooldownSeconds): int
    {
        $record = $this->getPendingByChallengeHash($challengeTokenHash);
        if ($record === null) {
            return 0;
        }

        return $this->getCooldownRemainingFromRecord($record, $cooldownSeconds);
    }

    public function deleteForUser(int $userId): void
    {
        $this->where('user_id', $userId)->delete();
    }

    /**
     * @param array<string,mixed>|null $row
     * @return array<string,mixed>|null
     */
    private function normalizeRow(?array $row): ?array
    {
        if (!is_array($row)) {
            return null;
        }

        if (empty($row['expires_at']) && !empty($row['expiry_time'])) {
            $row['expires_at'] = $row['expiry_time'];
        }

        if (empty($row['last_sent_at']) && !empty($row['created_at'])) {
            $row['last_sent_at'] = $row['created_at'];
        }

        if (!isset($row['max_attempts']) || (int) $row['max_attempts'] <= 0) {
            $row['max_attempts'] = 3;
        }

        if (!isset($row['is_used'])) {
            $row['is_used'] = 0;
        }

        return $row;
    }

    /**
     * @param array<string,mixed> $record
     */
    private function getCooldownRemainingFromRecord(array $record, int $cooldownSeconds): int
    {
        $lastSentAt = strtotime((string) ($record['last_sent_at'] ?? ''));
        if ($lastSentAt === false) {
            return 0;
        }

        $remaining = ($lastSentAt + $cooldownSeconds) - time();

        return max(0, $remaining);
    }

    private function hasColumn(string $column): bool
    {
        if (!array_key_exists($column, $this->columnExistsCache)) {
            $this->columnExistsCache[$column] = $this->db->fieldExists($column, $this->table);
        }

        return $this->columnExistsCache[$column];
    }
}
