<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User - E-Commerce Vape Shop</title>
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

        .navbar-menu a {
            color: #333333;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .navbar-menu a:hover {
            background-color: #f8f9fa;
            color: #27c56f;
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

        .user-info-nav { 
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

        .btn-danger-nav {
            background-color: #dc3545;
            color: #fff;
            border-radius: 5px;
            padding: .5rem .8rem;
            text-decoration: none;
        }
        .btn-danger-nav:hover { background-color: #c82333; }

        .container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 0 2rem;
            position: relative;
            z-index: 2;
        }

        .delete-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .delete-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .delete-header h1 {
            font-size: 1.8rem;
            color: #ffffff;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        .delete-header .warning {
            color: #ffc107;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .user-info {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.3);
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .user-info h3 {
            color: #ffffff;
            margin-bottom: 1rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .user-info p {
            margin-bottom: 0.5rem;
            color: #f8d7da;
        }

        .user-info strong {
            color: #ffffff;
        }

        .confirmation-text {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.2);
            border-radius: 10px;
        }

        .confirmation-text p {
            color: #fff3cd;
            font-size: 1.1rem;
            line-height: 1.6;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .btn-danger {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #f8d7da;
        }

        .btn-danger:hover {
            background: rgba(220, 53, 69, 0.3);
            border-color: rgba(220, 53, 69, 0.4);
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
            margin-bottom: 1rem;
        }

        .btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .alert-error {
            background-color: rgba(220, 53, 69, 0.2);
            color: #f8d7da;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .back-link {
            text-align: center;
            margin-top: 1rem;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }

            .delete-container {
                padding: 1.5rem;
            }

            .delete-header h1 {
                font-size: 1.5rem;
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
                <div class="user-info-nav">
                    <div class="user-avatar"><?= strtoupper(substr($user_name ?? '', 0, 1)) ?></div>
                    <span class="user-name"><?= htmlspecialchars($user_name ?? '') ?></span>
                    <span class="badge"><?= htmlspecialchars(ucfirst($user_role ?? '')) ?></span>
                    <?php if (!empty($user_shop_name)): ?>
                        <span class="badge"><?= htmlspecialchars($user_shop_name) ?></span>
                    <?php endif; ?>
                </div>
                <a href="<?= site_url('auth/logout') ?>" class="btn-danger-nav" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <div class="delete-container">
            <div class="delete-header">
                <h1>Delete User Account</h1>
                <div class="warning">⚠️ This action cannot be undone!</div>
            </div>

            <div class="user-info">
                <h3>User Information</h3>
                <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Role:</strong> <?= htmlspecialchars(ucfirst($user['role'])) ?></p>
                <p><strong>Member Since:</strong> <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
                <p><strong>Last Login:</strong> <?= $user['last_login'] ? date('F j, Y g:i A', strtotime($user['last_login'])) : 'Never' ?></p>
            </div>

            <div class="confirmation-text">
                <p>Are you sure you want to delete this user account?</p>
                <p>All data associated with this account will be permanently removed from the system.</p>
                <p>This action is irreversible and cannot be undone.</p>
            </div>

            <form action="<?= site_url('user-management/destroy/' . $user['id']) ?>" method="post" onsubmit="return confirmDelete();">
                <?= csrf_field() ?>
                
                <button type="submit" class="btn btn-danger">
                    🗑️ Yes, Delete This User Account
                </button>
            </form>

            <button type="button" class="btn btn-secondary" onclick="history.back()">
                ← Cancel, Go Back
            </button>
        </div>
    </div>

    <script>
        function confirmDelete() {
            const confirmation = confirm('Are you absolutely sure you want to delete this user?\n\nThis will permanently remove:\n• User account and all data\n• Login history\n• System access\n\nThis action CANNOT be undone!');
            
            if (!confirmation) {
                return false;
            }
            
            const finalConfirmation = confirm('FINAL CONFIRMATION:\nType "DELETE" to confirm the permanent deletion of this user account.');
            
            return finalConfirmation && prompt('Please type "DELETE" to confirm:') === 'DELETE';
        }

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


