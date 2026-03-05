<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title ?? 'Customer') ?> - E-Commerce Vape Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --main-font: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            --text-main: #f7f9ff;
            --text-muted: #c9d2ea;
            --surface: rgba(8, 18, 36, 0.72);
            --surface-soft: rgba(255, 255, 255, 0.08);
            --border: rgba(255, 255, 255, 0.22);
            --accent: #27c56f;
            --danger: #dc3545;
        }

        body {
            font-family: var(--main-font);
            min-height: 100vh;
            color: var(--text-main);
            background:
                linear-gradient(135deg, rgba(0, 0, 0, 0.2) 0%, rgba(0, 0, 0, 0.4) 100%),
                url('<?= base_url('assets/img/smokebg.jpg') ?>') center/cover no-repeat;
        }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 30;
            background: rgba(5, 13, 27, 0.78);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            padding: .9rem 1.5rem;
        }

        .navbar-content {
            max-width: 1240px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .brand {
            color: #fff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            font-weight: 700;
            letter-spacing: .2px;
            white-space: nowrap;
        }

        .brand-logo {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--accent), #7ef0b2);
            color: #05311a;
            font-size: .85rem;
            font-weight: 800;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .nav-links a {
            color: #fff;
            text-decoration: none;
            padding: .45rem .86rem;
            border-radius: 999px;
            border: 1px solid transparent;
            font-size: .9rem;
            transition: all .2s ease;
        }

        .nav-links a:hover,
        .nav-links a.active {
            border-color: rgba(255, 255, 255, 0.36);
            background: rgba(255, 255, 255, 0.12);
        }

        .nav-links .cart-link {
            border-color: rgba(39, 197, 111, 0.55);
            background: linear-gradient(135deg, rgba(39, 197, 111, 0.34), rgba(39, 197, 111, 0.14));
            font-weight: 600;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: .7rem;
        }

        .user-chip {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .3rem .6rem;
            color: var(--text-muted);
            font-size: .84rem;
        }

        .avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-weight: 700;
        }

        .btn-logout {
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            padding: .45rem .82rem;
            background: var(--danger);
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-size: .9rem;
        }

        .btn-logout:hover { background: #c82333; }

        .container {
            max-width: 1240px;
            margin: 1.5rem auto 2rem;
            padding: 0 1.5rem;
        }

        .alert {
            margin-bottom: 1rem;
            padding: .86rem 1rem;
            border-radius: 10px;
            border: 1px solid transparent;
        }

        .alert-success {
            background: rgba(23, 160, 69, 0.2);
            border-color: rgba(23, 160, 69, 0.48);
            color: #d9ffe8;
        }

        .alert-error {
            background: rgba(220, 53, 69, 0.2);
            border-color: rgba(220, 53, 69, 0.48);
            color: #ffe2e6;
        }

        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1rem;
        }

        .page-grid {
            display: grid;
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .navbar { padding: .85rem .95rem; }
            .navbar-content { flex-direction: column; align-items: stretch; gap: .65rem; }
            .nav-links { justify-content: flex-start; }
            .nav-right { justify-content: space-between; }
            .container { padding: 0 .95rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <a href="<?= site_url('customer/home') ?>" class="brand">
                <span class="brand-logo">Q</span>
                <span>QuickPuff Vape Shop</span>
            </a>

            <div class="nav-links">
                <a href="<?= site_url('customer/home') ?>" class="<?= ($active_page ?? '') === 'home' ? 'active' : '' ?>">Home</a>
                <a href="<?= site_url('customer/products') ?>" class="<?= ($active_page ?? '') === 'products' ? 'active' : '' ?>">Products</a>
                <a href="<?= site_url('customer/orders') ?>" class="<?= ($active_page ?? '') === 'orders' ? 'active' : '' ?>">Orders</a>
                <a href="<?= site_url('customer/cart') ?>" class="cart-link <?= ($active_page ?? '') === 'cart' ? 'active' : '' ?>">Cart &#128722;</a>
                <a href="<?= site_url('dashboard/profile') ?>" class="<?= ($active_page ?? '') === 'profile' ? 'active' : '' ?>">Profile &#128100;</a>
            </div>

            <div class="nav-right">
                <div class="user-chip">
                    <span class="avatar"><?= strtoupper(substr((string) ($user_name ?? ''), 0, 1)) ?></span>
                    <span><?= esc($user_name ?? '') ?></span>
                </div>
                <a href="<?= site_url('auth/logout') ?>" class="btn-logout" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
