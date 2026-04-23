<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Quick Puff Vape Shop</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            position: relative;
            color: #333333;
        }


        .navbar {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
            color: #333333;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
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
            color: #333333;
            text-decoration: none;
            padding: .5rem 1rem;
            border-radius: 5px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-family: inherit;
            font-size: .95rem;
            transition: all .3s;
        }

        .navbar-menu a:hover, .nav-link.active, .nav-dropdown-btn:hover { 
            background-color: #f8f9fa; 
            color: #27c56f;
        }

        .nav-dropdown { position: relative; }
        .nav-dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: .5rem;
            min-width: 220px;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            z-index: 50;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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
            color: #333333; 
        }

        .user-name {
            max-width: 170px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: #27c56f;
            color: #ffffff;
            display: flex; align-items: center; justify-content: center; font-weight: 700;
        }

        .badge {
            border: 1px solid #e0e0e0;
            padding: .2rem .5rem;
            border-radius: 999px;
            font-size: .75rem;
            background: #f8f9fa;
            color: #666666;
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
            color: #333333;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .navbar-menu a:hover,
        .navbar-menu a.active {
            background-color: #f8f9fa;
            color: #27c56f;
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
            color: #333333;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: #666666;
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
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            color: #333333;
        }

        .btn {
            padding: 0.5rem 1rem;
            background: #ffffff;
            color: #333333;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            line-height: 1.1;
            white-space: nowrap;
        }

        .btn:hover {
            background: #f8f9fa;
            border-color: #27c56f;
            color: #27c56f;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-success {
            background: #27c56f;
            border: 1px solid #27c56f;
            color: #ffffff;
        }

        .btn-success:hover {
            background: #218838;
            border-color: #218838;
        }

        .btn-view {
            background: #0ea5e9;
            border-color: #0ea5e9;
            color: #ffffff;
        }

        .btn-view:hover {
            background: #0284c7;
            border-color: #0284c7;
            color: #ffffff;
        }

        .btn-edit {
            background: #d48806;
            border-color: #d48806;
            color: #ffffff;
        }

        .btn-edit:hover {
            background: #b36f00;
            border-color: #b36f00;
            color: #ffffff;
        }

        .btn-delete {
            background: #dc3545;
            border-color: #dc3545;
            color: #ffffff;
        }

        .btn-delete:hover {
            background: #b52a37;
            border-color: #b52a37;
            color: #ffffff;
        }

        .btn-approve {
            background: #1f9d55;
            border-color: #1f9d55;
            color: #ffffff;
        }

        .btn-approve:hover {
            background: #167a42;
            border-color: #167a42;
            color: #ffffff;
        }

        .users-table {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333333;
            position: sticky;
            top: 0;
            z-index: 5;
        }

        .table tr:hover {
            background: #f8f9fa;
        }

        .table td {
            color: #333333;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .approval-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .approval-approved {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .approval-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .approval-rejected {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .role-admin {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        
        .actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .actions form {
            margin: 0;
        }

        .actions a {
            padding: 0.4rem 0.75rem;
            font-size: 0.85rem;
            border-radius: 8px;
            min-width: 52px;
            text-align: center;
        }

        .actions button {
            padding: 0.4rem 0.75rem;
            font-size: 0.85rem;
            border-radius: 8px;
            min-width: 52px;
            text-align: center;
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
<?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
    <!-- Navigation -->
    <?= $this->include('admin/partials/sidebar') ?>

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
                + Add New User
            </a>
        </div>

        <div class="users-table">
            <?php if (empty($users)): ?>
                <div style="text-align: center; padding: 2rem; color: #666666;">
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
                            <th>Approval</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <?php foreach ($users as $user): ?>
                            <?php $approvalStatus = $user['approval_status'] ?? 'approved'; ?>
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
                                    <span class="approval-badge approval-<?= htmlspecialchars($approvalStatus) ?>">
                                        <?= htmlspecialchars(ucfirst($approvalStatus)) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $user['last_login'] ? date('M d, Y', strtotime($user['last_login'])) : 'Never' ?>
                                </td>
                                <td>
                                    <div class="actions">
                                        <?php if ($user['role'] === 'customer'): ?>
                                            <a href="<?= site_url('user-management/view/' . $user['id']) ?>" 
                                               class="btn btn-view">
                                                View
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($user['role'] === 'customer' && $approvalStatus === 'pending'): ?>
                                            <form action="<?= site_url('user-management/approve/' . $user['id']) ?>" method="post">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-approve">Approve</button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="<?= site_url('user-management/edit/' . $user['id']) ?>" 
                                           class="btn btn-edit">
                                            Edit
                                        </a>
                                        <?php if ($user['role'] !== 'admin'): ?>
                                        <a href="<?= site_url('user-management/delete/' . $user['id']) ?>" 
                                           class="btn btn-delete">
                                            Delete
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

