<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - E-Commerce Vape Shop</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('<?= base_url('assets/img/smokebg.jpg') ?>') center/cover no-repeat;
            min-height: 100vh;
            position: relative;
            color: #ffffff;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.4) 0%, rgba(0, 0, 0, 0.6) 100%);
            z-index: 1;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1rem 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10;
        }

        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        .navbar-center {
            flex: 1 1 auto;
            display: flex;
            justify-content: center;
            min-width: 0;
        }

        .navbar-menu { 
            display: flex; 
            align-items: center; 
            gap: .75rem; 
            flex-wrap: nowrap; 
        }

        .navbar-menu a, .nav-dropdown-btn {
            color: #fff;
            text-decoration: none;
            padding: .5rem 1rem;
            border-radius: 5px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-family: inherit;
            font-size: .95rem;
            transition: background-color .3s;
        }

        .navbar-menu a:hover, .nav-link.active, .nav-dropdown-btn:hover { 
            background-color: rgba(255,255,255,.2); 
        }

        .nav-dropdown { position: relative; }
        .nav-dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: .5rem;
            min-width: 220px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 12px;
            overflow: hidden;
            z-index: 50;
        }
        .nav-dropdown:hover .nav-dropdown-content { display: block; }
        .nav-dropdown-content a { display: block; }

        .nav-right {
            display: flex;
            align-items: center;
            gap: .8rem;
            flex: 0 0 auto;
        }

        .user-info { 
            display: flex; 
            align-items: center; 
            gap: .55rem; 
            color: #fff; 
        }

        .user-name {
            max-width: 170px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center; font-weight: 700;
        }

        .badge {
            border: 1px solid rgba(255,255,255,.3);
            padding: .2rem .5rem;
            border-radius: 999px;
            font-size: .75rem;
        }

        .btn-danger {
            background-color: #dc3545;
            color: #fff;
            border-radius: 5px;
            padding: .5rem .8rem;
            text-decoration: none;
        }
        .btn-danger:hover { background-color: #c82333; }

        .navbar-menu a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .navbar-menu a:hover,
        .navbar-menu a.active {
            background-color: rgba(255,255,255,0.2);
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
            position: relative;
            z-index: 2;
        }

        .page-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
            color: #ffffff;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        .page-header p {
            color: #f0f0f0;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            gap: 1rem;
        }

        .search-box {
            flex: 1;
            max-width: 300px;
        }

        .search-box input {
            width: 100%;
            padding: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            color: #ffffff;
            backdrop-filter: blur(10px);
        }

        .btn {
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-success {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: #d4edda;
        }

        .btn-success:hover {
            background: rgba(40, 167, 69, 0.3);
        }

        .users-table {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .table th {
            background: rgba(255, 255, 255, 0.05);
            font-weight: 600;
            color: #ffffff;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
            position: sticky;
            top: 0;
            z-index: 5;
        }

        .table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .table td {
            color: #f0f0f0;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .status-active {
            background: rgba(40, 167, 69, 0.2);
            color: #d4edda;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .status-inactive {
            background: rgba(220, 53, 69, 0.2);
            color: #f8d7da;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .role-admin {
            background: rgba(220, 53, 69, 0.2);
            color: #f8d7da;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        
        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .actions a {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            border-radius: 5px;
            text-decoration: none;
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.2);
            color: #d4edda;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .alert-error {
            background-color: rgba(220, 53, 69, 0.2);
            color: #f8d7da;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }

            .actions-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: none;
            }

            .table {
                font-size: 0.9rem;
            }

            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
            }

            .actions {
                flex-direction: column;
            }
        }
</style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="<?= site_url('dashboard') ?>" class="navbar-brand">E-Commerce Vape Shop</a>
            <div class="navbar-center">
                <div class="navbar-menu">
                    <a href="<?= site_url('dashboard') ?>" class="nav-link">Dashboard</a>
                    <?php if (isset($user_role) && $user_role === 'admin'): ?>
                        <a href="<?= site_url('records') ?>" class="nav-link">Records</a>
                        <a href="<?= site_url('user-management') ?>" class="nav-link active">User Management</a>
                    <?php endif; ?>
                    <a href="<?= site_url('dashboard/profile') ?>" class="nav-link">Profile</a>
                    <?php if (isset($user_role) && $user_role === 'admin'): ?>
                        <a href="<?= site_url('dashboard/settings') ?>" class="nav-link">Settings</a>
                    <?php endif; ?>

                    <?php if (isset($user_role) && $user_role === 'admin'): ?>
                        <div class="nav-dropdown">
                            <button class="nav-dropdown-btn">Quick Actions</button>
                            <div class="nav-dropdown-content">
                                <a href="<?= site_url('records/create') ?>">Add Record</a>
                                <a href="<?= site_url('records') ?>">Manage Records</a>
                                <a href="<?= site_url('user-management/create') ?>">Create User</a>
                                <a href="<?= site_url('user-management') ?>">Manage Users</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="nav-right">
                <div class="user-info">
                    <div class="user-avatar"><?= strtoupper(substr($user_name ?? '', 0, 1)) ?></div>
                    <span class="user-name"><?= htmlspecialchars($user_name ?? '') ?></span>
                    <span class="badge"><?= htmlspecialchars(ucfirst($user_role ?? '')) ?></span>
                    <?php if (!empty($user_shop_name)): ?>
                        <span class="badge"><?= htmlspecialchars($user_shop_name) ?></span>
                    <?php endif; ?>
                </div>
                <a href="<?= site_url('auth/logout') ?>" class="btn-danger" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <h1>User Management</h1>
            <p>Manage system users, roles, and permissions</p>
        </div>

        <div class="actions-bar">
            <div class="search-box">
                <input type="text" placeholder="Search users..." id="searchInput" onkeyup="searchUsers()">
            </div>
            <a href="<?= site_url('user-management/create') ?>" class="btn btn-success">
                ➕ Add New User
            </a>
        </div>

        <div class="users-table">
            <?php if (empty($users)): ?>
                <div style="text-align: center; padding: 2rem; color: #f0f0f0;">
                    <h3>No users found</h3>
                    <p>Start by adding your first user account.</p>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($user['name']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="role-badge role-<?= $user['role'] ?>">
                                        <?= htmlspecialchars(ucfirst($user['role'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $user['is_active'] ? 'active' : 'inactive' ?>">
                                        <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $user['last_login'] ? date('M d, Y', strtotime($user['last_login'])) : 'Never' ?>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="<?= site_url('user-management/edit/' . $user['id']) ?>" 
                                           class="btn" 
                                           style="background: rgba(23, 162, 184, 0.2); border-color: rgba(23, 162, 184, 0.3);">
                                            ✏️ Edit
                                        </a>
                                        <?php if ($user['role'] !== 'admin'): ?>
                                        <a href="<?= site_url('user-management/delete/' . $user['id']) ?>" 
                                           class="btn" 
                                           style="background: rgba(220, 53, 69, 0.2); border-color: rgba(220, 53, 69, 0.3);">
                                            🗑️ Delete
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Search functionality
        function searchUsers() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const rows = document.getElementById('userTableBody').getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length; j++) {
                    const cellText = cells[j].textContent || cells[j].innerText;
                    if (cellText.toLowerCase().includes(filter)) {
                        found = true;
                        break;
                    }
                }

                rows[i].style.display = found ? '' : 'none';
            }
        }

        // Auto-hide success messages after 5 seconds
        setTimeout(function() {
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                successAlert.style.display = 'none';
            }
        }, 5000);

        // Auto-hide error messages after 8 seconds
        setTimeout(function() {
            const errorAlert = document.querySelector('.alert-error');
            if (errorAlert) {
                errorAlert.style.display = 'none';
            }
        }, 8000);
    </script>
</body>
</html>


