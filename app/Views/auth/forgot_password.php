<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/background.css') ?>">
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
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 1rem;
        }


        .forgot-container {
            background: #f9fbfa;
            border: 1px solid rgba(14, 27, 22, 0.08);
            padding: 2rem;
            border-radius: 24px;
            box-shadow: 0 16px 34px rgba(0, 0, 0, 0.12);
            width: 100%;
            max-width: 560px;
            position: relative;
            z-index: 2;
        }

        .forgot-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .forgot-header h1 {
            color: #1a2a24;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .forgot-header p {
            color: #61726b;
            font-size: 1.02rem;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #23352e;
            font-weight: 600;
            font-size: 1.02rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.85rem 0.95rem;
            background: #fdfefe;
            border: 1px solid #d5dfd9;
            border-radius: 12px;
            font-size: 1.06rem;
            color: #1f2e28;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #27c56f;
            background: #ffffff;
            box-shadow: 0 0 15px rgba(39, 197, 111, 0.2);
        }

        .form-group input::placeholder {
            color: #999999;
        }

        .form-group .error {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .btn {
            width: 100%;
            padding: 0.95rem;
            background: #27c56f;
            color: #ffffff;
            border: 1px solid #27c56f;
            border-radius: 12px;
            font-size: 1.08rem;
            font-weight: 700;
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
            padding: 0.75rem;
            border-radius: 5px;
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

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .validation-errors {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .validation-errors ul {
            margin-left: 1.5rem;
        }

        .back-to-login {
            text-align: center;
            margin-top: 1rem;
        }

        .back-to-login a {
            color: #667eea;
            text-decoration: none;
            font-size: 1rem;
        }

        .back-to-login a:hover {
            text-decoration: underline;
        }

        .debug-info {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            padding: 0.75rem;
            border-radius: 5px;
            margin-top: 1rem;
            font-size: 0.8rem;
        }

        .debug-link {
            color: #007bff;
            text-decoration: none;
            word-break: break-all;
        }

        .debug-link:hover {
            text-decoration: underline;
        }

        .debug-link:visited {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <h1>Forgot Password</h1>
            <p>Enter your email address and we'll send you a link to reset your password.</p>
        </div>

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

        <?php if (session()->getFlashdata('debug_link')): ?>
            <div class="debug-info">
                <strong>Debug (Remove in production):</strong><br>
                <div style="margin-top: 0.5rem;">
                    <strong>Reset Link:</strong><br>
                    <a href="<?= htmlspecialchars(session()->getFlashdata('debug_link')) ?>" 
                       class="debug-link" 
                       target="_blank"
                       rel="noopener noreferrer">
                        <?= htmlspecialchars(session()->getFlashdata('debug_link')) ?>
                    </a>
                </div>
                <div style="margin-top: 0.5rem; font-size: 0.75rem; color: #6c757d;">Tip: Click the link above to reset your password</div>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('auth/sendResetLink') ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="<?= old('email') ?>" 
                    required 
                    autocomplete="email"
                    placeholder="Enter your registered email"
                >
            </div>

            <button type="submit" class="btn">Send Reset Link</button>
        </form>

        <div class="back-to-login">
            <a href="<?= site_url('login') ?>">&larr; Back to Login</a>
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

        // Clear form on successful submission
        const form = document.querySelector('form');
        form.addEventListener('submit', function() {
            setTimeout(() => {
                form.reset();
            }, 100);
        });
    </script>
</body>
</html>

