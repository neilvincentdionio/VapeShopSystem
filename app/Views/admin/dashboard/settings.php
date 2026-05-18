<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Quick Puff Vape Shop System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --main-font: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body {
            font-family: var(--main-font);
            background: #ffffff;
            min-height: 100vh; position: relative; color: #333333;
        }
        .navbar {
            position: sticky;
            top: 0;
            z-index: 20;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.2rem;
        }
        .navbar-brand {
            color: #333333;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            flex: 0 0 auto;
        }
        .navbar-center {
            flex: 1 1 auto;
            display: flex;
            justify-content: center;
            min-width: 0;
        }
        .navbar-menu { display: flex; align-items: center; gap: .75rem; flex-wrap: nowrap; }
        .navbar-menu a, .nav-dropdown-btn {
            color: #333333;
            text-decoration: none;
            padding: .5rem 1rem;
            border-radius: 5px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-family: inherit;
            font-size: .95rem;
            transition: all .3s;
        }
        .navbar-menu a:hover, .nav-link.active, .nav-dropdown-btn:hover { background-color: #f8f9fa; color: #27c56f; }
        .nav-dropdown { position: relative; }
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
        .nav-dropdown:hover .nav-dropdown-content { display: block; }
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
        .nav-right { display: flex; align-items: center; gap: .8rem; flex: 0 0 auto; }
        .user-info { display: flex; align-items: center; gap: .55rem; color: #333333; }
        .user-name {
            max-width: 170px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: #27c56f;
            color: #ffffff;
            display: flex; align-items: center; justify-content: center; font-weight: 700;
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
        .btn-danger:hover { background-color: #c82333; }
        .container { max-width: 1180px; margin: 1.5rem auto; padding: 0 1rem; position: relative; z-index: 2; }
        .hero {
            background: transparent;
            border: 0;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 0;
            padding: .35rem 0 1rem;
            box-shadow: none;
            margin-bottom: 1rem;
        }
        .hero h1 { font-size: 1.45rem; margin-bottom: .4rem; }
        .hero p { color: #4b5563; font-size: .92rem; }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: .75rem;
            margin-top: .9rem;
        }
        .stat-card {
            border: 0;
            border-left: 3px solid #e5e7eb;
            border-radius: 0;
            background: transparent;
            padding: .25rem .75rem;
        }
        .stat-label { font-size: .72rem; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; margin-bottom: .18rem; }
        .stat-value { font-size: 1.05rem; font-weight: 700; color: #111827; }
        .settings-card {
            background: transparent;
            border: 0;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 0;
            padding: .2rem 0 .95rem;
            box-shadow: none;
            margin-bottom: .95rem;
        }
        .settings-card h2 { font-size: 1rem; margin-bottom: .38rem; }
        .settings-card p { color: #4b5563; font-size: .9rem; margin-bottom: .6rem; }
        .system-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: .6rem;
            margin-top: .45rem;
        }
        .meta-chip {
            border: 1px solid #edf2f7;
            border-radius: 8px;
            background: #fafafa;
            padding: .5rem .6rem;
        }
        .meta-chip .label {
            font-size: .72rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: .15rem;
        }
        .meta-chip .value {
            font-size: .88rem;
            color: #0f172a;
            font-weight: 700;
        }
        .about-lead {
            color: #374151;
            font-size: .92rem;
            line-height: 1.55;
            margin-bottom: .85rem;
            max-width: 72ch;
        }
        .capability-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: .65rem;
            margin-top: .85rem;
        }
        .capability-item {
            border: 1px solid #edf2f7;
            border-radius: 10px;
            background: #fafafa;
            padding: .65rem .75rem;
        }
        .capability-item h3 {
            font-size: .84rem;
            color: #111827;
            margin-bottom: .28rem;
        }
        .capability-item p {
            margin: 0;
            color: #4b5563;
            font-size: .84rem;
            line-height: 1.4;
        }
        @media (max-width: 768px) {
            .navbar-content { flex-direction: column; align-items: stretch; gap: .8rem; }
            .navbar-center { justify-content: flex-start; }
            .navbar-menu { flex-wrap: wrap; }
            .nav-right { justify-content: space-between; }
        }
    </style>
<?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
    <?= $this->include('admin/partials/sidebar') ?>

    <div class="container">
        <?php
        $pageHeaderTitle = 'Settings (Admin)';
        $pageHeaderSubtitle = 'System overview and environment details for QuickPuff administration.';
        ?>
        <?= $this->include('admin/partials/page_header') ?>

        <section class="hero">
            <p>Administration panel for QuickPuff Vapeshop's single-seller e-commerce operations.</p>
            <div class="stats">
                <article class="stat-card">
                    <p class="stat-label">Role</p>
                    <p class="stat-value"><?= esc((string) ($user_role ?? 'admin')) ?></p>
                </article>
                <article class="stat-card">
                    <p class="stat-label">Account</p>
                    <p class="stat-value"><?= esc((string) ($user_name ?? 'Administrator')) ?></p>
                </article>
                <article class="stat-card">
                    <p class="stat-label">Email</p>
                    <p class="stat-value" style="font-size:.9rem;"><?= esc((string) ($user_email ?? 'N/A')) ?></p>
                </article>
            </div>
        </section>

        <article class="settings-card">
            <h2>About System</h2>
            <p class="about-lead">
                <strong>QuickPuff Vapeshop E-Commerce System</strong> powers one QuickPuff store end to end:
                online ordering, product and stock management, customer and rider accounts, delivery tracking,
                and admin auditing. Built for a single seller, it keeps storefront, operations, and security tools in one workspace.
            </p>
            <div class="system-meta">
                <div class="meta-chip">
                    <p class="label">Store</p>
                    <p class="value">QuickPuff (Single Branch)</p>
                </div>
                <div class="meta-chip">
                    <p class="label">Business Model</p>
                    <p class="value">Single Seller</p>
                </div>
                <div class="meta-chip">
                    <p class="label">System Version</p>
                    <p class="value">1.0</p>
                </div>
                <div class="meta-chip">
                    <p class="label">Framework</p>
                    <p class="value">CodeIgniter 4</p>
                </div>
                <div class="meta-chip">
                    <p class="label">Environment</p>
                    <p class="value"><?= esc(ENVIRONMENT) ?></p>
                </div>
                <div class="meta-chip">
                    <p class="label">Supported Roles</p>
                    <p class="value">Admin, Customer, Rider</p>
                </div>
            </div>
            <div class="capability-grid">
                <article class="capability-item">
                    <h3>Orders &amp; Delivery</h3>
                    <p>Checkout to completion with statuses (to pay, to ship, to receive, completed). Admin manages orders and riders; customers track orders; riders handle pickup, delivery proof, and completion.</p>
                </article>
                <article class="capability-item">
                    <h3>Products &amp; Inventory</h3>
                    <p>Catalog, pricing, stock levels, and product visibility (active/inactive) are controlled from the Products module for the storefront.</p>
                </article>
                <article class="capability-item">
                    <h3>Accounts &amp; Access</h3>
                    <p>Role-based access for Admin, Customer, and Rider. New customers need admin approval. Roles and permissions are configured in User Management, Roles, and Permissions.</p>
                </article>
                <article class="capability-item">
                    <h3>Security &amp; Audit</h3>
                    <p>OTP-verified login, Session Logs for sign-ins, Activity Logs for critical actions, and database Backup for maintenance and recovery.</p>
                </article>
            </div>
        </article>

    </div>
</body>
</html>





