<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name',
        'email',
        'password',
        'role',
        'shop_name',
        'approval_status',
        'verification_id_path',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $afterFind = ['hydrateUserRelations'];

    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[255]|regex_match[/^[a-zA-Z0-9\s\-\'\.]+$/]',
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'permit_empty|min_length[8]',
        'role' => 'required|in_list[admin,customer]',
        'shop_name' => 'permit_empty|max_length[150]',
        'approval_status' => 'permit_empty|in_list[pending,approved,rejected]',
        'verification_id_path' => 'permit_empty|max_length[255]',
        'is_active' => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'This email address is already registered.',
        ],
        'password' => [
            'min_length' => 'Password must be at least 8 characters long.',
        ],
        'name' => [
            'regex_match' => 'Name can only contain letters, numbers, spaces, hyphens, apostrophes, and periods.',
        ],
    ];

    private array $profileFields = [
        'phone_number',
        'legal_age_confirmed',
        'last_login',
        'login_attempts',
        'locked_until',
    ];

    private array $addressFields = [
        'address_line',
        'city',
        'country',
        'barangay',
        'province',
        'postal_code',
    ];

    public function getUserByEmail($email)
    {
        return $this->where('email', $email)
            ->where('is_active', 1)
            ->where('approval_status', 'approved')
            ->first();
    }

    public function findUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    public function verifyCredentials($email, $password)
    {
        $user = $this->getUserByEmail($email);

        if (! $user) {
            return false;
        }

        if (! empty($user['locked_until']) && strtotime((string) $user['locked_until']) > time()) {
            return false;
        }

        if (password_verify($password, (string) $user['password'])) {
            $this->resetLoginAttempts((int) $user['id']);
            return $this->find((int) $user['id']);
        }

        $this->incrementLoginAttempts((int) $user['id']);
        return false;
    }

    public function incrementLoginAttempts($userId)
    {
        $db = $this->db->table('user_profiles');
        $db->where('user_id', $userId)
            ->set('login_attempts', 'login_attempts + 1', false)
            ->set('updated_at', date('Y-m-d H:i:s'))
            ->update();

        $user = $this->find((int) $userId);
        if ((int) ($user['login_attempts'] ?? 0) >= 5) {
            $this->lockAccount((int) $userId);
        }
    }

    public function resetLoginAttempts($userId)
    {
        $this->upsertUserProfile((int) $userId, [
            'login_attempts' => 0,
            'locked_until' => null,
            'last_login' => date('Y-m-d H:i:s'),
        ]);
    }

    public function lockAccount($userId, $minutes = 30)
    {
        $lockedUntil = date('Y-m-d H:i:s', strtotime("+{$minutes} minutes"));
        $this->upsertUserProfile((int) $userId, ['locked_until' => $lockedUntil]);
    }

    public function createUser($data)
    {
        $coreData = $this->extractCoreData($data);
        $profileData = $this->extractProfileData($data);
        $addressData = $this->extractAddressData($data);

        if (! isset($coreData['password'])) {
            return false;
        }

        $coreData['password'] = password_hash((string) $coreData['password'], PASSWORD_DEFAULT);

        $this->db->transStart();

        $inserted = $this->insert($coreData);
        if ($inserted === false) {
            $this->db->transRollback();
            return false;
        }

        $userId = (int) $this->getInsertID();
        $this->upsertUserProfile($userId, $profileData);
        $this->upsertPrimaryAddress($userId, $addressData);

        $this->db->transComplete();

        return $this->db->transStatus() ? $userId : false;
    }

    public function approveUser($userId)
    {
        return $this->update($userId, [
            'approval_status' => 'approved',
            'is_active' => 1,
        ]);
    }

    public function updateUserDirectly($id, $data)
    {
        return $this->update($id, $data);
    }

    public function update($id = null, $data = null): bool
    {
        if ($id === null || ! is_array($data)) {
            return false;
        }

        $coreData = $this->extractCoreData($data);
        $profileData = $this->extractProfileData($data);
        $addressData = $this->extractAddressData($data);

        $this->db->transStart();

        $result = true;
        if ($coreData !== []) {
            $result = parent::update($id, $coreData);
        }

        if ($result && $profileData !== []) {
            $this->upsertUserProfile((int) $id, $profileData);
        }

        if ($result && $addressData !== []) {
            $this->upsertPrimaryAddress((int) $id, $addressData);
        }

        $this->db->transComplete();

        return $result && $this->db->transStatus();
    }

    public function updatePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }

    public function isUserLocked($userId)
    {
        $user = $this->find((int) $userId);
        if (! $user) {
            return false;
        }

        return ! empty($user['locked_until']) && strtotime((string) $user['locked_until']) > time();
    }

    public function getRemainingLockTime($userId)
    {
        $user = $this->find((int) $userId);
        if (! $user || empty($user['locked_until'])) {
            return 0;
        }

        $remaining = strtotime((string) $user['locked_until']) - time();
        return max(0, (int) ceil($remaining / 60));
    }

    protected function hydrateUserRelations(array $data): array
    {
        if (! array_key_exists('data', $data) || $data['data'] === null) {
            return $data;
        }

        if ($this->isSingleRow($data['data'])) {
            $data['data'] = $this->enrichUserRow($data['data']);
            return $data;
        }

        if (is_array($data['data'])) {
            $data['data'] = $this->enrichUserRows($data['data']);
        }

        return $data;
    }

    private function isSingleRow($data): bool
    {
        return is_array($data) && ! isset($data[0]) && array_key_exists('id', $data);
    }

    private function enrichUserRows(array $rows): array
    {
        if ($rows === []) {
            return $rows;
        }

        $userIds = array_values(array_unique(array_map(static fn ($row) => (int) ($row['id'] ?? 0), $rows)));
        $profiles = $this->fetchProfiles($userIds);
        $addresses = $this->fetchPrimaryAddresses($userIds);

        foreach ($rows as &$row) {
            $userId = (int) ($row['id'] ?? 0);
            $row = $this->mergeRelations($row, $profiles[$userId] ?? [], $addresses[$userId] ?? []);
        }
        unset($row);

        return $rows;
    }

    private function enrichUserRow(array $row): array
    {
        $userId = (int) ($row['id'] ?? 0);
        $profiles = $this->fetchProfiles([$userId]);
        $addresses = $this->fetchPrimaryAddresses([$userId]);

        return $this->mergeRelations($row, $profiles[$userId] ?? [], $addresses[$userId] ?? []);
    }

    private function mergeRelations(array $row, array $profile, array $address): array
    {
        $row = array_merge([
            'shop_name' => null,
            'phone_number' => null,
            'legal_age_confirmed' => 0,
            'last_login' => null,
            'login_attempts' => 0,
            'locked_until' => null,
            'address_line' => null,
            'city' => null,
            'country' => null,
            'barangay' => null,
            'province' => null,
            'postal_code' => null,
        ], $row);

        foreach ($this->profileFields as $field) {
            if (array_key_exists($field, $profile)) {
                $row[$field] = $profile[$field];
            }
        }

        foreach ($this->addressFields as $field) {
            if (array_key_exists($field, $address)) {
                $row[$field] = $address[$field];
            }
        }

        $row['phone'] = $row['phone_number'];
        $row['address'] = $this->buildAddressString($row);

        return $row;
    }

    private function fetchProfiles(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        $rows = $this->db->table('user_profiles')
            ->whereIn('user_id', $userIds)
            ->get()
            ->getResultArray();

        $profiles = [];
        foreach ($rows as $row) {
            $profiles[(int) $row['user_id']] = $row;
        }

        return $profiles;
    }

    private function fetchPrimaryAddresses(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        $rows = $this->db->table('user_addresses')
            ->whereIn('user_id', $userIds)
            ->orderBy('is_primary', 'DESC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $addresses = [];
        foreach ($rows as $row) {
            $userId = (int) $row['user_id'];
            if (! isset($addresses[$userId])) {
                $addresses[$userId] = $row;
            }
        }

        return $addresses;
    }

    private function extractCoreData(array $data): array
    {
        return array_intersect_key($data, array_flip($this->allowedFields));
    }

    private function extractProfileData(array $data): array
    {
        return array_intersect_key($data, array_flip($this->profileFields));
    }

    private function extractAddressData(array $data): array
    {
        return array_intersect_key($data, array_flip($this->addressFields));
    }

    private function upsertUserProfile(int $userId, array $profileData): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $existing = $this->db->table('user_profiles')
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        $payload = array_merge([
            'phone_number' => $existing['phone_number'] ?? null,
            'legal_age_confirmed' => isset($existing['legal_age_confirmed']) ? (int) $existing['legal_age_confirmed'] : 0,
            'last_login' => $existing['last_login'] ?? null,
            'login_attempts' => isset($existing['login_attempts']) ? (int) $existing['login_attempts'] : 0,
            'locked_until' => $existing['locked_until'] ?? null,
        ], $profileData, [
            'updated_at' => $timestamp,
        ]);

        if ($existing) {
            $this->db->table('user_profiles')
                ->where('user_id', $userId)
                ->update($payload);
            return;
        }

        $payload['user_id'] = $userId;
        $payload['created_at'] = $timestamp;

        $this->db->table('user_profiles')->insert($payload);
    }

    private function upsertPrimaryAddress(int $userId, array $addressData): void
    {
        $existing = $this->db->table('user_addresses')
            ->where('user_id', $userId)
            ->where('is_primary', 1)
            ->get()
            ->getRowArray();

        $timestamp = date('Y-m-d H:i:s');
        $payload = array_merge([
            'address_line' => $existing['address_line'] ?? null,
            'city' => $existing['city'] ?? null,
            'country' => $existing['country'] ?? null,
            'barangay' => $existing['barangay'] ?? null,
            'province' => $existing['province'] ?? null,
            'postal_code' => $existing['postal_code'] ?? null,
        ], $addressData, [
            'updated_at' => $timestamp,
        ]);

        if (! $this->hasMeaningfulAddress($payload)) {
            if ($existing && $addressData !== []) {
                $this->db->table('user_addresses')->where('id', $existing['id'])->delete();
            }
            return;
        }

        if ($existing) {
            $this->db->table('user_addresses')
                ->where('id', $existing['id'])
                ->update($payload);
            return;
        }

        $payload['user_id'] = $userId;
        $payload['is_primary'] = 1;
        $payload['created_at'] = $timestamp;

        $this->db->table('user_addresses')->insert($payload);
    }

    private function hasMeaningfulAddress(array $addressData): bool
    {
        foreach ($this->addressFields as $field) {
            if (! empty($addressData[$field])) {
                return true;
            }
        }

        return false;
    }

    private function buildAddressString(array $row): string
    {
        $parts = [];
        foreach (['address_line', 'city', 'barangay', 'province', 'postal_code', 'country'] as $field) {
            $value = trim((string) ($row[$field] ?? ''));
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        return implode(', ', $parts);
    }
}
