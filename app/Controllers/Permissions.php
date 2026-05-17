<?php

namespace App\Controllers;

use App\Models\PermissionModel;
use App\Models\RoleModel;
use App\Models\RolePermissionModel;

class Permissions extends BaseController
{
    protected PermissionModel $permissionModel;
    protected RoleModel $roleModel;
    protected RolePermissionModel $rolePermissionModel;

    public function __construct()
    {
        $this->permissionModel = new PermissionModel();
        $this->roleModel = new RoleModel();
        $this->rolePermissionModel = new RolePermissionModel();
    }

    public function index()
    {
        if ($redirect = $this->guard('permissions.manage')) {
            return $redirect;
        }

        return view('admin/permissions/index', [
            'title' => 'Permissions',
            'permissions' => $this->permissionModel->getAllWithRoleCounts(),
        ]);
    }

    public function create()
    {
        if ($redirect = $this->guard('permissions.manage')) {
            return $redirect;
        }

        return view('admin/permissions/form', [
            'title' => 'Create Permission',
            'permission' => null,
            'roles' => $this->roleModel->orderBy('level', 'DESC')->findAll(),
            'assignedRoleIds' => [],
        ]);
    }

    public function store()
    {
        if ($redirect = $this->guard('permissions.manage')) {
            return $redirect;
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[100]|regex_match[/^[a-z0-9_.]+$/]|is_unique[permissions.name]',
            'description' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $permissionId = $this->permissionModel->insert([
            'name' => strtolower(trim((string) $this->request->getPost('name'))),
            'description' => trim((string) $this->request->getPost('description')),
        ]);

        $this->syncRolesForPermission((int) $permissionId, (array) $this->request->getPost('role_ids'));

        return redirect()->to('/admin/permissions')->with('success', 'Permission created successfully.');
    }

    public function show(int $id)
    {
        if ($redirect = $this->guard('permissions.manage')) {
            return $redirect;
        }

        $permission = $this->permissionModel->find($id);
        if (!is_array($permission)) {
            return redirect()->to('/admin/permissions')->with('error', 'Permission not found.');
        }

        $roles = $this->db()->table('roles')
            ->select('roles.*')
            ->join('role_permissions', 'role_permissions.role_id = roles.id', 'inner')
            ->where('role_permissions.permission_id', $id)
            ->orderBy('roles.level', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/permissions/show', [
            'title' => 'Permission Details',
            'permission' => $permission,
            'roles' => $roles,
        ]);
    }

    public function edit(int $id)
    {
        if ($redirect = $this->guard('permissions.manage')) {
            return $redirect;
        }

        $permission = $this->permissionModel->find($id);
        if (!is_array($permission)) {
            return redirect()->to('/admin/permissions')->with('error', 'Permission not found.');
        }

        $assignedRoleIds = $this->db()->table('role_permissions')
            ->select('role_id')
            ->where('permission_id', $id)
            ->get()
            ->getResultArray();

        return view('admin/permissions/form', [
            'title' => 'Edit Permission',
            'permission' => $permission,
            'roles' => $this->roleModel->orderBy('level', 'DESC')->findAll(),
            'assignedRoleIds' => array_map(static fn ($row) => (int) ($row['role_id'] ?? 0), $assignedRoleIds),
        ]);
    }

    public function update(int $id)
    {
        if ($redirect = $this->guard('permissions.manage')) {
            return $redirect;
        }

        $permission = $this->permissionModel->find($id);
        if (!is_array($permission)) {
            return redirect()->to('/admin/permissions')->with('error', 'Permission not found.');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[100]|regex_match[/^[a-z0-9_.]+$/]|is_unique[permissions.name,id,' . $id . ']',
            'description' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->permissionModel->update($id, [
            'name' => strtolower(trim((string) $this->request->getPost('name'))),
            'description' => trim((string) $this->request->getPost('description')),
        ]);

        $this->syncRolesForPermission($id, (array) $this->request->getPost('role_ids'));

        return redirect()->to('/admin/permissions/' . $id)->with('success', 'Permission updated successfully.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->guard('permissions.manage')) {
            return $redirect;
        }

        $permission = $this->permissionModel->find($id);
        if (!is_array($permission)) {
            return redirect()->to('/admin/permissions')->with('error', 'Permission not found.');
        }

        $this->rolePermissionModel->db->table('role_permissions')->where('permission_id', $id)->delete();
        $this->permissionModel->delete($id);

        return redirect()->to('/admin/permissions')->with('success', 'Permission deleted.');
    }

    /**
     * @param int[] $roleIds
     */
    private function syncRolesForPermission(int $permissionId, array $roleIds): void
    {
        $this->rolePermissionModel->db->table('role_permissions')->where('permission_id', $permissionId)->delete();

        foreach (array_unique(array_filter(array_map('intval', $roleIds))) as $roleId) {
            if ($roleId > 0) {
                $this->rolePermissionModel->assignPermission($roleId, $permissionId);
            }
        }
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

    private function db()
    {
        return \Config\Database::connect();
    }
}
