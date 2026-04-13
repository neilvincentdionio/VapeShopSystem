<?php

namespace App\Controllers;

use App\Models\UserModel;

class UserManagement extends BaseController
{
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->session = session();
    }

    /**
     * Check if user is logged in and is admin
     */
    private function checkAdminAuth()
    {
        if (
            !$this->session->get('logged_in')
            || !$this->hasRole('admin')
            || !$this->hasPermission('manage_users')
        ) {
            return redirect()->to('/dashboard')
                           ->with('error', 'Access denied. User management permission required.');
        }
        return true;
    }

    /**
     * Check if current user can edit the target user
     */
    private function canEditUser($targetUserId)
    {
        $currentUserId = $this->session->get('user_id');
        $currentUserRole = $this->session->get('user_role');
        
        // Debug logging
        file_put_contents(WRITEPATH . 'debug_security.log', date('Y-m-d H:i:s') . " - Security Check: Current User ID: " . $currentUserId . ", Target User ID: " . $targetUserId . ", Role: " . $currentUserRole . "\n", FILE_APPEND);
        
        // If session data is missing, deny access
        if (!$currentUserId || !$currentUserRole) {
            file_put_contents(WRITEPATH . 'debug_security.log', date('Y-m-d H:i:s') . " - Security Check: Missing session data, denying access\n", FILE_APPEND);
            return false;
        }
        
        // Users can only edit their own profile
        // Super admin (id = 1) can edit anyone
        if ($currentUserRole === 'admin' && $currentUserId == 1) {
            file_put_contents(WRITEPATH . 'debug_security.log', date('Y-m-d H:i:s') . " - Security Check: Super admin access granted\n", FILE_APPEND);
            return true;
        }
        
        // Regular users can only edit their own profile
        if ($currentUserId == $targetUserId) {
            file_put_contents(WRITEPATH . 'debug_security.log', date('Y-m-d H:i:s') . " - Security Check: User editing own profile\n", FILE_APPEND);
            return true;
        }
        
        file_put_contents(WRITEPATH . 'debug_security.log', date('Y-m-d H:i:s') . " - Security Check: Access denied\n", FILE_APPEND);
        return false;
    }

    /**
     * Show user management dashboard
     */
    public function index()
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $users = $this->userModel->findAll();

        $data = [
            'users' => $users,
            'user_name' => $this->session->get('user_name'),
            'user_role' => $this->session->get('user_role'),
            'page_title' => 'User Management'
        ];

        return view('admin/user_management/index', $data);
    }

    /**
     * Show registration form
     */
    public function create()
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        return view('admin/user_management/create');
    }

    /**
     * View customer details before approval
     */
    public function view($id)
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/user-management')
                           ->with('error', 'User not found.');
        }

        return view('admin/user_management/view', [
            'user' => $user,
            'user_name' => $this->session->get('user_name'),
            'user_role' => $this->session->get('user_role'),
            'page_title' => 'View Customer',
        ]);
    }

    /**
     * Stream the uploaded verification ID to admins only.
     */
    public function verificationId($id)
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $this->response->setStatusCode(403)->setBody('Access denied.');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setStatusCode(404)->setBody('User not found.');
        }

        $absolutePath = $this->resolveVerificationIdAbsolutePath($user['verification_id_path'] ?? null);
        if ($absolutePath === null || !is_file($absolutePath)) {
            return $this->response->setStatusCode(404)->setBody('Verification ID not found.');
        }

        $mimeType = 'application/octet-stream';
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedType = $finfo->file($absolutePath);
        if (is_string($detectedType) && $detectedType !== '') {
            $mimeType = $detectedType;
        }

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($absolutePath) . '"')
            ->setBody((string) file_get_contents($absolutePath));
    }

    /**
     * Approve a pending customer account
     */
    public function approve($id)
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/user-management')
                           ->with('error', 'User not found.');
        }

        if (($user['role'] ?? '') !== 'customer') {
            return redirect()->to('/user-management')
                           ->with('error', 'Only customer accounts require approval.');
        }

        if (($user['approval_status'] ?? 'approved') === 'approved') {
            return redirect()->to('/user-management')
                           ->with('success', 'This customer account is already approved.');
        }

        try {
            if ($this->userModel->approveUser($id)) {
                return redirect()->to('/user-management')
                               ->with('success', 'Customer account approved successfully.');
            }

            return redirect()->to('/user-management')
                           ->with('error', 'Failed to approve the customer account.');
        } catch (\Exception $e) {
            log_message('error', 'User approval error: ' . $e->getMessage());
            return redirect()->to('/user-management')
                           ->with('error', 'Failed to approve the customer account.');
        }
    }

    /**
     * Process user registration
     */
    public function store()
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $request = service('request');

        // Validate input data
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]|regex_match[/^[\p{L}\p{M}\p{N}\s\-\.\'’]+$/u]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'role' => 'required|in_list[admin,customer]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        // Sanitize input
        $data = [
            'name' => htmlspecialchars($request->getPost('name'), ENT_QUOTES, 'UTF-8'),
            'email' => filter_var($request->getPost('email'), FILTER_SANITIZE_EMAIL),
            'password' => $request->getPost('password'),
            'role' => $request->getPost('role'),
            'approval_status' => 'approved',
            'is_active' => 1
        ];

        // Create user with hashed password
        try {
            $result = $this->userModel->createUser($data);
            
            if ($result) {
                return redirect()->to('/user-management')
                               ->with('success', 'User created successfully.');
            } else {
                $modelErrors = $this->userModel->errors();
                if (!empty($modelErrors)) {
                    return redirect()->back()
                                   ->withInput()
                                   ->with('errors', $modelErrors);
                }

                return redirect()->back()
                               ->with('error', 'Failed to create user. Database error occurred.');
            }
        } catch (\Exception $e) {
            log_message('error', 'User creation error: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Show edit user form
     */
    public function edit($id)
    {
        // Get current session data
        $currentUserId = $this->session->get('user_id');
        $currentUserRole = $this->session->get('user_role');
        
        // IMMEDIATE SECURITY CHECK - No bypassing allowed
        if (!($currentUserRole === 'admin' && $currentUserId == 1) && $currentUserId != $id) {
            return redirect()->to('/user-management')
                           ->with('error', 'Access denied. You can only edit your own profile.');
        }
        
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('/user-management')
                           ->with('error', 'User not found.');
        }


        $data = [
            'user' => $user,
            'user_name' => $this->session->get('user_name'),
            'user_role' => $this->session->get('user_role'),
            'page_title' => 'Edit User'
        ];

        return view('admin/user_management/edit', $data);
    }

    /**
     * Update user profile
     */
    public function update($id)
    {
        // Get current session data
        $currentUserId = $this->session->get('user_id');
        $currentUserRole = $this->session->get('user_role');
        
        // IMMEDIATE SECURITY CHECK - No bypassing allowed
        if (!($currentUserRole === 'admin' && $currentUserId == 1) && $currentUserId != $id) {
            return redirect()->to('/user-management')
                           ->with('error', 'Access denied. You can only edit your own profile.');
        }
        
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $request = service('request');
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/user-management')
                           ->with('error', 'User not found.');
        }

        $postData = $request->getPost();

        // Validate input data
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]|regex_match[/^[\p{L}\p{M}\p{N}\s\-\.\'’]+$/u]',
            'email' => 'required|valid_email|is_unique[users.email,id,' . $id . ']'
        ];

        // Only validate role if not editing self
        if ($currentUserId != $id) {
            $rules['role'] = 'required|in_list[admin,customer]';
        }

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $errors);
        }

        // Sanitize input
        $data = [
            'name' => htmlspecialchars($request->getPost('name'), ENT_QUOTES, 'UTF-8'),
            'email' => filter_var($request->getPost('email'), FILTER_SANITIZE_EMAIL)
        ];

        // Only allow role change if not editing self
        if ($currentUserId != $id) {
            $data['role'] = $request->getPost('role');
        } else {
            // Keep existing role when editing self
            $data['role'] = $user['role'];
        }

        // Update password if provided
        if (!empty($request->getPost('password'))) {
            $data['password'] = $request->getPost('password');
        }

        try {
            // Try the direct update method first
            $result = $this->userModel->updateUserDirectly($id, $data);
            
            if ($result) {
                $updatedUser = $this->userModel->find($id);
                $nameUpdated = $updatedUser && $updatedUser['name'] === $data['name'];
                $emailUpdated = $updatedUser && $updatedUser['email'] === $data['email'];
                $roleUpdated = $updatedUser && $updatedUser['role'] === $data['role'];
            }
            
            // Consider it successful if the data matches what we tried to update
            if ($result || ($nameUpdated && $emailUpdated && $roleUpdated)) {
                return redirect()->to('/user-management')
                               ->with('success', 'User updated successfully.');
            } else {
                return redirect()->back()
                               ->with('error', 'Failed to update user. No changes made or database error.');
            }
        } catch (\Exception $e) {
            log_message('error', 'User update error: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Show delete confirmation
     */
    public function delete($id)
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        // Check if current user can delete this user (only super admin can delete others)
        $currentUserId = $this->session->get('user_id');
        $currentUserRole = $this->session->get('user_role');
        
        if (!($currentUserRole === 'admin' && $currentUserId == 1)) {
            return redirect()->to('/user-management')
                           ->with('error', 'Access denied. Only super admin can delete users.');
        }

        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('/user-management')
                           ->with('error', 'User not found.');
        }

        // Prevent deletion of admin accounts
        if ($user['role'] === 'admin') {
            return redirect()->to('/user-management')
                           ->with('error', 'Admin accounts cannot be deleted.');
        }

        // Prevent self-deletion
        if ($id == $this->session->get('user_id')) {
            return redirect()->to('/user-management')
                           ->with('error', 'You cannot delete your own account.');
        }

        $data = [
            'user' => $user,
            'user_name' => $this->session->get('user_name'),
            'user_role' => $this->session->get('user_role'),
            'page_title' => 'Delete User'
        ];

        return view('admin/user_management/delete', $data);
    }

    /**
     * Process user deletion
     */
    public function destroy($id)
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        // Check if current user can delete this user (only super admin can delete others)
        $currentUserId = $this->session->get('user_id');
        $currentUserRole = $this->session->get('user_role');
        
        if (!($currentUserRole === 'admin' && $currentUserId == 1)) {
            return redirect()->to('/user-management')
                           ->with('error', 'Access denied. Only super admin can delete users.');
        }

        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('/user-management')
                           ->with('error', 'User not found.');
        }

        // Prevent deletion of admin accounts
        if ($user['role'] === 'admin') {
            return redirect()->to('/user-management')
                           ->with('error', 'Admin accounts cannot be deleted.');
        }

        // Prevent self-deletion
        if ($id == $this->session->get('user_id')) {
            return redirect()->to('/user-management')
                           ->with('error', 'You cannot delete your own account.');
        }

        try {
            $this->userModel->delete($id);
            return redirect()->to('/user-management')
                           ->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Failed to delete user. Please try again.');
        }
    }

    private function resolveVerificationIdAbsolutePath(?string $relativePath): ?string
    {
        if (empty($relativePath)) {
            return null;
        }

        $uploadRoot = realpath(WRITEPATH . 'uploads');
        if ($uploadRoot === false) {
            return null;
        }

        $absolutePath = realpath($uploadRoot . DIRECTORY_SEPARATOR . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR));
        if ($absolutePath === false) {
            return null;
        }

        return str_starts_with($absolutePath, $uploadRoot . DIRECTORY_SEPARATOR) ? $absolutePath : null;
    }
}
