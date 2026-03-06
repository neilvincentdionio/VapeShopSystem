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
            --text-main: #333333;
            --text-muted: #666666;
            --surface: #ffffff;
            --surface-soft: #f8f9fa;
            --border: #e0e0e0;
            --accent: #27c56f;
            --danger: #dc3545;
        }

        body {
            font-family: var(--main-font);
            min-height: 100vh;
            color: var(--text-main);
            background: #ffffff;
        }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 30;
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            padding: .9rem 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
            color: #333333;
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
            color: #ffffff;
            font-size: .85rem;
            font-weight: 700;
            box-shadow: 0 2px 4px rgba(39, 197, 111, 0.2);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .nav-links a {
            color: #333333;
            text-decoration: none;
            padding: .45rem .86rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            font-size: .9rem;
            transition: all .2s ease;
        }

        .nav-links a:hover,
        .nav-links a.active {
            border-color: var(--accent);
            background: rgba(39, 197, 111, 0.1);
            color: var(--accent);
        }

        .nav-links .cart-link {
            border-color: rgba(39, 197, 111, 0.55);
            background: linear-gradient(135deg, rgba(39, 197, 111, 0.34), rgba(39, 197, 111, 0.14));
            font-weight: 600;
            position: relative;
        }

        .nav-links .cart-link:hover {
            border-color: var(--accent);
            background: linear-gradient(135deg, rgba(39, 197, 111, 0.44), rgba(39, 197, 111, 0.24));
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(39, 197, 111, 0.2);
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
            background: var(--surface-soft);
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
            background: var(--accent);
            color: #ffffff;
            font-weight: 700;
        }

        .btn-logout {
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            padding: .45rem .82rem;
            background: var(--danger);
            border: 1px solid var(--danger);
            font-size: .9rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-logout:hover { 
            background: #c82333; 
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
        }

        .alert {
            margin-bottom: 1rem;
            padding: .86rem 1rem;
            border-radius: 10px;
            border: 1px solid var(--border);
        }

        .alert-success {
            background: rgba(23, 160, 69, 0.1);
            border-color: rgba(23, 160, 69, 0.3);
            color: #155724;
        }

        .alert-error {
            background: rgba(220, 53, 69, 0.1);
            border-color: rgba(220, 53, 69, 0.3);
            color: #721c24;
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
