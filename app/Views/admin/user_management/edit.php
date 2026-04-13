<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Quick Puff Vape Shop</title>
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
            color: #333333;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            flex: 0 0 auto;
        }

        .navbar-menu a,
        .nav-dropdown-btn {
            color: #333333;
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
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            z-index: 50;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .nav-dropdown:hover .nav-dropdown-content {
            display: block;
        }

        .nav-dropdown-content a {
            display: block;
            color: #333333;
            text-decoration: none;
            padding: .5rem 1rem;
            transition: background-color .3s;
        }

        .nav-dropdown-content a:hover {
            background-color: #f8f9fa;
            color: #27c56f;
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
            color: #333333;
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
            background: #27c56f;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
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
            background: #ffffff;
            border: 1px solid #e0e0e0;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header h1 {
            font-size: 1.8rem;
            color: #333333;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .form-header p {
            color: #666666;
        }

        .user-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border: 1px solid #e0e0e0;
        }

        .user-info p {
            margin-bottom: 0.5rem;
            color: #666666;
        }

        .user-info strong {
            color: #333333;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333333;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            color: #333333;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #27c56f;
            background: #ffffff;
            box-shadow: 0 0 15px rgba(39, 197, 111, 0.2);
        }

        .form-group input::placeholder,
        .form-group select::placeholder {
            color: #999999;
        }

        .password-input-wrap {
            position: relative;
        }

        .password-input-wrap input {
            padding-right: 2.8rem;
        }

        .password-toggle {
            position: absolute;
            right: 0.65rem;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            cursor: pointer;
            color: #666666;
            font-size: 1rem;
            line-height: 1;
        }

        /* Fix dropdown option text color - matching theme */
        .form-group select option {
            background: #ffffff;
            color: #333333;
            border: 1px solid #e0e0e0;
        }

        .form-group select option:hover,
        .form-group select option:focus {
            background: #f8f9fa;
            border-color: #27c56f;
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
            color: #666666;
            margin-top: 0.5rem;
            font-style: italic;
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            background: #27c56f;
            color: #ffffff;
            border: 1px solid #27c56f;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn:hover {
            background: #218838;
            border-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 197, 111, 0.3);
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid #e0e0e0;
            color: #333333;
            margin-top: 0.5rem;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
            border-color: #27c56f;
            color: #27c56f;
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
                    <div class="password-input-wrap">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            autocomplete="new-password"
                            placeholder="Enter new password (optional)"
                        >
                        <button type="button" class="password-toggle" data-target="password" aria-label="Show password">&#128065;</button>
                    </div>
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

        document.querySelectorAll('.password-toggle').forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = button.getAttribute('data-target');
                const input = document.getElementById(targetId);
                if (!input) {
                    return;
                }

                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            });
        });
    </script>
</body>
</html>






