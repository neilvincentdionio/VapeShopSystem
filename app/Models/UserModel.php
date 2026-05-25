<?php

namespace App\Models;

use App\Libraries\EncryptionService;
use App\Libraries\PasswordService;
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
        'role_id',
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
        'name' => 'required|min_length[3]|max_length[255]|safe_person_name',
        'email' => 'required|valid_email',
        'password' => 'permit_empty|min_length[8]',
        'role' => 'permit_empty|max_length[100]',
        'role_id' => 'permit_empty|integer',
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
            'safe_person_name' => 'Name can only contain letters (including ñ/Ñ), numbers, spaces, hyphens, apostrophes, and periods.',
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

    private array $addressCoordinateFields = [
        'delivery_latitude',
        'delivery_longitude',
    ];
    private ?bool $usersRoleIdColumnExists = null;
    private EncryptionService $encryptionService;

    public function __construct(?\CodeIgniter\Database\ConnectionInterface $db = null, ?\CodeIgniter\Validation\ValidationInterface $validation = null)
    {
        parent::__construct($db, $validation);
        $this->encryptionService = new EncryptionService();
    }

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

        if (PasswordService::verify((string) $password, (string) $user['password'])) {
            if (PasswordService::needsRehash((string) $user['password'])) {
                $this->update((int) $user['id'], ['password' => PasswordService::hash((string) $password)]);
            }

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
        $addressData = $this->buildAddressPayload($data);
        $coreData = $this->normalizeRoleAssignments($coreData);

        if (! isset($coreData['password'])) {
            return false;
        }

        $coreData['password'] = PasswordService::hash((string) $coreData['password']);

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
        $addressData = $this->buildAddressPayload($data);
        $coreData = $this->normalizeRoleAssignments($coreData);

        if (array_key_exists('password', $coreData) && $coreData['password'] !== null && $coreData['password'] !== '') {
            $coreData['password'] = PasswordService::hash((string) $coreData['password']);
        }

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
        return $this->update($userId, ['password' => $newPassword]);
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
            'role_id' => null,
            'role_name' => null,
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
            'delivery_latitude' => null,
            'delivery_longitude' => null,
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

        foreach ($this->addressCoordinateFields as $field) {
            if (array_key_exists($field, $address) && $address[$field] !== null && $address[$field] !== '') {
                $row[$field] = (float) $address[$field];
            }
        }

        $row['phone'] = $row['phone_number'];
        $row['address'] = $this->buildAddressString($row);
        $row['role_name'] = $this->resolveRoleName($row);

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
            $row = $this->decryptProfileRow($row);
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
            $row = $this->decryptAddressRow($row);
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

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function buildAddressPayload(array $data): array
    {
        $payload = $this->extractAddressData($data);
        foreach ($this->addressCoordinateFields as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        return $payload;
    }

    private function upsertUserProfile(int $userId, array $profileData): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $existing = $this->db->table('user_profiles')
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        $existing = is_array($existing) ? $this->decryptProfileRow($existing) : [];
        $profileData = $this->encryptProfileData($profileData);

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
        $existing = is_array($existing) ? $this->decryptAddressRow($existing) : [];
        $coordinateData = $this->extractAddressCoordinateData($addressData);
        $addressData = $this->encryptAddressData($addressData);

        $payload = array_merge([
            'address_line' => $existing['address_line'] ?? null,
            'city' => $existing['city'] ?? null,
            'country' => $existing['country'] ?? null,
            'barangay' => $existing['barangay'] ?? null,
            'province' => $existing['province'] ?? null,
            'postal_code' => $existing['postal_code'] ?? null,
            'delivery_latitude' => $existing['delivery_latitude'] ?? null,
            'delivery_longitude' => $existing['delivery_longitude'] ?? null,
        ], $addressData, $coordinateData, [
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

        foreach ($this->addressCoordinateFields as $field) {
            if (isset($addressData[$field]) && $addressData[$field] !== null && $addressData[$field] !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, float|null>
     */
    private function extractAddressCoordinateData(array &$data): array
    {
        $coordinates = [];
        foreach ($this->addressCoordinateFields as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }
            $coordinates[$field] = $this->normalizeCoordinateValue($data[$field], $field);
            unset($data[$field]);
        }

        return $coordinates;
    }

    private function normalizeCoordinateValue($value, string $field = 'delivery_longitude'): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $float = (float) $value;
        if ($field === 'delivery_latitude' && ($float < -90 || $float > 90)) {
            return null;
        }
        if ($field === 'delivery_longitude' && ($float < -180 || $float > 180)) {
            return null;
        }

        return $float;
    }

    /**
     * @param array<string,mixed> $coreData
     * @return array<string,mixed>
     */
    private function normalizeRoleAssignments(array $coreData): array
    {
        $supportsRoleId = $this->supportsRoleIdColumn();

        $roleName = isset($coreData['role']) ? strtolower(trim((string) $coreData['role'])) : '';
        $roleId = isset($coreData['role_id']) ? (int) $coreData['role_id'] : 0;

        if (!$supportsRoleId) {
            unset($coreData['role_id']);
        }

        if ($supportsRoleId && $roleId > 0 && $roleName === '') {
            $resolvedName = $this->getRoleNameById($roleId);
            $coreData['role'] = $resolvedName ?? 'customer';
            $this->ensureRoleColumnSupportsValue((string) $coreData['role']);
            return $coreData;
        }

        if ($roleName !== '') {
            $this->ensureRoleColumnSupportsValue($roleName);
            $coreData['role'] = $roleName;
            if ($supportsRoleId) {
                $resolvedRoleId = $this->getRoleIdByName($coreData['role']);
                if ($resolvedRoleId !== null) {
                    $coreData['role_id'] = $resolvedRoleId;
                }
            }
            return $coreData;
        }

        // Profile updates without role must not overwrite the existing role.
        unset($coreData['role'], $coreData['role_id']);

        return $coreData;
    }

    private function ensureRoleColumnSupportsValue(string $roleName): void
    {
        $roleName = strtolower(trim($roleName));
        if ($roleName === '' || !$this->db->tableExists($this->table) || !$this->db->fieldExists('role', $this->table)) {
            return;
        }

        $columnType = $this->getRoleColumnType();
        if ($columnType === null) {
            return;
        }

        $typeLower = strtolower($columnType);
        if (!str_starts_with($typeLower, 'enum(')) {
            return;
        }

        $allowed = $this->extractEnumValues($columnType);
        if (in_array($roleName, $allowed, true)) {
            return;
        }

        $table = $this->db->protectIdentifiers($this->table, true);
        $this->db->query("ALTER TABLE {$table} MODIFY role VARCHAR(100) NOT NULL DEFAULT 'customer'");
    }

    private function getRoleColumnType(): ?string
    {
        $table = $this->db->protectIdentifiers($this->table, true);
        $row = $this->db->query("SHOW COLUMNS FROM {$table} LIKE 'role'")->getRowArray();
        if (!is_array($row)) {
            return null;
        }

        $type = (string) ($row['Type'] ?? '');
        return $type !== '' ? $type : null;
    }

    /**
     * @return string[]
     */
    private function extractEnumValues(string $columnType): array
    {
        if (!preg_match_all("/'([^']+)'/", $columnType, $matches)) {
            return [];
        }

        return array_values(array_map(static fn (string $v): string => strtolower(trim($v)), $matches[1]));
    }

    /**
     * @param array<string,mixed> $user
     */
    public function userHasRole(array $user, string $roleName): bool
    {
        $expectedRole = strtolower(trim($roleName));
        if ($expectedRole === '') {
            return false;
        }

        $resolvedRole = strtolower((string) $this->resolveRoleName($user));
        if ($resolvedRole !== '' && hash_equals($resolvedRole, $expectedRole)) {
            return true;
        }

        $roleId = (int) ($user['role_id'] ?? 0);
        if ($roleId > 0) {
            $byId = $this->getRoleNameById($roleId);
            return is_string($byId) && hash_equals(strtolower($byId), $expectedRole);
        }

        return false;
    }

    public function hasRole(int $userId, string $roleName): bool
    {
        $user = $this->find($userId);
        return is_array($user) && $this->userHasRole($user, $roleName);
    }

    /**
     * @param array<string,mixed> $user
     */
    public function userHasPermission(array $user, string $permissionName): bool
    {
        $targetPermission = strtolower(trim($permissionName));
        if ($targetPermission === '') {
            return false;
        }

        if ($this->userHasRole($user, 'admin')) {
            $rbac = new \App\Libraries\RbacService();
            $rbac->ensureAdminRoleActive();

            return true;
        }

        if (
            !$this->db->tableExists('roles')
            || !$this->db->tableExists('permissions')
            || !$this->db->tableExists('role_permissions')
        ) {
            $role = strtolower((string) ($user['role'] ?? ''));
            if ($role === 'admin') {
                return true;
            }

            if ($role === 'customer') {
                return in_array($targetPermission, ['read', 'view_products'], true);
            }

            if ($role === 'staff') {
                return in_array($targetPermission, ['read', 'write', 'update', 'view_products', 'create_products', 'update_products', 'manage_orders'], true);
            }

            if ($role === 'rider') {
                return in_array($targetPermission, ['read', 'orders.read', 'orders.update'], true);
            }

            return false;
        }

        $permissions = $this->getPermissionNamesForUser((int) ($user['id'] ?? 0), $user);
        foreach ($permissions as $permission) {
            if (hash_equals(strtolower($permission), $targetPermission)) {
                return true;
            }
        }

        return false;
    }

    public function hasPermission(int $userId, string $permissionName): bool
    {
        $user = $this->find($userId);
        return is_array($user) && $this->userHasPermission($user, $permissionName);
    }

    /**
     * @param array<string,mixed>|null $user
     * @return string[]
     */
    public function getPermissionNamesForUser(int $userId, ?array $user = null): array
    {
        if ($userId <= 0) {
            return [];
        }

        $roleId = 0;
        $userRow = $user;
        if (!is_array($userRow)) {
            $userRow = $this->find($userId);
        }

        if (is_array($userRow)) {
            $roleId = (int) ($userRow['role_id'] ?? 0);
            if ($roleId <= 0) {
                $roleName = $this->resolveRoleName($userRow);
                $fallbackRoleId = $this->getRoleIdByName($roleName);
                $roleId = $fallbackRoleId ?? 0;
            }
        }

        if ($roleId <= 0) {
            return [];
        }

        if (is_array($userRow) && $this->userHasRole($userRow, 'admin')) {
            (new \App\Libraries\RbacService())->ensureAdminRoleActive();

            if ($this->db->tableExists('permissions')) {
                $rows = $this->db->table('permissions')->select('name')->orderBy('name', 'ASC')->get()->getResultArray();

                return array_values(array_unique(array_map(
                    static fn ($row) => (string) ($row['name'] ?? ''),
                    $rows
                )));
            }
        }

        if ($this->db->tableExists('roles') && $this->db->fieldExists('status', 'roles')) {
            $roleRow = $this->db->table('roles')->where('id', $roleId)->get()->getRowArray();
            if (is_array($roleRow) && strtolower((string) ($roleRow['status'] ?? 'active')) !== 'active') {
                return [];
            }
        }

        $rows = $this->db->table('role_permissions rp')
            ->select('p.name')
            ->join('permissions p', 'p.id = rp.permission_id', 'inner')
            ->where('rp.role_id', $roleId)
            ->get()
            ->getResultArray();

        return array_values(array_unique(array_map(static fn ($row) => (string) ($row['name'] ?? ''), $rows)));
    }

    /**
     * @param array<string,mixed> $user
     */
    public function resolveRoleName(array $user): ?string
    {
        $legacyRole = strtolower(trim((string) ($user['role'] ?? '')));
        if ($legacyRole !== '') {
            return $legacyRole;
        }

        $roleId = (int) ($user['role_id'] ?? 0);
        if ($roleId > 0) {
            return $this->getRoleNameById($roleId);
        }

        return null;
    }

    public function getRoleIdByName(string $roleName): ?int
    {
        $normalized = strtolower(trim($roleName));
        if ($normalized === '' || !$this->db->tableExists('roles')) {
            return null;
        }

        $row = $this->db->table('roles')
            ->select('id')
            ->where('name', $normalized)
            ->get()
            ->getRowArray();

        return is_array($row) ? (int) $row['id'] : null;
    }

    public function getRoleNameById(int $roleId): ?string
    {
        if ($roleId <= 0 || !$this->db->tableExists('roles')) {
            return null;
        }

        $row = $this->db->table('roles')
            ->select('name')
            ->where('id', $roleId)
            ->get()
            ->getRowArray();

        return is_array($row) ? (string) $row['name'] : null;
    }

    private function supportsRoleIdColumn(): bool
    {
        if ($this->usersRoleIdColumnExists === null) {
            $this->usersRoleIdColumnExists = $this->db->fieldExists('role_id', $this->table);
        }

        return $this->usersRoleIdColumnExists;
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

    /**
     * @param array<string,mixed> $row
     * @return array<string,mixed>
     */
    private function decryptProfileRow(array $row): array
    {
        if (array_key_exists('phone_number', $row) && $row['phone_number'] !== null) {
            $row['phone_number'] = $this->decryptSensitiveValue((string) $row['phone_number'], 'phone');
        }

        return $row;
    }

    /**
     * @param array<string,mixed> $row
     * @return array<string,mixed>
     */
    private function decryptAddressRow(array $row): array
    {
        foreach ($this->addressFields as $field) {
            if (array_key_exists($field, $row) && $row[$field] !== null) {
                $row[$field] = $this->decryptSensitiveValue((string) $row[$field], 'address');
            }
        }

        return $row;
    }

    /**
     * @param array<string,mixed> $profileData
     * @return array<string,mixed>
     */
    private function encryptProfileData(array $profileData): array
    {
        if (array_key_exists('phone_number', $profileData) && $profileData['phone_number'] !== null && $profileData['phone_number'] !== '') {
            $profileData['phone_number'] = $this->encryptionService->encryptPhoneNumber((string) $profileData['phone_number']);
        }

        return $profileData;
    }

    /**
     * @param array<string,mixed> $addressData
     * @return array<string,mixed>
     */
    private function encryptAddressData(array $addressData): array
    {
        foreach ($this->addressFields as $field) {
            if (array_key_exists($field, $addressData) && $addressData[$field] !== null && $addressData[$field] !== '') {
                $addressData[$field] = $this->encryptionService->encryptAddress((string) $addressData[$field]);
            }
        }

        return $addressData;
    }

    private function decryptSensitiveValue(string $value, string $type): string
    {
        if ($value === '') {
            return '';
        }

        return $type === 'phone'
            ? $this->encryptionService->decryptPhoneNumber($value)
            : $this->encryptionService->decryptAddress($value);
    }
}
