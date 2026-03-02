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

        .navbar-menu a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .navbar-menu a:hover {
            background-color: rgba(255,255,255,0.2);
        }

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
            <a href="<?= site_url('dashboard') ?>" class="navbar-brand">
                E-Commerce Vape Shop
            </a>
            <div class="navbar-menu">
                <a href="<?= site_url('dashboard') ?>">Dashboard</a>
                <a href="<?= site_url('user-management') ?>" class="active">User Management</a>
                <a href="<?= site_url('dashboard/profile') ?>">Profile</a>
                <a href="<?= site_url('auth/logout') ?>">Logout</a>
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
