<?php

namespace App\Controllers;

use App\Libraries\RbacService;
use App\Models\PermissionModel;
use App\Models\RoleModel;
use App\Models\UserModel;

class AdminRoles extends BaseController
{
    protected RoleModel $roleModel;
    protected PermissionModel $permissionModel;
    protected UserModel $userModel;
    protected RbacService $rbac;
    protected \CodeIgniter\Session\Session $session;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
        $this->permissionModel = new PermissionModel();
        $this->userModel = new UserModel();
        $this->rbac = new RbacService();
        $this->session = session();
    }

    public function index()
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        if (!$this->prepareRbac()) {
            return redirect()->to('/dashboard')->with('error', 'Unable to initialize RBAC tables.');
        }

        $search = trim((string) $this->request->getGet('q'));
        $statusFilter = strtolower(trim((string) $this->request->getGet('status')));
        $typeFilter = strtolower(trim((string) $this->request->getGet('type')));

        $builder = $this->roleModel->builder();
        if ($search !== '') {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('description', $search)
                ->groupEnd();
        }
        if (in_array($statusFilter, ['active', 'inactive'], true)) {
            $builder->where('status', $statusFilter);
        }
        if ($typeFilter === 'system') {
            $builder->where('is_system_role', 1);
        } elseif ($typeFilter === 'custom') {
            $builder->where('is_system_role', 0);
        }

        $roles = $builder->orderBy('is_system_role', 'DESC')->orderBy('name', 'ASC')->get()->getResultArray();
        $permissionCounts = $this->getPermissionCountsByRoleId();

        $stats = ['total' => 0, 'active' => 0, 'system' => 0, 'custom' => 0];
        foreach ($roles as &$role) {
            $roleId = (int) ($role['id'] ?? 0);
            $roleName = (string) ($role['name'] ?? '');
            $role['permission_count'] = (int) ($permissionCounts[$roleId] ?? 0);
            $role['user_count'] = $this->countUsersForRole($roleId, $roleName);
            $role['is_system'] = $this->rbac->isSystemRole($role);
            $role['status_label'] = strtolower((string) ($role['status'] ?? 'active')) === 'inactive' ? 'Inactive' : 'Active';

            $stats['total']++;
            if ($role['status_label'] === 'Active') {
                $stats['active']++;
            }
            $role['is_system'] ? $stats['system']++ : $stats['custom']++;
        }
        unset($role);

        return view('admin/roles/index', [
            'roles' => $roles,
            'stats' => $stats,
            'filters' => ['q' => $search, 'status' => $statusFilter, 'type' => $typeFilter],
            'page_title' => 'System Roles',
            'user_name' => $this->session->get('user_name'),
            'user_role' => $this->session->get('user_role'),
        ]);
    }

    public function create()
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }
        if (!$this->prepareRbac()) {
            return redirect()->to('/dashboard')->with('error', 'Unable to initialize RBAC tables.');
        }

        return view('admin/roles/create', [
            'permissions_grouped' => $this->rbac->getPermissionsGrouped(),
            'users' => $this->userModel->select('id, name, email, role')->orderBy('name', 'ASC')->findAll(),
            'page_title' => 'Create Role',
            'user_name' => $this->session->get('user_name'),
            'user_role' => $this->session->get('user_role'),
        ]);
    }

    public function store()
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }
        if (!$this->prepareRbac()) {
            return redirect()->to('/admin/roles')->with('error', 'RBAC tables unavailable.');
        }

        $name = $this->normalizeRoleName((string) $this->request->getPost('name'));
        $description = trim((string) $this->request->getPost('description'));
        $permissionIds = $this->parsePermissionIds($this->request->getPost('permissions'));
        $assignUserIds = $this->parseUserIds($this->request->getPost('user_ids'));

        if (!$this->validate(['name' => 'required|min_length[3]|max_length[100]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        if ($name === '' || $this->roleModel->findByName($name) !== null) {
            return redirect()->back()->withInput()->with('error', 'Invalid or duplicate role name.');
        }
        if (in_array($name, RbacService::SYSTEM_ROLES, true)) {
            return redirect()->back()->withInput()->with('error', 'Reserved system role name.');
        }

        $db = \Config\Database::connect();
        $db->transStart();
        $now = date('Y-m-d H:i:s');
        $this->roleModel->insert([
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'status' => 'active',
            'is_system_role' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $roleId = (int) $this->roleModel->getInsertID();
        $this->syncRolePermissions($roleId, $permissionIds);
        $this->assignUsersToRole($roleId, $name, $assignUserIds);
        $db->transComplete();

        if ($db->transStatus() === false || $roleId <= 0) {
            return redirect()->back()->withInput()->with('error', 'Failed to create role.');
        }

        $this->rbac->logAudit((int) $this->session->get('user_id'), 'role_created', $roleId, [
            'role_name' => $name,
            'permission_ids' => $permissionIds,
            'user_ids' => $assignUserIds,
        ]);

        return redirect()->to('/admin/roles')->with('success', 'Role created successfully.');
    }

    public function permissions(int $id)
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $role = $this->roleModel->find($id);
        if (!is_array($role)) {
            return redirect()->to('/admin/roles')->with('error', 'Role not found.');
        }

        $permissions = $this->getPermissionRowsByRoleId($id);
        $role['is_system'] = $this->rbac->isSystemRole($role);

        return view('admin/roles/permissions', [
            'role' => $role,
            'permissions' => $permissions,
            'permissions_grouped' => $this->groupPermissionRows($permissions),
            'page_title' => 'Role Permissions',
            'user_name' => $this->session->get('user_name'),
            'user_role' => $this->session->get('user_role'),
        ]);
    }

    public function assignUsers(int $id)
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $role = $this->roleModel->find($id);
        if (!is_array($role)) {
            return redirect()->to('/admin/roles')->with('error', 'Role not found.');
        }

        $assignedIds = $this->getAssignedUserIds($id, (string) ($role['name'] ?? ''));

        return view('admin/roles/assign_users', [
            'role' => $role,
            'users' => $this->userModel->select($this->userSelectFields())->orderBy('name', 'ASC')->findAll(),
            'assigned_user_ids' => $assignedIds,
            'page_title' => 'Assign Users',
            'user_name' => $this->session->get('user_name'),
            'user_role' => $this->session->get('user_role'),
        ]);
    }

    public function saveAssignedUsers(int $id)
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $role = $this->roleModel->find($id);
        if (!is_array($role)) {
            return redirect()->to('/admin/roles')->with('error', 'Role not found.');
        }

        $userIds = $this->parseUserIds($this->request->getPost('user_ids'));
        $this->assignUsersToRole($id, (string) ($role['name'] ?? ''), $userIds);

        $this->rbac->logAudit((int) $this->session->get('user_id'), 'role_users_assigned', $id, [
            'role_name' => $role['name'] ?? '',
            'user_ids' => $userIds,
        ]);

        return redirect()->to('/admin/roles/assign-users/' . $id)->with('success', 'Users assigned successfully.');
    }

    public function edit(int $id)
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $role = $this->roleModel->find($id);
        if (!is_array($role)) {
            return redirect()->to('/admin/roles')->with('error', 'Role not found.');
        }

        return view('admin/roles/edit', [
            'role' => $role,
            'permissions_grouped' => $this->rbac->getPermissionsGrouped(),
            'selected_permission_ids' => $this->getPermissionIdsByRoleId($id),
            'is_system' => $this->rbac->isSystemRole($role),
            'page_title' => 'Edit Role',
            'user_name' => $this->session->get('user_name'),
            'user_role' => $this->session->get('user_role'),
        ]);
    }

    public function update(int $id)
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $role = $this->roleModel->find($id);
        if (!is_array($role)) {
            return redirect()->to('/admin/roles')->with('error', 'Role not found.');
        }

        $isSystem = $this->rbac->isSystemRole($role);
        $name = $this->normalizeRoleName((string) $this->request->getPost('name'));
        $description = trim((string) $this->request->getPost('description'));
        $permissionIds = $this->parsePermissionIds($this->request->getPost('permissions'));

        if (!$isSystem) {
            if (!$this->validate(['name' => 'required|min_length[3]|max_length[100]'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
            $existing = $this->roleModel->findByName($name);
            if (is_array($existing) && (int) ($existing['id'] ?? 0) !== $id) {
                return redirect()->back()->withInput()->with('error', 'Role name already exists.');
            }
        } else {
            $name = (string) ($role['name'] ?? '');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $payload = [
            'description' => $description !== '' ? $description : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if (!$isSystem) {
            $payload['name'] = $name;
        }
        $this->roleModel->update($id, $payload);

        $db->table('role_permissions')->where('role_id', $id)->delete();
        $this->syncRolePermissions($id, $permissionIds);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update role.');
        }

        $this->rbac->logAudit((int) $this->session->get('user_id'), 'role_updated', $id, [
            'role_name' => $name,
            'permission_ids' => $permissionIds,
        ]);
        $this->rbac->logAudit((int) $this->session->get('user_id'), 'role_permissions_modified', $id, [
            'permission_ids' => $permissionIds,
        ]);

        return redirect()->to('/admin/roles')->with('success', 'Role updated successfully.');
    }

    public function toggleStatus(int $id)
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $role = $this->roleModel->find($id);
        if (!is_array($role)) {
            return redirect()->to('/admin/roles')->with('error', 'Role not found.');
        }

        if ($this->rbac->isSystemRole($role)) {
            return redirect()->to('/admin/roles')->with(
                'error',
                'System roles (Admin, Customer, Rider) cannot be deactivated or deleted.'
            );
        }

        $current = strtolower((string) ($role['status'] ?? 'active'));
        $next = $current === 'active' ? 'inactive' : 'active';

        $this->roleModel->update($id, [
            'status' => $next,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->rbac->logAudit((int) $this->session->get('user_id'), 'role_deactivated', $id, [
            'role_name' => $role['name'] ?? '',
            'status' => $next,
        ]);

        $message = $next === 'inactive' ? 'Role deactivated successfully.' : 'Role activated successfully.';
        return redirect()->to('/admin/roles')->with('success', $message);
    }

    public function destroy(int $id)
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $role = $this->roleModel->find($id);
        if (!is_array($role)) {
            return redirect()->to('/admin/roles')->with('error', 'Role not found.');
        }

        if ($this->rbac->isSystemRole($role)) {
            return redirect()->to('/admin/roles')->with('error', 'System roles cannot be deleted.');
        }

        $roleName = (string) ($role['name'] ?? '');
        if ($this->countUsersForRole($id, $roleName) > 0) {
            return redirect()->to('/admin/roles')->with('error', 'Remove users from this role before deleting.');
        }

        $db = \Config\Database::connect();
        $db->transStart();
        $db->table('role_permissions')->where('role_id', $id)->delete();
        if ($db->tableExists('user_roles')) {
            $db->table('user_roles')->where('role_id', $id)->delete();
        }
        $this->roleModel->delete($id);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/admin/roles')->with('error', 'Failed to delete role.');
        }

        $this->rbac->logAudit((int) $this->session->get('user_id'), 'role_deleted', $id, ['role_name' => $roleName]);

        return redirect()->to('/admin/roles')->with('success', 'Role deleted successfully.');
    }

    public function view(int $id)
    {
        return redirect()->to('/admin/roles/permissions/' . $id);
    }

    private function prepareRbac(): bool
    {
        if ($this->rbac->tablesAvailable()) {
            $this->rbac->migrateSchemaColumns();
            $this->rbac->syncPermissionsCatalog();
            $this->rbac->ensureSystemRoles();
            $this->rbac->syncSystemRolePermissions();
            $this->rbac->syncUserRoleLinks();
            $this->removeOrphanStaffRole();
            return true;
        }

        return $this->rbac->bootstrap();
    }

    private function removeOrphanStaffRole(): void
    {
        $staff = $this->roleModel->findByName('staff');
        if (!is_array($staff)) {
            return;
        }
        $roleId = (int) ($staff['id'] ?? 0);
        if ($roleId > 0 && $this->countUsersForRole($roleId, 'staff') === 0 && !$this->rbac->isSystemRole($staff)) {
            $db = \Config\Database::connect();
            $db->table('role_permissions')->where('role_id', $roleId)->delete();
            $this->roleModel->delete($roleId);
        }
    }

    private function checkAdminAuth()
    {
        if (
            !$this->session->get('logged_in')
            || in_array(strtolower(trim((string) $this->session->get('user_role'))), ['customer', 'rider'], true)
            || !$this->hasPermission('manage_users')
        ) {
            return redirect()->to('/dashboard')->with('error', 'Access denied. User management permission required.');
        }
        return true;
    }

    private function normalizeRoleName(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', '_', $name) ?? ''));
    }

    /**
     * @param mixed $raw
     * @return int[]
     */
    private function parsePermissionIds($raw): array
    {
        if (!is_array($raw)) {
            return [];
        }
        return array_values(array_unique(array_filter(array_map('intval', $raw), static fn ($id) => $id > 0)));
    }

    /**
     * @param mixed $raw
     * @return int[]
     */
    private function parseUserIds($raw): array
    {
        if (!is_array($raw)) {
            return [];
        }
        return array_values(array_unique(array_filter(array_map('intval', $raw), static fn ($id) => $id > 0)));
    }

    /**
     * @param int[] $permissionIds
     */
    private function syncRolePermissions(int $roleId, array $permissionIds): void
    {
        if ($roleId <= 0 || $permissionIds === []) {
            return;
        }

        $db = \Config\Database::connect();
        $valid = $db->table('permissions')->select('id')->whereIn('id', $permissionIds)->get()->getResultArray();
        $now = date('Y-m-d H:i:s');
        foreach ($valid as $row) {
            $pid = (int) ($row['id'] ?? 0);
            if ($pid <= 0) {
                continue;
            }
            $db->table('role_permissions')->insert([
                'role_id' => $roleId,
                'permission_id' => $pid,
                'created_at' => $now,
            ]);
        }
    }

    /**
     * @param int[] $userIds
     */
    private function assignUsersToRole(int $roleId, string $roleName, array $userIds): void
    {
        if ($roleId <= 0) {
            return;
        }

        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        $assignedBy = (int) ($this->session->get('user_id') ?? 0);

        foreach ($userIds as $userId) {
            $user = $this->userModel->find($userId);
            if (!is_array($user)) {
                continue;
            }

            $update = ['role' => $roleName];
            if ($db->fieldExists('role_id', 'users')) {
                $update['role_id'] = $roleId;
            }
            $db->table('users')->where('id', $userId)->update($update);

            if ($db->tableExists('user_roles')) {
                $exists = $db->table('user_roles')->where('user_id', $userId)->where('role_id', $roleId)->countAllResults();
                if ($exists === 0) {
                    $db->table('user_roles')->insert([
                        'user_id' => $userId,
                        'role_id' => $roleId,
                        'assigned_at' => $now,
                        'assigned_by' => $assignedBy > 0 ? $assignedBy : null,
                    ]);
                }
            }
        }
    }

    /**
     * @return int[]
     */
    private function getAssignedUserIds(int $roleId, string $roleName): array
    {
        $db = \Config\Database::connect();
        $ids = [];

        if ($db->tableExists('user_roles')) {
            $rows = $db->table('user_roles')->select('user_id')->where('role_id', $roleId)->get()->getResultArray();
            foreach ($rows as $row) {
                $ids[] = (int) ($row['user_id'] ?? 0);
            }
        }

        if ($db->fieldExists('role_id', 'users')) {
            $rows = $db->table('users')->select('id')->where('role_id', $roleId)->get()->getResultArray();
            foreach ($rows as $row) {
                $ids[] = (int) ($row['id'] ?? 0);
            }
        } elseif ($roleName !== '') {
            $rows = $db->table('users')->select('id')->where('role', $roleName)->get()->getResultArray();
            foreach ($rows as $row) {
                $ids[] = (int) ($row['id'] ?? 0);
            }
        }

        return array_values(array_unique(array_filter($ids, static fn ($id) => $id > 0)));
    }

    /**
     * @return array<int,int>
     */
    private function getPermissionCountsByRoleId(): array
    {
        $rows = \Config\Database::connect()
            ->table('role_permissions rp')
            ->select('rp.role_id, COUNT(DISTINCT rp.permission_id) AS total', false)
            ->join('permissions p', 'p.id = rp.permission_id', 'inner')
            ->groupBy('rp.role_id')
            ->get()
            ->getResultArray();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) ($row['role_id'] ?? 0)] = (int) ($row['total'] ?? 0);
        }
        return $counts;
    }

    private function countUsersForRole(int $roleId, string $roleName): int
    {
        return count($this->getAssignedUserIds($roleId, $roleName));
    }

    /**
     * @return int[]
     */
    private function getPermissionIdsByRoleId(int $roleId): array
    {
        $rows = \Config\Database::connect()->table('role_permissions')->select('permission_id')->where('role_id', $roleId)->get()->getResultArray();
        return array_values(array_filter(array_map(static fn ($r) => (int) ($r['permission_id'] ?? 0), $rows), static fn ($id) => $id > 0));
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function getPermissionRowsByRoleId(int $roleId): array
    {
        return \Config\Database::connect()
            ->table('role_permissions')
            ->select('permissions.id, permissions.name, permissions.description, permissions.module_name')
            ->join('permissions', 'permissions.id = role_permissions.permission_id', 'inner')
            ->where('role_permissions.role_id', $roleId)
            ->orderBy('permissions.module_name', 'ASC')
            ->orderBy('permissions.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @param array<int,array<string,mixed>> $rows
     * @return array<string, array<int, array<string,mixed>>>
     */
    private function groupPermissionRows(array $rows): array
    {
        $grouped = [];
        foreach ($rows as $row) {
            $module = trim((string) ($row['module_name'] ?? 'General')) ?: 'General';
            $grouped[$module][] = $row;
        }
        return $grouped;
    }

    private function userSelectFields(): string
    {
        $fields = 'id, name, email, role';
        if (\Config\Database::connect()->fieldExists('role_id', 'users')) {
            $fields .= ', role_id';
        }

        return $fields;
    }
}
