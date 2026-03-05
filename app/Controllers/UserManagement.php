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
        if (!$this->session->get('logged_in') || $this->session->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard')
                           ->with('error', 'Access denied. Admin privileges required.');
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
        
        // Debug: Log the users found
        log_message('info', 'Users found in index: ' . print_r($users, true));

        $data = [
            'users' => $users,
            'user_name' => $this->session->get('user_name'),
            'user_role' => $this->session->get('user_role'),
            'page_title' => 'User Management'
        ];

        return view('user_management/index', $data);
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

        return view('user_management/create');
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
            'name' => 'required|min_length[3]|max_length[255]|regex_match[/^[a-zA-Z0-9\s\-\'\.]+$/]',
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
            'is_active' => 1
        ];

        // Create user with hashed password
        try {
            $result = $this->userModel->createUser($data);
            
            // Log the result for debugging
            log_message('info', 'User creation result: ' . print_r($result, true));
            
            if ($result) {
                return redirect()->to('/user-management')
                               ->with('success', 'User created successfully.');
            } else {
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

        file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - Edit method called for user ID: " . $id . "\n", FILE_APPEND);
        file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - User data loaded: " . print_r($user, true) . "\n", FILE_APPEND);

        $data = [
            'user' => $user,
            'user_name' => $this->session->get('user_name'),
            'user_role' => $this->session->get('user_role'),
            'page_title' => 'Edit User'
        ];

        return view('user_management/edit', $data);
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

        // Debug: Log the posted data
        $postData = $request->getPost();
        file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - Update request data: " . print_r($postData, true) . "\n", FILE_APPEND);
        log_message('info', 'Update request data: ' . print_r($postData, true));

        // Validate input data
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]|regex_match[/^[a-zA-Z0-9\s\-\'\.]+$/]',
            'email' => 'required|valid_email|is_unique[users.email,id,' . $id . ']'
        ];

        // Only validate role if not editing self
        if ($currentUserId != $id) {
            $rules['role'] = 'required|in_list[admin,customer]';
        }

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - Update validation failed: " . print_r($errors, true) . "\n", FILE_APPEND);
            log_message('error', 'Update validation failed: ' . print_r($errors, true));
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
            $data['password'] = password_hash($request->getPost('password'), PASSWORD_DEFAULT);
        }

        try {
            // Try the direct update method first
            $result = $this->userModel->updateUserDirectly($id, $data);
            
            // Debug: Log the update result
            file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - User update result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n", FILE_APPEND);
            file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - Update data: " . print_r($data, true) . "\n", FILE_APPEND);
            log_message('info', 'User update result: ' . print_r($result, true));
            log_message('info', 'Update data: ' . print_r($data, true));
            
            // Check if the data was actually updated by verifying the database
            $updatedUser = $this->userModel->find($id);
            $nameUpdated = $updatedUser && $updatedUser['name'] === $data['name'];
            $emailUpdated = $updatedUser && $updatedUser['email'] === $data['email'];
            $roleUpdated = $updatedUser && $updatedUser['role'] === $data['role'];
            
            file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - Verification - Name updated: " . ($nameUpdated ? 'YES' : 'NO') . "\n", FILE_APPEND);
            file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - Verification - Email updated: " . ($emailUpdated ? 'YES' : 'NO') . "\n", FILE_APPEND);
            file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - Verification - Role updated: " . ($roleUpdated ? 'YES' : 'NO') . "\n", FILE_APPEND);
            
            // Consider it successful if the data matches what we tried to update
            if ($result || ($nameUpdated && $emailUpdated && $roleUpdated)) {
                return redirect()->to('/user-management')
                               ->with('success', 'User updated successfully.');
            } else {
                file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - Update failed - no changes detected\n", FILE_APPEND);
                return redirect()->back()
                               ->with('error', 'Failed to update user. No changes made or database error.');
            }
        } catch (\Exception $e) {
            file_put_contents(WRITEPATH . 'debug_update.log', date('Y-m-d H:i:s') . " - Update exception: " . $e->getMessage() . "\n", FILE_APPEND);
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

        return view('user_management/delete', $data);
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
}
