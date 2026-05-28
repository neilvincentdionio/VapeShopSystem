<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Commerce Vape Shop</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/background.css') ?>">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 1rem;
        }
        .book-login {
            width: min(1120px, 96vw);
            min-height: 640px;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 24px;
            display: flex;
            position: relative;
            overflow: hidden;
            font-size: 1.08rem;
            box-shadow: 0 16px 34px rgba(0,0,0,0.12), inset 10px 0 14px rgba(0,0,0,0.04), inset -10px 0 14px rgba(0,0,0,0.04);
            z-index: 3;
        }
        .book-login::before {
            content: "";
            position: absolute;
            left: 50%; top: 0;
            transform: translateX(-50%);
            width: 42px; height: 100%;
            background: linear-gradient(90deg, rgba(0,0,0,0.02) 0%, rgba(0,0,0,0.08) 48%, rgba(255,255,255,0.35) 52%, rgba(0,0,0,0.02) 100%);
            pointer-events: none;
        }
        .book-page { width: 50%; padding: 2.75rem; position: relative; }
        .book-page-left {
            background: #f8f9fa;
            border-right: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            gap: 0.75rem;
        }
        .brand-wrap {
            width: 100%;
            max-width: 420px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-inline: 0.5rem;
        }
        .brand-logo {
            width: 100%;
            height: auto;
            max-height: 260px;
            object-fit: contain;
            filter: drop-shadow(0 8px 18px rgba(15,30,60,0.14));
        }
        .book-spine-mark { display: none; }
        .login-container { width: 100%; max-width: 100%; display: flex; flex-direction: column; justify-content: center; }
        .login-header { text-align: center; margin-bottom: 2rem; }
        .welcome-title { color: #333; font-size: clamp(1.9rem, 2.3vw, 2.45rem); line-height: 1.1; font-weight: 700; margin-bottom: 0.35rem; }
        .login-header p { color: #666; font-size: 1rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: #333; font-weight: 500; font-size: 1.05rem; }
        .form-group input {
            width: 100%; padding: 0.9rem 1rem;
            background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 10px;
            font-size: 1.1rem; color: #333; transition: all 0.3s;
        }
        .form-group input:focus { outline: none; border-color: #27c56f; background: #fff; box-shadow: 0 0 15px rgba(39,197,111,0.2); }
        .form-group input::placeholder { color: #999; }
        .password-input-wrap { position: relative; }
        .password-input-wrap input { padding-right: 2.8rem; }
        .password-toggle {
            position: absolute; right: 0.65rem; top: 50%; transform: translateY(-50%);
            border: none; background: transparent; cursor: pointer; color: #666; font-size: 1.15rem; line-height: 1;
        }
        .btn {
            width: 100%; padding: 0.95rem; background: #27c56f; color: #fff;
            border: 1px solid #27c56f; border-radius: 10px; font-size: 1.2rem; font-weight: 500; cursor: pointer; transition: all 0.3s;
        }
        .btn:hover { background: #218838; border-color: #218838; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(39,197,111,0.3); }
        .alert { padding: 0.75rem; border-radius: 5px; margin-bottom: 1rem; font-size: 0.9rem; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .forgot-password, .register-link { text-align: center; margin-top: 1rem; }
        .forgot-password a { color: #667eea; text-decoration: none; font-size: 1.05rem; }
        .register-link { margin-top: 0.75rem; }
        .register-link a { color: #27c56f; text-decoration: none; font-size: 1.15rem; font-weight: 600; }
        .validation-errors { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 0.75rem; border-radius: 5px; margin-bottom: 1rem; font-size: 0.9rem; }
        .validation-errors ul { margin-left: 1.5rem; }
        .apk-download-wrap {
            margin-top: 1rem;
            text-align: center;
        }
        .apk-download-btn {
            display: inline-block;
            width: 100%;
            padding: 0.85rem;
            border-radius: 10px;
            border: 1px solid #1f6feb;
            color: #1f6feb;
            text-decoration: none;
            font-size: 1.03rem;
            font-weight: 600;
            transition: all 0.2s ease;
            background: #f7fbff;
        }
        .apk-download-btn:hover {
            background: #1f6feb;
            color: #ffffff;
        }
        @media (max-width: 900px) {
            .book-login { min-height: auto; font-size: 1rem; }
            .book-page { padding: 1.75rem; }
            .brand-wrap { max-width: 360px; }
            .brand-logo { width: 100%; max-height: 220px; }
        }
        @media (max-width: 760px) {
            .book-login { flex-direction: column; }
            .book-login::before { display: none; }
            .book-page { width: 100%; }
            .book-page-left { border-right: 0; border-bottom: 1px solid #e0e0e0; padding-block: 2rem; }
            .brand-wrap { max-width: 360px; padding-inline: 0.5rem; }
            .brand-logo { width: 100%; max-height: 220px; }
            .welcome-title { font-size: clamp(1.8rem, 8vw, 2.2rem); }
        }
    </style>
</head>
<body>
    <div class="book-login">
        <aside class="book-page book-page-left">
            <div class="brand-wrap">
                <img
                    src="<?= base_url('public/QuickPuff_logo3.png?v=20260528') ?>"
                    alt="QuickPuff VapeShop"
                    class="brand-logo"
                    decoding="async"
                    fetchpriority="high"
                    onerror="if(!this.dataset.fallbackStep){this.dataset.fallbackStep='1';this.src='<?= base_url('public/QuickPuff_logo2.png?v=20260528') ?>';return;}this.onerror=null;this.src='<?= base_url('public/assets/img/quickpuff-logo-green.png?v=20260528') ?>';"
                >
            </div>
            <div class="book-spine-mark"></div>
        </aside>

        <section class="book-page">
            <div class="login-container">
                <div class="login-header">
                    <h2 class="welcome-title">Welcome Back!</h2>
                    <p> Sign in to your account</p>
                </div>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success"><?= htmlspecialchars(session()->getFlashdata('success')) ?></div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-error"><?= htmlspecialchars(session()->getFlashdata('error')) ?></div>
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

                <form action="<?= site_url('auth/authenticate') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?= old('email') ?>" required autocomplete="email" placeholder="Enter your email">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-input-wrap">
                            <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
                            <button type="button" class="password-toggle" data-target="password" aria-label="Show password">&#128065;</button>
                        </div>
                    </div>

                    <button type="submit" class="btn">Sign In</button>
                </form>

                <div class="forgot-password"><a href="<?= site_url('forgot-password') ?>">Forgot your password?</a></div>
                <div class="register-link"><a href="<?= site_url('register') ?>">Create a customer account</a></div>
                <div class="apk-download-wrap">
                    <a class="apk-download-btn" href="<?= base_url('downloads/QuickPuffMobile.apk') ?>" download>
                        Download Mobile App (APK)
                    </a>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.querySelectorAll('.password-toggle').forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = button.getAttribute('data-target');
                const input = document.getElementById(targetId);
                if (!input) return;
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            });
        });
    </script>
</body>
</html>
