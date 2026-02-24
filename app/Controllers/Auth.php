<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PasswordResetModel;
use App\Models\LoginAttemptModel;

class Auth extends BaseController
{
    protected $userModel;
    protected $passwordResetModel;
    protected $loginAttemptModel;
    protected $session;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->passwordResetModel = new PasswordResetModel();
        $this->loginAttemptModel = new LoginAttemptModel();
        $this->session = session();
    }

    /**
     * Show login form
     */
    public function login()
    {
        // If already logged in, redirect to dashboard
        if ($this->session->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    /**
     * Process login attempt
     */
    public function authenticate()
    {
        $request = service('request');
        
        // Get form data
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Validate input
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[8]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        // Check IP-based rate limiting
        $ipAddress = $request->getIPAddress();
        if ($this->loginAttemptModel->isIpBlocked($ipAddress)) {
            return redirect()->back()
                           ->with('error', 'Too many login attempts. Please try again later.');
        }

        // Sanitize input
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        // Attempt authentication
        $user = $this->userModel->verifyCredentials($email, $password);

        if ($user) {
            // Record successful login attempt
            $this->loginAttemptModel->recordAttempt($email, true);

            // Regenerate session ID for security
            session()->regenerate();

            // Set session data
            $this->session->set([
                'user_id' => $user['id'],
                'user_name' => $user['name'],
                'user_email' => $user['email'],
                'user_role' => $user['role'],
                'logged_in' => true,
                'last_activity' => time()
            ]);

            return redirect()->to('/dashboard')
                           ->with('success', 'Welcome back, ' . htmlspecialchars($user['name']) . '!');
        } else {
            // Record failed login attempt
            $this->loginAttemptModel->recordAttempt($email, false);

            // Check if account is locked
            $user = $this->userModel->getUserByEmail($email);
            if ($user && $this->userModel->isUserLocked($user['id'])) {
                $remainingTime = $this->userModel->getRemainingLockTime($user['id']);
                return redirect()->back()
                               ->with('error', "Account is temporarily locked. Try again in $remainingTime minutes.");
            }

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Invalid email or password.');
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        // Destroy session
        $this->session->destroy();

        return redirect()->to('/login')
                       ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show forgot password form
     */
    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    /**
     * Process forgot password request
     */
    public function sendResetLink()
    {
        $email = $this->request->getPost('email');

        // Validate input
        if (!$this->validate(['email' => 'required|valid_email'])) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        // Sanitize email
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        // Check if user exists
        $user = $this->userModel->getUserByEmail($email);
        if (!$user) {
            // Don't reveal if email exists or not for security
            return redirect()->back()
                           ->with('success', 'If an account with that email exists, a password reset link has been sent.');
        }

        // Generate reset token
        $token = $this->passwordResetModel->createToken($email);

        // In a real application, send email here
        // For demo purposes, we'll just show the token
        $resetLink = site_url("reset-password?email=" . urlencode($email) . "&token=" . $token);

        return redirect()->back()
                       ->with('success', 'Please click the link to reset your password.')
                       ->with('debug_link', $resetLink); // Remove in production
    }

    /**
     * Show reset password form
     */
    public function resetPassword()
    {
        $email = $this->request->getGet('email');
        $token = $this->request->getGet('token');

        // Validate token
        $reset = $this->passwordResetModel->validateToken($email, $token);
        if (!$reset) {
            return redirect()->to('/forgot-password')
                           ->with('error', 'Invalid or expired reset link.');
        }

        return view('auth/reset_password', [
            'email' => $email,
            'token' => $token
        ]);
    }

    /**
     * Process password reset
     */
    public function updatePassword()
    {
        $email = $this->request->getPost('email');
        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $confirmPassword = $this->request->getPost('confirm_password');

        // Validate input
        $rules = [
            'password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        // Validate token
        $reset = $this->passwordResetModel->validateToken($email, $token);
        if (!$reset) {
            return redirect()->to('/forgot-password')
                           ->with('error', 'Invalid or expired reset link.');
        }

        // Get user
        $user = $this->userModel->getUserByEmail($email);
        if (!$user) {
            return redirect()->to('/forgot-password')
                           ->with('error', 'User not found.');
        }

        // Update password
        $this->userModel->updatePassword($user['id'], $password);

        // Mark token as used
        $this->passwordResetModel->markTokenAsUsed($email, $token);

        return redirect()->to('/login')
                       ->with('success', 'Password has been reset successfully. Please login with your new password.');
    }

    /**
     * Check session timeout
     */
    public function checkSession()
    {
        $lastActivity = $this->session->get('last_activity');
        $timeout = 30 * 60; // 30 minutes

        if ($lastActivity && (time() - $lastActivity) > $timeout) {
            $this->logout();
            return redirect()->to('/login')
                           ->with('error', 'Session expired. Please login again.');
        }

        // Update last activity
        $this->session->set('last_activity', time());
    }
}
