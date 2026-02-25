<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - E-Commerce Vape Shop</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
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

        .reset-container {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 2;
        }

        .reset-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .reset-header h1 {
            color: #ffffff;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        .reset-header p {
            color: #f0f0f0;
            font-size: 0.9rem;
            line-height: 1.5;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
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

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            font-size: 1rem;
            color: #ffffff;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
        }

        .form-group input:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.2);
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-group .error {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .password-strength {
            margin-top: 0.5rem;
            height: 5px;
            background-color: #e1e5e9;
            border-radius: 3px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            transition: width 0.3s, background-color 0.3s;
        }

        .strength-weak {
            background-color: #dc3545;
            width: 33%;
        }

        .strength-medium {
            background-color: #ffc107;
            width: 66%;
        }

        .strength-strong {
            background-color: #28a745;
            width: 100%;
        }

        .password-requirements {
            font-size: 0.8rem;
            color: #f0f0f0;
            margin-top: 0.5rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .password-requirements ul {
            margin-left: 1.5rem;
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

        .btn:disabled {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            cursor: not-allowed;
            opacity: 0.6;
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
            font-size: 0.9rem;
        }

        .back-to-login a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <h1>Reset Password</h1>
            <p>Enter your new password below.</p>
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

        <form action="<?= site_url('auth/updatePassword') ?>" method="post" id="resetForm">
            <?= csrf_field() ?>
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            
            <div class="form-group">
                <label for="password">New Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    autocomplete="new-password"
                    placeholder="Enter your new password"
                    minlength="8"
                >
                <div class="password-strength">
                    <div class="password-strength-bar" id="strengthBar"></div>
                </div>
                <div class="password-requirements">
                    <strong>Password must:</strong>
                    <ul>
                        <li>Be at least 8 characters long</li>
                        <li>Contain uppercase and lowercase letters</li>
                        <li>Contain at least one number</li>
                        <li>Contain at least one special character</li>
                    </ul>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required 
                    autocomplete="new-password"
                    placeholder="Confirm your new password"
                >
            </div>

            <button type="submit" class="btn" id="submitBtn">Reset Password</button>
        </form>

        <div class="back-to-login">
            <a href="<?= site_url('login') ?>">← Back to Login</a>
        </div>
    </div>

    <script>
        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const confirmInput = document.getElementById('confirm_password');
        const submitBtn = document.getElementById('submitBtn');

        function checkPasswordStrength(password) {
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            
            // Complexity checks
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            return strength;
        }

        function updateStrengthBar(strength) {
            strengthBar.className = 'password-strength-bar';
            
            if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
            } else if (strength <= 4) {
                strengthBar.classList.add('strength-medium');
            } else {
                strengthBar.classList.add('strength-strong');
            }
        }

        function validatePasswords() {
            const password = passwordInput.value;
            const confirmPassword = confirmInput.value;
            
            if (password !== confirmPassword && confirmPassword !== '') {
                confirmInput.setCustomValidity('Passwords do not match');
            } else {
                confirmInput.setCustomValidity('');
            }
        }

        passwordInput.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            updateStrengthBar(strength);
            validatePasswords();
        });

        confirmInput.addEventListener('input', validatePasswords);

        // Prevent form submission if passwords don't match
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            if (passwordInput.value !== confirmInput.value) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });

        // Auto-hide success messages after 5 seconds
        setTimeout(function() {
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                successAlert.style.display = 'none';
            }
        }, 5000);
    </script>
</body>
</html>
