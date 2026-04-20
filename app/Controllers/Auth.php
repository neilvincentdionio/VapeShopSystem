<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PasswordResetModel;
use App\Models\LoginAttemptModel;
use App\Libraries\OtpService;
use App\Libraries\ActivityLogger;

class Auth extends BaseController
{
    protected $userModel;
    protected $passwordResetModel;
    protected $loginAttemptModel;
    protected $otpService;
    protected $session;
    protected $activityLogger;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->passwordResetModel = new PasswordResetModel();
        $this->loginAttemptModel = new LoginAttemptModel();
        $this->otpService = new OtpService();
        $this->session = session();
        $this->activityLogger = new ActivityLogger();
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
     * Show customer registration form
     */
    public function register()
    {
        if ($this->session->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/register');
    }

    /**
     * Process customer registration
     */
    public function storeRegistration()
    {
        if ($this->session->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        $input = $this->request->getPost();
        $verificationIdFile = $this->request->getFile('verification_id_image');
        $errors = $this->validateRegistrationData($input, $verificationIdFile);

        if ($errors !== []) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $errors);
        }

        $verificationIdPath = $this->storeVerificationIdImage($verificationIdFile);
        if ($verificationIdPath === null) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['verification_id_image' => 'Unable to save the uploaded verification ID. Please try again.']);
        }

        $data = [
            'name' => $this->sanitizeName((string) ($input['name'] ?? '')),
            'email' => filter_var((string) ($input['email'] ?? ''), FILTER_SANITIZE_EMAIL),
            'password' => (string) ($input['password'] ?? ''),
            'role' => 'customer',
            'phone_number' => $this->sanitizePhoneNumber((string) ($input['phone_number'] ?? '')),
            'address_line' => $this->sanitizeAddressField((string) ($input['address_line'] ?? '')),
            'city' => $this->sanitizeAddressField((string) ($input['city'] ?? '')),
            'province' => $this->sanitizeAddressField((string) ($input['province'] ?? '')),
            'postal_code' => $this->sanitizePostalCode((string) ($input['postal_code'] ?? '')),
            'legal_age_confirmed' => 1,
            'approval_status' => 'pending',
            'verification_id_path' => $verificationIdPath,
            'is_active' => 0,
        ];

        if (!$this->userModel->createUser($data)) {
            $this->deleteVerificationIdImage($verificationIdPath);
            $modelErrors = $this->userModel->errors();

            return redirect()->back()
                ->withInput()
                ->with('errors', $modelErrors !== [] ? $modelErrors : ['Unable to create your account right now. Please try again.']);
        }

        // Log account creation
        $userId = $this->userModel->getInsertID();
        $this->activityLogger->logAccountCreated($userId, $data['email']);

        return redirect()->to('/login')
            ->with('success', 'Your account request has been submitted and is pending admin approval.');
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

        // Sanitize input
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $account = $this->userModel->findUserByEmail($email);

        // Check IP-based rate limiting
        $ipAddress = $request->getIPAddress();
        if ($this->loginAttemptModel->isIpBlocked($ipAddress)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Too many login attempts from this IP. Please try again later.');
        }

        if (
            $account
            && password_verify($password, $account['password'])
            && ($account['approval_status'] ?? 'approved') !== 'approved'
        ) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Your customer account is pending admin approval.');
        }

        // Attempt authentication
        $user = $this->userModel->verifyCredentials($email, $password);

        if ($user) {
            $this->loginAttemptModel->recordAttempt($email, true);
            session()->regenerate();
            
            // Log successful login activity
            $this->activityLogger->logLoginSuccess((int) $user['id'], (string) $user['email']);

            $this->session->set([
                'otp_pending' => true,
                'otp_user_id' => (int) $user['id'],
                'otp_user_email' => (string) $user['email'],
                'otp_user_name' => (string) $user['name'],
                'otp_user_role' => (string) $user['role'],
                'otp_user_role_id' => (int) ($user['role_id'] ?? 0),
                'otp_user_shop_name' => $user['shop_name'] ?? null,
            ]);

            $issued = $this->otpService->issueOtp((int) $user['id'], (string) $user['email']);

            if (!$issued['sent']) {
                $this->session->setFlashdata('otp_debug', $issued['otp']);
                $this->session->setFlashdata('otp_email_error', (string) ($issued['email_error'] ?? 'Unable to send OTP email.'));
            }

            return redirect()->to('/otp')
                ->with('success', 'Password verified. Enter the OTP to finish signing in.');
        } else {
            // Record failed login attempt for audit/rate limiting.
            $this->loginAttemptModel->recordAttempt($email, false);
            
            // Log failed login activity
            $this->activityLogger->logLoginFailed($email, 'Invalid credentials');

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
     * Show OTP verification form.
     */
    public function otp()
    {
        $userId = (int) ($this->session->get('otp_user_id') ?? 0);
        if (!$this->session->get('otp_pending') || $userId <= 0) {
            return redirect()->to('/login');
        }

        return view('auth/otp_verify', [
            'remaining_attempts' => $this->otpService->getWebRemainingAttempts($userId),
            'resend_cooldown' => $this->otpService->getWebResendCooldown($userId),
            'max_attempts' => $this->otpService->maxAttempts(),
        ]);
    }

    /**
     * Verify OTP code (max 3 attempts).
     */
    public function verifyOtp()
    {
        $userId = (int) ($this->session->get('otp_user_id') ?? 0);
        $email = (string) ($this->session->get('otp_user_email') ?? '');

        if (!$this->session->get('otp_pending') || $userId <= 0 || $email === '') {
            return redirect()->to('/login');
        }

        $otpInput = (string) $this->request->getPost('otp_code');
        $otpInput = preg_replace('/\D+/', '', $otpInput ?? '') ?: '';

        if (strlen($otpInput) !== 6) {
            return redirect()->back()->with('error', 'Please enter the 6-digit OTP.');
        }

        $verification = $this->otpService->verifyForWeb($userId, $otpInput);
        if ($verification['status'] !== 'ok') {
            if (in_array($verification['status'], ['locked', 'expired'], true)) {
                $this->session->remove($this->pendingOtpSessionKeys());
                return redirect()->to('/login')->with('error', $verification['message']);
            }

            $error = $verification['message'];
            if (isset($verification['remaining_attempts'])) {
                $error .= ' Remaining attempts: ' . (int) $verification['remaining_attempts'] . '.';
            }

            return redirect()->back()->with('error', $error);
        }

        session()->regenerate();
        $this->session->set([
            'user_id' => $userId,
            'user_name' => (string) ($this->session->get('otp_user_name') ?? ''),
            'user_email' => $email,
            'user_role' => (string) ($this->session->get('otp_user_role') ?? ''),
            'user_role_id' => (int) ($this->session->get('otp_user_role_id') ?? 0),
            'user_shop_name' => $this->session->get('otp_user_shop_name'),
            'logged_in' => true,
            'last_activity' => time(),
        ]);

        // Create user session and log complete login
        $this->activityLogger->createUserSession($userId);
        
        $this->session->remove($this->pendingOtpSessionKeys());

        return redirect()->to('/dashboard')->with('success', 'OTP verified. Welcome!');
    }

    /**
     * Resend OTP (generates a new OTP and resets attempts).
     */
    public function resendOtp()
    {
        $userId = (int) ($this->session->get('otp_user_id') ?? 0);
        $email = (string) ($this->session->get('otp_user_email') ?? '');

        if (!$this->session->get('otp_pending') || $userId <= 0 || $email === '') {
            return redirect()->to('/login');
        }

        $result = $this->otpService->resendForWeb($userId, $email);

        if ($result['status'] === 'cooldown') {
            return redirect()->to('/otp')->with(
                'error',
                'Please wait ' . (int) ($result['resend_available_in'] ?? 0) . ' second(s) before resending OTP.'
            );
        }

        if (!($result['sent'] ?? false)) {
            $this->session->setFlashdata('otp_debug', (string) ($result['otp'] ?? ''));
            $this->session->setFlashdata('otp_email_error', (string) ($result['email_error'] ?? 'Unable to send OTP email.'));
        }

        return redirect()->to('/otp')->with('success', 'A new OTP has been sent.');
    }

    /**
     * Logout user
     */
    public function logout()
    {
        // Log logout activity if user is logged in
        $userId = $this->session->get('user_id');
        $userEmail = $this->session->get('user_email');
        
        if ($userId && $userEmail) {
            $this->activityLogger->logLogout((int) $userId, (string) $userEmail);
        }
        
        $this->session->remove($this->pendingOtpSessionKeys());
        $this->session->destroy();

        return redirect()->to('/login')
                       ->with('success', 'You have been logged out successfully.');
    }

    /**
     * @return string[]
     */
    private function pendingOtpSessionKeys(): array
    {
        return [
            'otp_pending',
            'otp_user_id',
            'otp_user_email',
            'otp_user_name',
            'otp_user_role',
            'otp_user_role_id',
            'otp_user_shop_name',
        ];
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

        // Check if user is admin - admins cannot reset password via forgot password
        if ($user['role'] === 'admin') {
            return redirect()->back()
                           ->with('error', 'Administrators cannot use the forgot password feature. Please contact the system administrator.');
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

        // Get user and check if admin
        $user = $this->userModel->getUserByEmail($email);
        if (!$user) {
            return redirect()->to('/forgot-password')
                           ->with('error', 'User not found.');
        }

        // Check if user is admin - admins cannot reset password
        if ($user['role'] === 'admin') {
            return redirect()->to('/forgot-password')
                           ->with('error', 'Administrators cannot use the password reset feature. Please contact the system administrator.');
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

        // Check if user is admin - admins cannot reset password
        if ($user['role'] === 'admin') {
            return redirect()->to('/forgot-password')
                           ->with('error', 'Administrators cannot use the password reset feature. Please contact the system administrator.');
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

    /**
     * Validate registration payload without exposing internal model errors.
     *
     * @param array<string, mixed> $input
     * @param \CodeIgniter\HTTP\Files\UploadedFile|null $verificationIdFile
     * @return array<string, string>
     */
    private function validateRegistrationData(array $input, ?\CodeIgniter\HTTP\Files\UploadedFile $verificationIdFile = null): array
    {
        $errors = [];

        $name = $this->sanitizeName((string) ($input['name'] ?? ''));
        $email = filter_var((string) ($input['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = (string) ($input['password'] ?? '');
        $confirmPassword = (string) ($input['confirm_password'] ?? '');
        $phoneNumber = $this->sanitizePhoneNumber((string) ($input['phone_number'] ?? ''));
        $addressLine = $this->sanitizeAddressField((string) ($input['address_line'] ?? ''));
        $city = $this->sanitizeAddressField((string) ($input['city'] ?? ''));
        $province = $this->sanitizeAddressField((string) ($input['province'] ?? ''));
        $postalCode = $this->sanitizePostalCode((string) ($input['postal_code'] ?? ''));

        if ($name === '') {
            $errors['name'] = 'Full name is required.';
        } elseif (strlen($name) < 3) {
            $errors['name'] = 'Full name must be at least 3 characters long.';
        } elseif (strlen($name) > 255) {
            $errors['name'] = 'Full name must not exceed 255 characters.';
        } elseif (!preg_match("/^[\\p{L}\\p{M}\\p{N}\\s\\-\\.'’]+$/u", $name)) {
            $errors['name'] = 'Full name can only contain letters (including ñ), numbers, spaces, hyphens, apostrophes, and periods.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'A valid email address is required.';
        } elseif ($this->userModel->where('email', $email)->first() !== null) {
            $errors['email'] = 'This email address is already registered.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long.';
        }

        if ($confirmPassword === '') {
            $errors['confirm_password'] = 'Please confirm your password.';
        } elseif ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Confirm password must match the password field.';
        }

        if ($phoneNumber === '') {
            $errors['phone_number'] = 'Phone number is required.';
        } elseif (!preg_match('/^[0-9+\-\s\(\)]{7,20}$/', $phoneNumber)) {
            $errors['phone_number'] = 'Phone number must be 7 to 20 characters and contain only digits or common phone symbols.';
        }

        if ($addressLine === '') {
            $errors['address_line'] = 'Street address is required.';
        } elseif (strlen($addressLine) < 5) {
            $errors['address_line'] = 'Street address must be at least 5 characters long.';
        } elseif (strlen($addressLine) > 255) {
            $errors['address_line'] = 'Street address must not exceed 255 characters.';
        } elseif (!preg_match("/^[a-zA-Z0-9\\s\\-\\.\\'#,\\/]+$/", $addressLine)) {
            $errors['address_line'] = 'Street address contains unsupported characters.';
        }

        if ($city === '') {
            $errors['city'] = 'City is required.';
        } elseif (strlen($city) < 2) {
            $errors['city'] = 'City must be at least 2 characters long.';
        } elseif (strlen($city) > 120) {
            $errors['city'] = 'City must not exceed 120 characters.';
        } elseif (!preg_match("/^[a-zA-Z0-9\\s\\-\\.\\']+$/", $city)) {
            $errors['city'] = 'City contains unsupported characters.';
        }

        if ($province === '') {
            $errors['province'] = 'Province is required.';
        } elseif (strlen($province) < 2) {
            $errors['province'] = 'Province must be at least 2 characters long.';
        } elseif (strlen($province) > 120) {
            $errors['province'] = 'Province must not exceed 120 characters.';
        } elseif (!preg_match("/^[a-zA-Z0-9\\s\\-\\.\\']+$/", $province)) {
            $errors['province'] = 'Province contains unsupported characters.';
        }

        if ($postalCode === '') {
            $errors['postal_code'] = 'Postal code is required.';
        } elseif (strlen($postalCode) < 4 || strlen($postalCode) > 20) {
            $errors['postal_code'] = 'Postal code must be between 4 and 20 characters long.';
        } elseif (!preg_match('/^[a-zA-Z0-9\s\-]+$/', $postalCode)) {
            $errors['postal_code'] = 'Postal code can only contain letters, numbers, spaces, and hyphens.';
        }

        $errors = array_merge($errors, $this->validateVerificationIdUpload($verificationIdFile));

        return $errors;
    }

    private function sanitizeName(string $name): string
    {
        return trim((string) preg_replace('/\s+/', ' ', strip_tags($name)));
    }

    private function sanitizePhoneNumber(string $phoneNumber): string
    {
        $cleanPhoneNumber = preg_replace('/[^0-9+\-\s\(\)]/', '', $phoneNumber);

        return trim((string) preg_replace('/\s+/', ' ', $cleanPhoneNumber ?? ''));
    }

    private function sanitizeAddressField(string $value): string
    {
        return trim((string) preg_replace('/\s+/', ' ', strip_tags($value)));
    }

    private function sanitizePostalCode(string $postalCode): string
    {
        $cleanPostalCode = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $postalCode);

        return strtoupper(trim((string) preg_replace('/\s+/', ' ', $cleanPostalCode ?? '')));
    }

    /**
     * @param \CodeIgniter\HTTP\Files\UploadedFile|null $verificationIdFile
     * @return array<string, string>
     */
    private function validateVerificationIdUpload(?\CodeIgniter\HTTP\Files\UploadedFile $verificationIdFile): array
    {
        if ($verificationIdFile === null || $verificationIdFile->getError() === UPLOAD_ERR_NO_FILE) {
            return ['verification_id_image' => 'A verification ID image is required.'];
        }

        $validation = service('validation');
        $validation->setRules([
            'verification_id_image' => 'uploaded[verification_id_image]|max_size[verification_id_image,3072]|is_image[verification_id_image]|mime_in[verification_id_image,image/jpg,image/jpeg,image/png,image/webp]',
        ]);

        if ($validation->withRequest($this->request)->run()) {
            return [];
        }

        return $validation->getErrors();
    }

    private function storeVerificationIdImage(\CodeIgniter\HTTP\Files\UploadedFile $verificationIdFile): ?string
    {
        if (!$verificationIdFile->isValid() || $verificationIdFile->hasMoved()) {
            return null;
        }

        $uploadDirectory = WRITEPATH . 'uploads/customer_ids';

        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0775, true) && !is_dir($uploadDirectory)) {
            return null;
        }

        try {
            $fileName = $verificationIdFile->getRandomName();
            $verificationIdFile->move($uploadDirectory, $fileName);

            return 'customer_ids/' . $fileName;
        } catch (\Throwable $exception) {
            log_message('error', 'Verification ID upload failed: ' . $exception->getMessage());
            return null;
        }
    }

    private function deleteVerificationIdImage(?string $verificationIdPath): void
    {
        if (empty($verificationIdPath)) {
            return;
        }

        $absolutePath = WRITEPATH . 'uploads/' . ltrim($verificationIdPath, '\\/');

        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }
}
