<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title ?? 'Rider') ?> - QuickPuff Vape Shop</title>
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
            padding: .35rem 1.2rem;
            min-height: 74px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar-content {
            max-width: 1600px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .brand {
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            white-space: nowrap;
            flex: 0 0 auto;
            min-height: 64px;
        }

        .brand-logo-image {
            height: 58px;
            max-height: 100%;
            width: auto;
            display: block;
            object-fit: contain;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
            justify-content: center;
            flex: 1 1 auto;
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

        .nav-right {
            display: flex;
            align-items: center;
            gap: .7rem;
            flex: 0 0 auto;
            justify-content: flex-end;
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
            text-decoration: none;
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

        .container {
            max-width: 1700px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
            font-size: 0.95rem;
        }

        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1rem;
        }

        @media (max-width: 768px) {
            .navbar { padding: .68rem .75rem; }
            .navbar-content { flex-direction: column; align-items: stretch; gap: .55rem; }
            .brand { flex: 0 0 auto; justify-content: center; min-height: 52px; }
            .brand-logo-image {
                height: 46px;
            }
            .nav-links {
                justify-content: flex-start;
                flex-wrap: nowrap;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
                padding-bottom: .2rem;
                gap: .42rem;
            }
            .nav-links a {
                flex: 0 0 auto;
                white-space: nowrap;
                font-size: .82rem;
                padding: .4rem .72rem;
            }
            .nav-right {
                flex: 0 0 auto;
                justify-content: center;
                flex-wrap: wrap;
                gap: .5rem;
            }
            .user-chip {
                max-width: 100%;
                font-size: .8rem;
                padding: .28rem .52rem;
            }
            .btn-logout {
                font-size: .82rem;
                padding: .4rem .72rem;
            }
            .container {
                padding: 1rem .75rem;
                font-size: .92rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <a href="<?= site_url('rider/dashboard') ?>" class="brand">
                <img
                    src="<?= base_url('public/QuickPuff_logo3.png?v=20260528') ?>"
                    alt="QuickPuff Vape Shop"
                    class="brand-logo-image"
                    decoding="async"
                    onerror="if(!this.dataset.fallbackStep){this.dataset.fallbackStep='1';this.src='<?= base_url('public/QuickPuff_logo2.png?v=20260528') ?>';return;}this.onerror=null;this.src='<?= base_url('public/assets/img/quickpuff-logo-green.png?v=20260528') ?>';"
                >
            </a>

            <div class="nav-links">
                <a href="<?= site_url('rider/dashboard') ?>" class="<?= ($active_page ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
                <a href="<?= site_url('rider/deliveries') ?>" class="<?= ($active_page ?? '') === 'deliveries' ? 'active' : '' ?>">My Deliveries</a>
                <a href="<?= site_url('rider/returns') ?>" class="<?= ($active_page ?? '') === 'returns' ? 'active' : '' ?>">Return Pickups</a>
                <a href="<?= site_url('rider/messages') ?>" class="<?= ($active_page ?? '') === 'messages' ? 'active' : '' ?>">Messages</a>
                <a href="<?= site_url('dashboard/profile') ?>" class="<?= ($active_page ?? '') === 'profile' ? 'active' : '' ?>">Profile</a>
            </div>

            <div class="nav-right">
                <?= $this->include('partials/notification_bell') ?>
                <a href="<?= site_url('dashboard/profile') ?>" class="user-chip">
                    <span class="avatar"><?= strtoupper(substr((string) ($user_name ?? 'R'), 0, 1)) ?></span>
                    <span><?= esc($user_name ?? 'Rider') ?></span>
                </a>
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
