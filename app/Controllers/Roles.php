<?php

namespace App\Controllers;

use App\Models\PermissionModel;
use App\Models\RoleModel;
use App\Models\RolePermissionModel;
use App\Models\UserModel;

class Roles extends BaseController
{
    protected RoleModel $roleModel;
    protected PermissionModel $permissionModel;
    protected RolePermissionModel $rolePermissionModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
        $this->permissionModel = new PermissionModel();
        $this->rolePermissionModel = new RolePermissionModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        if ($redirect = $this->guard('roles.manage')) {
            return $redirect;
        }

        return view('admin/roles/index', [
            'title' => 'Role Management',
            'roles' => $this->roleModel->getAllWithCounts(),
        ]);
    }

    public function create()
    {
        if ($redirect = $this->guard('roles.manage')) {
            return $redirect;
        }

        return view('admin/roles/form', [
            'title' => 'Create Role',
            'role' => null,
            'permissions' => $this->permissionModel->orderBy('name', 'ASC')->findAll(),
            'assignedPermissionIds' => [],
        ]);
    }

    public function store()
    {
        if ($redirect = $this->guard('roles.manage')) {
            return $redirect;
        }

        $name = $this->normalizeRoleName((string) $this->request->getPost('name'));
        $nameError = $this->validateRoleName($name);
        if ($nameError !== null) {
            return redirect()->back()->withInput()->with('errors', ['name' => $nameError]);
        }

        if ($this->roleModel->findByName($name) !== null) {
            return redirect()->back()->withInput()->with('errors', ['name' => 'This role name already exists.']);
        }

        $rules = [
            'description' => 'permit_empty|max_length[500]',
            'level' => 'required|integer|greater_than[0]|less_than_equal_to[999]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $roleId = $this->roleModel->insert([
            'name' => $name,
            'description' => trim((string) $this->request->getPost('description')),
            'level' => (int) $this->request->getPost('level'),
        ]);

        $permissionIds = array_map('intval', (array) $this->request->getPost('permission_ids'));
        $this->rolePermissionModel->syncPermissions((int) $roleId, $permissionIds);

        return redirect()->to('/admin/roles/' . $roleId)->with('success', 'Role created successfully.');
    }

    public function show(int $id)
    {
        if ($redirect = $this->guard('roles.manage')) {
            return $redirect;
        }

        $role = $this->roleModel->find($id);
        if (!is_array($role)) {
            return redirect()->to('/admin/roles')->with('error', 'Role not found.');
        }

        $permissionNames = $this->rolePermissionModel->getPermissionNamesByRoleId($id);

        return view('admin/roles/show', [
            'title' => 'Role Details',
            'role' => $role,
            'permissionNames' => $permissionNames,
            'userCount' => (int) $this->userModel
                ->groupStart()
                ->where('role_id', $id)
                ->orWhere('role', $role['name'])
                ->groupEnd()
                ->countAllResults(),
        ]);
    }

    public function edit(int $id)
    {
        if ($redirect = $this->guard('roles.manage')) {
            return $redirect;
        }

        $role = $this->roleModel->find($id);
        if (!is_array($role)) {
            return redirect()->to('/admin/roles')->with('error', 'Role not found.');
        }

        return view('admin/roles/form', [
            'title' => 'Edit Role',
            'role' => $role,
            'permissions' => $this->permissionModel->orderBy('name', 'ASC')->findAll(),
            'assignedPermissionIds' => $this->rolePermissionModel->getPermissionIdsForRole($id),
        ]);
    }

    public function update(int $id)
    {
        if ($redirect = $this->guard('roles.manage')) {
            return $redirect;
        }

        $role = $this->roleModel->find($id);
        if (!is_array($role)) {
            return redirect()->to('/admin/roles')->with('error', 'Role not found.');
        }

        $name = $this->normalizeRoleName((string) $this->request->getPost('name'));
        $nameError = $this->validateRoleName($name);
        if ($nameError !== null) {
            return redirect()->back()->withInput()->with('errors', ['name' => $nameError]);
        }

        $existing = $this->roleModel->findByName($name);
        if ($existing !== null && (int) $existing['id'] !== $id) {
            return redirect()->back()->withInput()->with('errors', ['name' => 'This role name already exists.']);
        }

        $rules = [
            'description' => 'permit_empty|max_length[500]',
            'level' => 'required|integer|greater_than[0]|less_than_equal_to[999]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->roleModel->update($id, [
            'name' => $name,
            'description' => trim((string) $this->request->getPost('description')),
            'level' => (int) $this->request->getPost('level'),
        ]);

        $permissionIds = array_map('intval', (array) $this->request->getPost('permission_ids'));
        $this->rolePermissionModel->syncPermissions($id, $permissionIds);

        $this->userModel
            ->groupStart()
            ->where('role_id', $id)
            ->orWhere('role', $role['name'])
            ->groupEnd()
            ->set(['role' => $name, 'role_id' => $id])
            ->update();

        return redirect()->to('/admin/roles/' . $id)->with('success', 'Role updated successfully.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->guard('roles.manage')) {
            return $redirect;
        }

        $role = $this->roleModel->find($id);
        if (!is_array($role)) {
            return redirect()->to('/admin/roles')->with('error', 'Role not found.');
        }

        $userCount = (int) $this->userModel
            ->groupStart()
            ->where('role_id', $id)
            ->orWhere('role', $role['name'])
            ->groupEnd()
            ->countAllResults();
        if ($userCount > 0) {
            return redirect()->to('/admin/roles')->with('error', 'Cannot delete a role that still has users assigned.');
        }

        if (in_array($role['name'], ['admin', 'customer', 'rider'], true)) {
            return redirect()->to('/admin/roles')->with('error', 'Core system roles cannot be deleted.');
        }

        $this->rolePermissionModel->db->table('role_permissions')->where('role_id', $id)->delete();
        $this->roleModel->delete($id);

        return redirect()->to('/admin/roles')->with('success', 'Role deleted.');
    }

    private function guard(string $permission)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        if (!$this->hasPermission($permission)) {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Insufficient permission.');
        }

        return null;
    }

    /**
     * Turn any readable role label into a safe stored name (e.g. "Shop Cashier" → shop_cashier).
     */
    private function normalizeRoleName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }

        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9\s_-]/', '', $slug) ?? '';
        $slug = preg_replace('/[\s-]+/', '_', $slug) ?? '';
        $slug = preg_replace('/_+/', '_', $slug) ?? '';

        return trim($slug, '_');
    }

    private function validateRoleName(string $name): ?string
    {
        if ($name === '') {
            return 'Role name is required.';
        }

        if (strlen($name) < 2) {
            return 'Role name must be at least 2 characters after formatting.';
        }

        if (strlen($name) > 50) {
            return 'Role name is too long (max 50 characters).';
        }

        return null;
    }
}
