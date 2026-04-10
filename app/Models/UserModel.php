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
        'phone_number',
        'address_line',
        'city',
        'country',
        'barangay',
        'province',
        'postal_code',
        'legal_age_confirmed',
        'approval_status',
        'verification_id_path',
        'is_active',
        'last_login',
        'login_attempts',
        'locked_until'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[255]|regex_match[/^[a-zA-Z0-9\s\-\'\.]+$/]',
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[8]',
        'role' => 'required|in_list[admin,customer]',
        'shop_name' => 'permit_empty|max_length[150]',
        'phone_number' => 'permit_empty|max_length[30]|regex_match[/^[0-9+\-\s\(\)]+$/]',
        'address_line' => 'permit_empty|max_length[255]|regex_match[/^[a-zA-Z0-9\s\-\.\'#,\/]+$/]',
        'city' => 'permit_empty|max_length[120]|regex_match[/^[a-zA-Z0-9\s\-\.\']+$/]',
        'country' => 'permit_empty|max_length[120]|regex_match[/^[a-zA-Z0-9\s\-\.\']+$/]',
        'barangay' => 'permit_empty|max_length[120]|regex_match[/^[a-zA-Z0-9\s\-\.\']+$/]',
        'province' => 'permit_empty|max_length[120]|regex_match[/^[a-zA-Z0-9\s\-\.\']+$/]',
        'postal_code' => 'permit_empty|max_length[20]|regex_match[/^[a-zA-Z0-9\s\-]+$/]',
        'legal_age_confirmed' => 'permit_empty|in_list[0,1]',
        'approval_status' => 'permit_empty|in_list[pending,approved,rejected]',
        'verification_id_path' => 'permit_empty|max_length[255]',
    ];
    protected $validationMessages = [
        'email' => [
            'is_unique' => 'This email address is already registered.'
        ],
        'password' => [
            'min_length' => 'Password must be at least 8 characters long.'
        ],
        'name' => [
            'regex_match' => 'Name can only contain letters, numbers, spaces, hyphens, apostrophes, and periods.'
        ],
        'phone_number' => [
            'regex_match' => 'Phone number can only contain digits, spaces, parentheses, plus signs, and hyphens.'
        ],
        'address_line' => [
            'regex_match' => 'Street address contains unsupported characters.'
        ],
        'city' => [
            'regex_match' => 'City contains unsupported characters.'
        ],
        'country' => [
            'regex_match' => 'Country contains unsupported characters.'
        ],
        'barangay' => [
            'regex_match' => 'Barangay contains unsupported characters.'
        ],
        'province' => [
            'regex_match' => 'Province contains unsupported characters.'
        ],
        'postal_code' => [
            'regex_match' => 'Postal code can only contain letters, numbers, spaces, and hyphens.'
        ]
    ];

    /**
     * Get user by email with security checks
     */
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

    /**
     * Verify user credentials
     */
    public function verifyCredentials($email, $password)
    {
        $user = $this->getUserByEmail($email);
        
        if (!$user) {
            return false;
        }

        // Check if account is locked
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            return false;
        }

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Reset login attempts on successful login
            $this->resetLoginAttempts($user['id']);
            return $user;
        }

        // Increment login attempts on failed login
        $this->incrementLoginAttempts($user['id']);
        return false;
    }

    /**
     * Increment login attempts
     */
    public function incrementLoginAttempts($userId)
    {
        $this->db->table('users')
                 ->where('id', $userId)
                 ->set('login_attempts', 'login_attempts + 1', false)
                 ->update();

        // Lock account after 5 failed attempts
        $user = $this->find($userId);
        if ($user['login_attempts'] >= 5) {
            $this->lockAccount($userId);
        }
    }

    /**
     * Reset login attempts
     */
    public function resetLoginAttempts($userId)
    {
        $this->update($userId, [
            'login_attempts' => 0,
            'locked_until' => null,
            'last_login' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Lock account temporarily
     */
    public function lockAccount($userId, $minutes = 30)
    {
        $lockedUntil = date('Y-m-d H:i:s', strtotime("+$minutes minutes"));
        $this->update($userId, ['locked_until' => $lockedUntil]);
    }

    /**
     * Create new user with hashed password
     */
    public function createUser($data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $this->insert($data);
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
        file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - Direct update called with ID: " . $id . "\n", FILE_APPEND);
        file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - Direct update data: " . print_r($data, true) . "\n", FILE_APPEND);
        
        try {
            $builder = $this->db->table('users');
            
            // Set the data to update
            foreach ($data as $key => $value) {
                $builder->set($key, $value);
            }
            
            // Add updated_at timestamp
            $builder->set('updated_at', 'NOW()', false);
            
            // Execute the update
            $result = $builder->where('id', $id)->update();
            
            file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - Direct update result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n", FILE_APPEND);
            file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - Direct update affected rows: " . $this->db->affectedRows() . "\n", FILE_APPEND);
            
            return $result;
        } catch (\Exception $e) {
            file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - Direct update exception: " . $e->getMessage() . "\n", FILE_APPEND);
            return false;
        }
    }

    /**
     * Override update method for debugging
     */
    public function update($id = null, $data = null): bool
    {
        file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - UserModel update called with ID: " . $id . "\n", FILE_APPEND);
        file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - UserModel update data: " . print_r($data, true) . "\n", FILE_APPEND);
        
        // Check if all fields are in allowedFields
        foreach ($data as $key => $value) {
            if (!in_array($key, $this->allowedFields)) {
                file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - Field '$key' not in allowedFields!\n", FILE_APPEND);
            }
        }
        
        log_message('info', 'UserModel update called with ID: ' . $id);
        log_message('info', 'UserModel update data: ' . print_r($data, true));
        
        // Disable validation temporarily to test
        $originalValidation = $this->skipValidation;
        $this->skipValidation = true;
        
        $result = parent::update($id, $data);
        
        // Restore validation setting
        $this->skipValidation = $originalValidation;
        
        file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - UserModel update result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n", FILE_APPEND);
        file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - Parent update affected rows: " . $this->db->affectedRows() . "\n", FILE_APPEND);
        log_message('info', 'UserModel update result: ' . ($result ? 'SUCCESS' : 'FAILED'));
        
        return $result;
    }

    /**
     * Update user password
     */
    public function updatePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }

    /**
     * Check if user is locked
     */
    public function isUserLocked($userId)
    {
        $user = $this->find($userId);
        if (!$user) {
            return false;
        }

        return $user['locked_until'] && strtotime($user['locked_until']) > time();
    }

    /**
     * Get remaining lock time in minutes
     */
    public function getRemainingLockTime($userId)
    {
        $user = $this->find($userId);
        if (!$user || !$user['locked_until']) {
            return 0;
        }

        $remaining = strtotime($user['locked_until']) - time();
        return max(0, ceil($remaining / 60));
    }
}
