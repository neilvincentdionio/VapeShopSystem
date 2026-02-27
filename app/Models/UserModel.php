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
        'name' => 'required|min_length[3]|max_length[255]',
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[8]',
        'role' => 'required|in_list[admin,customer]',
        'shop_name' => 'permit_empty|max_length[150]',
    ];
    protected $validationMessages = [
        'email' => [
            'is_unique' => 'This email address is already registered.'
        ],
        'password' => [
            'min_length' => 'Password must be at least 8 characters long.'
        ]
    ];

    /**
     * Get user by email with security checks
     */
    public function getUserByEmail($email)
    {
        return $this->where('email', $email)
                   ->where('is_active', 1)
                   ->first();
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
