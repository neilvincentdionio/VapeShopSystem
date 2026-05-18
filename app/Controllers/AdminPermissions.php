<?php

namespace App\Controllers;

use App\Libraries\RbacService;
use App\Models\PermissionModel;

class AdminPermissions extends BaseController
{
    protected PermissionModel $permissionModel;
    protected \CodeIgniter\Session\Session $session;

    public function __construct()
    {
        $this->permissionModel = new PermissionModel();
        $this->session = session();
    }

    public function index()
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        if (!$this->rbacTablesAvailable() && !$this->bootstrapRbacTables()) {
            return redirect()->to('/dashboard')->with('error', 'Unable to initialize permission tables.');
        }

        $rbac = new RbacService();
        if (!$rbac->tablesAvailable()) {
            $rbac->bootstrap();
        } else {
            $rbac->syncPermissionsCatalog();
        }

        $permissions = $this->permissionModel->orderBy('module_name', 'ASC')->orderBy('name', 'ASC')->findAll();
        $roleCounts = $this->getRoleCountsByPermissionId();
        $permissionsGrouped = [];

        foreach ($permissions as &$permission) {
            $permissionId = (int) ($permission['id'] ?? 0);
            $permission['role_count'] = (int) ($roleCounts[$permissionId] ?? 0);
            $module = trim((string) ($permission['module_name'] ?? '')) ?: 'General';
            $permissionsGrouped[$module][] = $permission;
        }
        unset($permission);

        return view('admin/permissions/index', [
            'permissions' => $permissions,
            'permissions_grouped' => $permissionsGrouped,
            'page_title' => 'System Permissions',
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

        if (!$this->rbacTablesAvailable() && !$this->bootstrapRbacTables()) {
            return redirect()->to('/dashboard')->with('error', 'Unable to initialize permission tables.');
        }

        $modules = array_keys((new RbacService())->getPermissionCatalogByModule());

        return view('admin/permissions/create', [
            'modules' => $modules,
            'page_title' => 'Create Permission',
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

        if (!$this->rbacTablesAvailable()) {
            return redirect()->to('/admin/permissions')->with('error', 'Permission tables are not available.');
        }

        $request = service('request');
        $normalizedName = $this->normalizePermissionName((string) $request->getPost('name'));
        $description = trim((string) $request->getPost('description'));
        $moduleName = trim((string) $request->getPost('module_name'));

        $rules = [
            'name' => 'required|min_length[3]|max_length[100]|regex_match[/^[a-z0-9._-]+$/]',
            'description' => 'permit_empty|max_length[255]',
            'module_name' => 'permit_empty|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        if ($normalizedName === '') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Permission name is required.');
        }

        if ($this->permissionModel->findByName($normalizedName) !== null) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Permission name already exists.');
        }

        $now = date('Y-m-d H:i:s');
        $inserted = $this->permissionModel->insert([
            'name' => $normalizedName,
            'description' => $description !== '' ? htmlspecialchars($description, ENT_QUOTES, 'UTF-8') : null,
            'module_name' => $moduleName !== '' ? $moduleName : null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($inserted === false) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create permission.');
        }

        return redirect()->to('/admin/permissions')
            ->with('success', 'Permission created successfully.');
    }

    public function view(int $id)
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        if (!$this->rbacTablesAvailable()) {
            return redirect()->to('/admin/permissions')->with('error', 'Permission tables are not available.');
        }

        $permission = $this->permissionModel->find($id);
        if (!is_array($permission)) {
            return redirect()->to('/admin/permissions')->with('error', 'Permission not found.');
        }

        $roles = $this->getRolesByPermissionId((int) ($permission['id'] ?? 0));

        return view('admin/permissions/view', [
            'permission' => $permission,
            'roles' => $roles,
            'page_title' => 'View Permission',
            'user_name' => $this->session->get('user_name'),
            'user_role' => $this->session->get('user_role'),
        ]);
    }

    public function edit(int $id)
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        if (!$this->rbacTablesAvailable()) {
            return redirect()->to('/admin/permissions')->with('error', 'Permission tables are not available.');
        }

        $permission = $this->permissionModel->find($id);
        if (!is_array($permission)) {
            return redirect()->to('/admin/permissions')->with('error', 'Permission not found.');
        }

        $modules = array_keys((new RbacService())->getPermissionCatalogByModule());

        return view('admin/permissions/edit', [
            'permission' => $permission,
            'modules' => $modules,
            'page_title' => 'Edit Permission',
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

        if (!$this->rbacTablesAvailable()) {
            return redirect()->to('/admin/permissions')->with('error', 'Permission tables are not available.');
        }

        $permission = $this->permissionModel->find($id);
        if (!is_array($permission)) {
            return redirect()->to('/admin/permissions')->with('error', 'Permission not found.');
        }

        $request = service('request');
        $normalizedName = $this->normalizePermissionName((string) $request->getPost('name'));
        $description = trim((string) $request->getPost('description'));
        $moduleName = trim((string) $request->getPost('module_name'));

        $rules = [
            'name' => 'required|min_length[3]|max_length[100]|regex_match[/^[a-z0-9._-]+$/]',
            'description' => 'permit_empty|max_length[255]',
            'module_name' => 'permit_empty|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        if ($normalizedName === '') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Permission name is required.');
        }

        $existing = $this->permissionModel->findByName($normalizedName);
        if (is_array($existing) && (int) ($existing['id'] ?? 0) !== $id) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Permission name already exists.');
        }

        $updated = $this->permissionModel->update($id, [
            'name' => $normalizedName,
            'description' => $description !== '' ? htmlspecialchars($description, ENT_QUOTES, 'UTF-8') : null,
            'module_name' => $moduleName !== '' ? $moduleName : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($updated === false) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update permission.');
        }

        return redirect()->to('/admin/permissions')->with('success', 'Permission updated successfully.');
    }

    public function destroy(int $id)
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        if (!$this->rbacTablesAvailable()) {
            return redirect()->to('/admin/permissions')->with('error', 'Permission tables are not available.');
        }

        $permission = $this->permissionModel->find($id);
        if (!is_array($permission)) {
            return redirect()->to('/admin/permissions')->with('error', 'Permission not found.');
        }

        $assignedRoles = $this->countRolesForPermission((int) ($permission['id'] ?? 0));
        if ($assignedRoles > 0) {
            return redirect()->to('/admin/permissions')
                ->with('error', 'Cannot delete permission while it is assigned to roles.');
        }

        $db = \Config\Database::connect();
        $db->transStart();
        $db->table('role_permissions')->where('permission_id', (int) $permission['id'])->delete();
        $this->permissionModel->delete($id);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/admin/permissions')->with('error', 'Failed to delete permission.');
        }

        return redirect()->to('/admin/permissions')->with('success', 'Permission deleted successfully.');
    }

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

    private function rbacTablesAvailable(): bool
    {
        $db = \Config\Database::connect();

        return $db->tableExists('permissions')
            && $db->tableExists('role_permissions');
    }

    private function bootstrapRbacTables(): bool
    {
        $db = \Config\Database::connect();

        try {
            if (!$db->tableExists('roles')) {
                $db->query("
                    CREATE TABLE IF NOT EXISTS roles (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100) NOT NULL UNIQUE,
                        description VARCHAR(255) NULL,
                        level INT NOT NULL DEFAULT 100,
                        created_at DATETIME NULL,
                        updated_at DATETIME NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }

            $db->query("
                CREATE TABLE IF NOT EXISTS permissions (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL UNIQUE,
                    description VARCHAR(255) NULL,
                    created_at DATETIME NULL,
                    updated_at DATETIME NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $db->query("
                CREATE TABLE IF NOT EXISTS role_permissions (
                    role_id INT UNSIGNED NOT NULL,
                    permission_id INT UNSIGNED NOT NULL,
                    created_at DATETIME NULL,
                    PRIMARY KEY (role_id, permission_id),
                    CONSTRAINT fk_role_permissions_role_id FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE ON UPDATE CASCADE,
                    CONSTRAINT fk_role_permissions_permission_id FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            return $this->rbacTablesAvailable();
        } catch (\Throwable $exception) {
            log_message('error', 'Permission tables bootstrap failed: {message}', ['message' => $exception->getMessage()]);
            return false;
        }
    }

    private function normalizePermissionName(string $name): string
    {
        $normalized = strtolower(trim($name));
        $normalized = preg_replace('/\s+/', '.', $normalized) ?? '';
        $normalized = preg_replace('/[^a-z0-9._-]/', '', $normalized) ?? '';

        return $normalized;
    }

    /**
     * @return array<int,int>
     */
    private function getRoleCountsByPermissionId(): array
    {
        if (!$this->rbacTablesAvailable() || !\Config\Database::connect()->tableExists('roles')) {
            return [];
        }

        $rows = \Config\Database::connect()
            ->table('role_permissions rp')
            ->select('rp.permission_id, COUNT(DISTINCT rp.role_id) AS total', false)
            ->join('roles r', 'r.id = rp.role_id', 'inner')
            ->groupBy('rp.permission_id')
            ->get()
            ->getResultArray();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) ($row['permission_id'] ?? 0)] = (int) ($row['total'] ?? 0);
        }

        return $counts;
    }

    private function countRolesForPermission(int $permissionId): int
    {
        if ($permissionId <= 0) {
            return 0;
        }

        $row = \Config\Database::connect()
            ->table('role_permissions')
            ->select('COUNT(DISTINCT role_id) AS total', false)
            ->where('permission_id', $permissionId)
            ->get()
            ->getRowArray();

        return (int) ($row['total'] ?? 0);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function getRolesByPermissionId(int $permissionId): array
    {
        if ($permissionId <= 0 || !\Config\Database::connect()->tableExists('roles')) {
            return [];
        }

        return \Config\Database::connect()
            ->table('role_permissions rp')
            ->select('roles.id, roles.name, roles.description')
            ->join('roles', 'roles.id = rp.role_id', 'inner')
            ->where('rp.permission_id', $permissionId)
            ->orderBy('roles.name', 'ASC')
            ->get()
            ->getResultArray();
    }
}
