<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - E-Commerce Vape Shop</title>
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
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.2rem;
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

        .navbar-brand {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
            flex: 0 0 auto;
        }

        .navbar-menu a,
        .nav-dropdown-btn {
            color: white;
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

        .navbar-menu a:hover,
        .nav-link.active,
        .nav-dropdown-btn:hover {
            background-color: rgba(255,255,255,.2);
        }

        .nav-dropdown {
            position: relative;
        }

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

        .nav-dropdown:hover .nav-dropdown-content {
            display: block;
        }

        .nav-dropdown-content a {
            display: block;
        }

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
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255,255,255,.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
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

        .btn-danger:hover {
            background-color: #c82333;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 2rem;
            position: relative;
            z-index: 2;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header h1 {
            font-size: 1.8rem;
            color: #ffffff;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        .form-header p {
            color: #f0f0f0;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .user-info {
            background: rgba(255, 255, 255, 0.05);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-info p {
            margin-bottom: 0.5rem;
            color: #f0f0f0;
        }

        .user-info strong {
            color: #ffffff;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #ffffff;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            font-size: 1rem;
            color: #ffffff;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.2);
        }

        .form-group input::placeholder,
        .form-group select::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        /* Fix dropdown option text color - matching theme */
        .form-group select option {
            background: rgba(44, 62, 80, 0.9);
            color: #ffffff;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-group select option:hover,
        .form-group select option:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .form-group .error {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            background-color: rgba(220, 53, 69, 0.2);
            padding: 0.25rem;
            border-radius: 5px;
        }

        .password-hint {
            font-size: 0.8rem;
            color: #f0f0f0;
            margin-top: 0.5rem;
            font-style: italic;
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
            margin-top: 0.5rem;
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

        .validation-errors {
            background-color: rgba(220, 53, 69, 0.2);
            color: #f8d7da;
            border: 1px solid rgba(220, 53, 69, 0.3);
            padding: 0.75rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .validation-errors ul {
            margin-left: 1.5rem;
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

            .form-container {
                padding: 1.5rem;
            }

            .form-header h1 {
                font-size: 1.5rem;
            }

            .navbar-content {
                flex-direction: column;
                align-items: stretch;
                gap: .8rem;
            }

            .navbar-center {
                justify-content: flex-start;
            }

            .navbar-menu {
                flex-wrap: wrap;
            }

            .nav-right {
                justify-content: space-between;
            }
        }
</style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="<?= site_url('dashboard') ?>" class="navbar-brand">
                E-Commerce Vape Shop
            </a>
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

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="validation-errors">
                <strong>Please fix the following errors:</strong>
                <ul>
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <div class="form-header">
                <h1>Edit User Profile</h1>
                <p>Update user information below.</p>
            </div>

            <div class="user-info">
                <p><strong>Current User:</strong> <?= htmlspecialchars($user['name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Role:</strong> <?= htmlspecialchars(ucfirst($user['role'])) ?></p>
                <p><strong>Status:</strong> <?= $user['is_active'] ? 'Active' : 'Inactive' ?></p>
                <p><strong>Created:</strong> <?= date('M d, Y', strtotime($user['created_at'])) ?></p>
            </div>

            <form action="<?= site_url('user-management/update/' . $user['id']) ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="<?= old('name', $user['name']) ?>" 
                        required 
                        autocomplete="name"
                        placeholder="Enter full name"
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?= old('email', $user['email']) ?>" 
                        required 
                        autocomplete="email"
                        placeholder="Enter email address"
                    >
                </div>

                <div class="form-group">
                    <label for="password">New Password (leave blank to keep current)</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        autocomplete="new-password"
                        placeholder="Enter new password (optional)"
                    >
                    <div class="password-hint">Password must be at least 8 characters long</div>
                </div>

                <div class="form-group">
                    <label for="role">User Role</label>
                    <?php 
                    $currentUserId = session()->get('user_id');
                    $isEditingSelf = ($currentUserId == $user['id']);
                    ?>
                    <?php if ($isEditingSelf): ?>
                        <input 
                            type="text" 
                            id="role" 
                            name="role" 
                            value="<?= htmlspecialchars(ucfirst($user['role'])) ?>" 
                            readonly 
                            style="background: rgba(255, 255, 255, 0.05); cursor: not-allowed;"
                        >
                        <small style="color: #ffc107; display: block; margin-top: 0.25rem;">
                            ⚠️ You cannot change your own role for security reasons.
                        </small>
                    <?php else: ?>
                        <select id="role" name="role" required>
                            <option value="admin" <?= old('role', $user['role']) == 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="customer" <?= old('role', $user['role']) == 'customer' ? 'selected' : '' ?>>Customer</option>
                        </select>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn">Update User</button>
            </form>

            <div class="back-link">
                <a href="<?= site_url('user-management') ?>">← Back to User Management</a>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide success messages after 5 seconds
        setTimeout(function() {
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                successAlert.style.display = 'none';
            }
        }, 5000);

        // Real-time validation feedback
        document.getElementById('email').addEventListener('blur', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!emailRegex.test(email)) {
                this.style.borderColor = 'rgba(220, 53, 69, 0.5)';
            } else {
                this.style.borderColor = 'rgba(255, 255, 255, 0.3)';
            }
        });

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            
            if (password.length === 0) {
                this.style.borderColor = 'rgba(255, 255, 255, 0.3)';
                return;
            }
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            
            let color = 'rgba(220, 53, 69, 0.5)'; // Weak
            if (strength >= 3) color = 'rgba(255, 193, 7, 0.5)'; // Medium
            if (strength >= 4) color = 'rgba(40, 167, 69, 0.5)'; // Strong
            
            this.style.borderColor = color;
        });
    </script>
</body>
</html>


