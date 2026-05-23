<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Activity Logs') ?> - Quick Puff Vape Shop System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --main-font: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        body {
            font-family: var(--main-font);
            background: #ffffff;
            min-height: 100vh;
            position: relative;
            color: #333333;
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
        .nav-dropdown-content a { display: block; }
        .customer-actions {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-left: .35rem;
        }
        .customer-action-btn {
            color: #333333;
            text-decoration: none;
            padding: .45rem .85rem;
            border-radius: 999px;
            border: 1px solid #27c56f;
            background: rgba(39, 197, 111, 0.1);
            font-size: .82rem;
            font-weight: 600;
            transition: transform .2s ease, background .2s ease, border-color .2s ease;
        }
        .customer-action-btn:hover {
            transform: translateY(-1px);
            background: linear-gradient(135deg, rgba(255,255,255,.3), rgba(255,255,255,.14));
            border-color: rgba(255, 255, 255, 0.55);
        }
        .nav-right {
            display: flex;
            align-items: center;
            gap: .8rem;
            flex: 0 0 auto;
        }
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

        .container { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; position: relative; z-index: 2; }
        .alert { padding: 1rem; border-radius: 5px; margin-bottom: 1rem; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .welcome-section, .card, .notifications-panel {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,.08);
        }
        .welcome-section { padding: 2rem; margin-bottom: 2rem; }
        .welcome-section h2 { font-size: 1.8rem; margin-bottom: 1rem; color: #333333; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .stat-item {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            padding: 1rem;
            border-radius: 15px;
            text-align: center;
        }
        .stat-value { font-size: 1.5rem; font-weight: 700; margin-bottom: .5rem; }
        .security-alerts-list { display: grid; gap: .75rem; margin-top: 1rem; }
        .security-alert-item {
            border-left: 4px solid #f39c12;
            background: #fff8e1;
            border-radius: 8px;
            padding: .75rem 1rem;
        }
        .security-alert-item.high {
            border-left-color: #dc3545;
            background: #fdecea;
        }
        .security-alert-item .meta {
            font-size: .82rem;
            color: #666666;
            margin-top: .25rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.2rem;
            margin-bottom: 2rem;
        }
        .card { padding: 1.5rem; }
        .card-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
        .card-icon {
            width: 48px; height: 48px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700;
        }
        .card-title { font-size: 1.1rem; font-weight: 600; }
        .card-value { font-size: 2rem; font-weight: 700; color: #27c56f; }
        
        .table-responsive {
            overflow-x: auto;
            border-radius: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        .table th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #e0e0e0;
        }
        .table td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }
        .table tr:hover {
            background: #f8f9fa;
        }
        .badge-success { background: #28a745; color: white; padding: .25rem .5rem; border-radius: 5px; font-size: .8rem; }
        .badge-info { background: #17a2b8; color: white; padding: .25rem .5rem; border-radius: 5px; font-size: .8rem; }
        .badge-warning { background: #ffc107; color: #212529; padding: .25rem .5rem; border-radius: 5px; font-size: .8rem; }
        .badge-danger { background: #dc3545; color: white; padding: .25rem .5rem; border-radius: 5px; font-size: .8rem; }
        .badge-primary { background: #007bff; color: white; padding: .25rem .5rem; border-radius: 5px; font-size: .8rem; }
        .badge-secondary { background: #6c757d; color: white; padding: .25rem .5rem; border-radius: 5px; font-size: .8rem; }
        .log-user-cell { min-width: 160px; }
        .log-user-cell strong { display: block; font-size: .9rem; color: #333; }
        .log-user-cell small { color: #666; font-size: .78rem; }
        .log-action-cell { max-width: 280px; line-height: 1.4; }
        
        .btn {
            padding: .5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-size: .9rem;
            transition: all .3s;
        }
        .btn-primary { background: #007bff; color: white; }
        .btn-info { background: #17a2b8; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-sm { padding: .25rem .5rem; font-size: .8rem; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0,0,0,.2); }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        .form-control, .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: .9rem;
            transition: border-color .3s;
        }
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #27c56f;
            box-shadow: 0 0 0 3px rgba(39, 197, 111, 0.1);
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal.show { display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .modal-dialog {
            background: #fff;
            border-radius: 16px;
            max-width: 920px;
            width: 100%;
            max-height: 92vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
        }
        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e8ecef;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            background: linear-gradient(180deg, #f8fafb 0%, #fff 100%);
        }
        .modal-header .card-title { margin: 0; font-size: 1.15rem; color: #1f2937; }
        .modal-header-sub { font-size: .82rem; color: #6b7280; margin-top: .2rem; }
        .modal-body {
            padding: 1.25rem 1.5rem 1.5rem;
            overflow-y: auto;
        }
        .modal-close {
            background: #f3f4f6;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            font-size: 1.35rem;
            line-height: 1;
            cursor: pointer;
            color: #4b5563;
            flex-shrink: 0;
        }
        .modal-close:hover { background: #e5e7eb; color: #111827; }
        .log-detail-summary {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-bottom: 1rem;
        }
        .log-detail-action-text {
            font-size: 1rem;
            font-weight: 600;
            color: #111827;
            margin: 0 0 .35rem;
            line-height: 1.45;
            word-break: break-word;
        }
        .log-detail-meta {
            font-size: .85rem;
            color: #6b7280;
            margin: 0;
        }
        .log-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }
        .log-detail-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1rem 1.1rem;
        }
        .log-detail-card-wide { grid-column: 1 / -1; }
        .log-detail-card h6 {
            margin: 0 0 .75rem;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #6b7280;
        }
        .log-detail-list {
            margin: 0;
            display: grid;
            gap: .55rem;
        }
        .log-detail-row {
            display: grid;
            grid-template-columns: 110px 1fr;
            gap: .65rem;
            align-items: start;
            font-size: .88rem;
        }
        .log-detail-row dt {
            margin: 0;
            font-weight: 600;
            color: #4b5563;
        }
        .log-detail-row dd {
            margin: 0;
            color: #111827;
            word-break: break-word;
        }
        .log-detail-kv {
            display: grid;
            gap: .5rem;
        }
        .log-detail-kv-item {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: .55rem .7rem;
            background: #fff;
            border: 1px solid #edf0f2;
            border-radius: 8px;
            font-size: .86rem;
        }
        .log-detail-kv-item span:first-child {
            color: #6b7280;
            font-weight: 600;
            flex-shrink: 0;
        }
        .log-detail-kv-item span:last-child {
            color: #111827;
            text-align: right;
            word-break: break-word;
        }
        .log-detail-empty {
            margin: 0;
            padding: .85rem 1rem;
            background: #fff;
            border: 1px dashed #d1d5db;
            border-radius: 8px;
            color: #9ca3af;
            font-size: .86rem;
            text-align: center;
        }
        .log-detail-ua {
            margin: 0;
            padding: .75rem;
            background: #fff;
            border: 1px solid #edf0f2;
            border-radius: 8px;
            font-size: .78rem;
            line-height: 1.45;
            color: #374151;
            word-break: break-word;
            font-family: Consolas, Monaco, monospace;
        }
        .log-detail-loading {
            text-align: center;
            padding: 2.5rem 1rem;
            color: #6b7280;
        }
        .log-detail-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #27c56f;
            border-radius: 50%;
            width: 42px;
            height: 42px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @media (max-width: 768px) {
            .log-detail-grid { grid-template-columns: 1fr; }
            .log-detail-row { grid-template-columns: 1fr; gap: .2rem; }
        }

        @media (max-width: 768px) {
            .navbar-content {
                flex-direction: column;
                align-items: stretch;
                gap: .8rem;
            }
            .navbar-center { justify-content: flex-start; }
            .navbar-menu { flex-wrap: wrap; }
            .customer-actions { width: 100%; margin-left: 0; }
            .nav-right { justify-content: space-between; }
            .container { padding: 0 1rem; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
    <?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
    <?= $this->include('admin/partials/sidebar') ?>

    <div class="container">
        <?php
        $pageHeaderTitle = 'Activity Logs';
        $pageHeaderSubtitle = 'Track system events, user actions, and security-relevant activity.';
        ?>
        <?= $this->include('admin/partials/page_header') ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= htmlspecialchars(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error"><?= htmlspecialchars(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <div class="welcome-section">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value"><?= $activityStats['last_24h'] ?? 0 ?></div>
                    <div>Last 24h</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $activityStats['total'] ?? 0 ?></div>
                    <div>Total Logs</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $failedLoginCount ?? 0 ?></div>
                    <div>Failed Logins</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $successRate ?? 0 ?>%</div>
                    <div>Success Rate</div>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <div class="card-icon" style="background:#fff3cd;color:#8a6d3b;">SA</div>
                <div class="card-title">Security Alerts (Last 24 Hours)</div>
                <div style="margin-left:auto;display:flex;gap:.5rem;">
                    <button class="btn btn-info" onclick="exportSecurityReport('csv')">Export Security Report</button>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;">
                <div class="stat-item">
                    <div class="stat-value"><?= (int)($securitySummary['total_login_attempts'] ?? 0) ?></div>
                    <div>Total Login Attempts</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= (int)($securitySummary['failed_login_attempts'] ?? 0) ?></div>
                    <div>Failed Attempts</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= (int)($securitySummary['suspicious_ips'] ?? 0) ?></div>
                    <div>Suspicious IPs</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= (int)($securitySummary['suspicious_accounts'] ?? 0) ?></div>
                    <div>Targeted Accounts</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= (int)($securitySummary['warning_events'] ?? 0) ?></div>
                    <div>Warning Events</div>
                </div>
            </div>
            <div class="security-alerts-list">
                <?php if (!empty($securityAlerts)): ?>
                    <?php foreach ($securityAlerts as $alert): ?>
                        <div class="security-alert-item <?= esc($alert['severity'] ?? 'medium') ?>">
                            <strong><?= esc($alert['message'] ?? 'Security alert') ?></strong>
                            <div class="meta">
                                Type: <?= esc($alert['type'] ?? 'N/A') ?> |
                                Severity: <?= esc(strtoupper($alert['severity'] ?? 'medium')) ?> |
                                Last Seen: <?= esc($alert['last_seen'] ?? 'N/A') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="security-alert-item" style="border-left-color:#28a745;background:#edf7ed;">
                        <strong>No suspicious activity detected in the last 24 hours.</strong>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filters -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <div class="card-icon" style="background:#f3e5f5;color:#9c27b0;">FL</div>
                <div class="card-title">Filters</div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div>
                    <label for="actionTypeFilter" class="form-label">Action Type</label>
                    <select class="form-select" id="actionTypeFilter" onchange="filterLogs()">
                        <option value="">All Types</option>
                        <?php
                        $actionTypeOptions = \Config\ActivityLogTypes::filterOptions();
                        $inGroup = false;
                        foreach ($actionTypeOptions as $typeKey => $typeLabel):
                            if ($typeLabel === ''):
                                if ($inGroup):
                                    echo '</optgroup>';
                                endif;
                                echo '<optgroup label="' . esc($typeKey) . '">';
                                $inGroup = true;
                                continue;
                            endif;
                        ?>
                        <option value="<?= esc($typeKey) ?>"><?= esc($typeLabel) ?></option>
                        <?php endforeach;
                        if ($inGroup):
                            echo '</optgroup>';
                        endif;
                        ?>
                    </select>
                </div>
                <div>
                    <label for="statusFilter" class="form-label">Status</label>
                    <select class="form-select" id="statusFilter" onchange="filterLogs()">
                        <option value="">All Status</option>
                        <option value="success">Success</option>
                        <option value="failed">Failed</option>
                        <option value="warning">Warning</option>
                    </select>
                </div>
                <div>
                    <label for="dateFilter" class="form-label">Date Range</label>
                    <select class="form-select" id="dateFilter" onchange="filterLogs()">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">Last 7 Days</option>
                        <option value="month">Last 30 Days</option>
                    </select>
                </div>
                <div>
                    <label for="searchFilter" class="form-label">Search</label>
                    <input type="text" class="form-control" id="searchFilter" placeholder="Search..." onkeyup="filterLogs()">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-icon" style="background:#fce4ec;color:#e91e63;">AL</div>
                <div class="card-title">Recent Activity</div>
                <div style="margin-left: auto; display: flex; gap: 0.5rem;">
                    <button class="btn btn-danger" onclick="cleanupLogs()">
                        Cleanup 90d+
                    </button>
                    <button class="btn btn-primary" onclick="refreshLogs()">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                        </svg>
                        Refresh
                    </button>
                    <button class="btn btn-info" onclick="exportLogs('all')">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                        </svg>
                        Export
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table" id="activityLogsTable">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>IP</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $actionBadgeColors = \Config\ActivityLogTypes::badgeColors();
                        foreach ($recentLogs as $log):
                            $typeBadge = 'badge-' . ($actionBadgeColors[$log['action_type'] ?? ''] ?? 'secondary');
                            $statusBadge = $log['status'] === 'success' ? 'badge-success'
                                : ($log['status'] === 'failed' ? 'badge-danger' : 'badge-warning');
                        ?>
                        <tr data-action-type="<?= esc($log['action_type']) ?>" data-status="<?= esc($log['status']) ?>" data-date="<?= date('Y-m-d', strtotime($log['created_at'])) ?>">
                            <td style="white-space:nowrap;font-size:.88rem;"><?= date('M j, Y H:i:s', strtotime($log['created_at'])) ?></td>
                            <td class="log-user-cell">
                                <?php if (! empty($log['user_id'])): ?>
                                    <strong><?= esc($log['user_name'] ?? 'User #' . $log['user_id']) ?></strong>
                                    <small><?= esc($log['user_email'] ?? '') ?></small>
                                    <?php if (! empty($log['user_role'])): ?>
                                        <small style="display:block;color:#27c56f;"><?= esc(ucfirst($log['user_role'])) ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color:#9ca3af;">System</span>
                                <?php endif; ?>
                            </td>
                            <td class="log-action-cell"><?= esc($log['action']) ?></td>
                            <td>
                                <span class="<?= $typeBadge ?>" title="<?= esc($log['action_type']) ?>">
                                    <?= esc($log['action_type_label'] ?? $log['action_type']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="<?= $statusBadge ?>"><?= esc(ucfirst($log['status'])) ?></span>
                            </td>
                            <td style="font-size:.85rem;"><?= esc($log['ip_address'] ?: '—') ?></td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="viewLogDetails(<?= (int) $log['id'] ?>)" title="View details">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Log Details Modal -->
    <div class="modal" id="logDetailsModal" role="dialog" aria-labelledby="logDetailsTitle">
        <div class="modal-dialog">
            <div class="modal-header">
                <div>
                    <h5 class="card-title" id="logDetailsTitle">Activity Log Details</h5>
                    <p class="modal-header-sub">Full event record for audit and support</p>
                </div>
                <button type="button" class="modal-close" onclick="closeModal('logDetailsModal')" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body" id="logDetailsContent">
                <div class="log-detail-loading">
                    <div class="log-detail-spinner"></div>
                    Loading details...
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <script>
        // Base URL for AJAX requests
        const baseUrl = '<?= site_url() ?>';
        const csrfTokenName = '<?= csrf_token() ?>';
        let csrfHash = '<?= csrf_hash() ?>';

        const ACTION_TYPE_LABELS = <?= json_encode(\Config\ActivityLogTypes::labelsMap(), JSON_UNESCAPED_UNICODE) ?>;
        const ACTION_TYPE_COLORS = <?= json_encode(\Config\ActivityLogTypes::badgeColors(), JSON_UNESCAPED_UNICODE) ?>;

        function setCsrfFromResponse(data) {
            if (data && typeof data.csrfHash === 'string' && data.csrfHash !== '') {
                csrfHash = data.csrfHash;
            }
        }

        function escapeHtml(value) {
            if (value === null || value === undefined) {
                return '';
            }
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatDetailLabel(key) {
            return String(key)
                .replace(/_/g, ' ')
                .replace(/\b\w/g, (char) => char.toUpperCase());
        }

        function formatDetailValue(value) {
            if (value === null || value === undefined || value === '') {
                return '—';
            }
            if (typeof value === 'object') {
                return JSON.stringify(value);
            }
            return String(value);
        }

        function renderParsedDetails(parsedDetails) {
            if (!parsedDetails || Object.keys(parsedDetails).length === 0) {
                return '<p class="log-detail-empty">No extra event data for this action.</p>';
            }

            const items = Object.entries(parsedDetails).map(([key, value]) => `
                <div class="log-detail-kv-item">
                    <span>${escapeHtml(formatDetailLabel(key))}</span>
                    <span>${escapeHtml(formatDetailValue(value))}</span>
                </div>
            `).join('');

            return `<div class="log-detail-kv">${items}</div>`;
        }

        function getActionTypeBadgeClass(actionType) {
            const color = ACTION_TYPE_COLORS[actionType] || 'secondary';
            return `badge-${color}`;
        }

        function getActionTypeLabel(actionType, fallbackLabel) {
            return fallbackLabel || ACTION_TYPE_LABELS[actionType] || formatDetailLabel(actionType);
        }

        function buildDetailRow(label, valueHtml) {
            return `
                <div class="log-detail-row">
                    <dt>${escapeHtml(label)}</dt>
                    <dd>${valueHtml}</dd>
                </div>
            `;
        }
        
        function viewLogDetails(logId) {
            document.getElementById('logDetailsModal').classList.add('show');
            document.getElementById('logDetailsContent').innerHTML = `
                <div class="log-detail-loading">
                    <div class="log-detail-spinner"></div>
                    Loading details...
                </div>
            `;
            
            fetch(`${baseUrl}/admin/activity-logs/details/${logId}`)
                .then(response => response.json())
                .then(data => {
                    setCsrfFromResponse(data);
                    if (!data.success || !data.log) {
                        document.getElementById('logDetailsContent').innerHTML =
                            `<div class="alert alert-error">${escapeHtml(data.message || 'Unable to load log details.')}</div>`;
                        return;
                    }
                    
                    const log = data.log;
                    const createdAt = log.created_at ? new Date(log.created_at).toLocaleString() : '—';
                    const typeLabel = getActionTypeLabel(log.action_type, log.action_type_label);
                    const typeBadge = getActionTypeBadgeClass(log.action_type);
                    const statusBadge = getStatusBadgeClass(log.status);
                    const parsedDetails = log.parsed_details || {};
                    const userBlock = log.user_id
                        ? `<strong>${escapeHtml(log.user_name || ('User #' + log.user_id))}</strong>
                           ${log.user_email ? `<br><small>${escapeHtml(log.user_email)}</small>` : ''}
                           ${log.user_role ? `<br><small style="color:#27c56f;">${escapeHtml(String(log.user_role).charAt(0).toUpperCase() + String(log.user_role).slice(1))}</small>` : ''}`
                        : '<span style="color:#9ca3af;">System / Anonymous</span>';

                    const html = `
                        <div class="log-detail-summary">
                            <span class="${typeBadge}">${escapeHtml(typeLabel)}</span>
                            <span class="${statusBadge}">${escapeHtml(String(log.status || '').charAt(0).toUpperCase() + String(log.status || '').slice(1))}</span>
                            <span class="badge-secondary">Log #${escapeHtml(log.id)}</span>
                        </div>
                        <p class="log-detail-action-text">${escapeHtml(log.action || '')}</p>
                        <p class="log-detail-meta">${escapeHtml(createdAt)}</p>

                        <div class="log-detail-grid" style="margin-top:1.25rem;">
                            <section class="log-detail-card">
                                <h6>User</h6>
                                <dl class="log-detail-list">
                                    ${buildDetailRow('Account', userBlock)}
                                    ${buildDetailRow('User ID', escapeHtml(log.user_id || '—'))}
                                </dl>
                            </section>

                            <section class="log-detail-card">
                                <h6>Event</h6>
                                <dl class="log-detail-list">
                                    ${buildDetailRow('Type', `<span class="${typeBadge}">${escapeHtml(typeLabel)}</span>`)}
                                    ${buildDetailRow('Status', `<span class="${statusBadge}">${escapeHtml(log.status || '—')}</span>`)}
                                    ${buildDetailRow('Log ID', escapeHtml(log.id))}
                                </dl>
                            </section>

                            <section class="log-detail-card log-detail-card-wide">
                                <h6>Event Data</h6>
                                ${renderParsedDetails(parsedDetails)}
                            </section>

                            <section class="log-detail-card log-detail-card-wide">
                                <h6>Device &amp; Network</h6>
                                <dl class="log-detail-list">
                                    ${buildDetailRow('IP Address', escapeHtml(log.ip_address || '—'))}
                                </dl>
                                <h6 style="margin-top:1rem;">User Agent</h6>
                                <p class="log-detail-ua">${escapeHtml(log.user_agent || 'Not recorded')}</p>
                            </section>
                        </div>
                    `;
                    
                    document.getElementById('logDetailsContent').innerHTML = html;
                })
                .catch(() => {
                    document.getElementById('logDetailsContent').innerHTML =
                        '<div class="alert alert-error">Error loading log details. Please try again.</div>';
                });
        }

        function filterLogs() {
            const actionType = document.getElementById('actionTypeFilter').value;
            const status = document.getElementById('statusFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;
            const searchTerm = document.getElementById('searchFilter').value.toLowerCase();
            
            const rows = document.querySelectorAll('#activityLogsTable tbody tr');
            
            rows.forEach(row => {
                let show = true;
                
                // Action type filter
                if (actionType && row.dataset.actionType !== actionType) {
                    show = false;
                }
                
                // Status filter
                if (status && row.dataset.status !== status) {
                    show = false;
                }
                
                // Date filter
                if (dateFilter) {
                    const rowDate = row.dataset.date;
                    const today = new Date().toISOString().split('T')[0];
                    
                    switch(dateFilter) {
                        case 'today':
                            if (rowDate !== today) show = false;
                            break;
                        case 'week':
                            const weekAgo = new Date();
                            weekAgo.setDate(weekAgo.getDate() - 7);
                            if (rowDate < weekAgo.toISOString().split('T')[0]) show = false;
                            break;
                        case 'month':
                            const monthAgo = new Date();
                            monthAgo.setMonth(monthAgo.getMonth() - 1);
                            if (rowDate < monthAgo.toISOString().split('T')[0]) show = false;
                            break;
                    }
                }
                
                // Search filter
                if (searchTerm && !row.textContent.toLowerCase().includes(searchTerm)) {
                    show = false;
                }
                
                row.style.display = show ? '' : 'none';
            });
        }

        function getStatusBadgeClass(status) {
            switch(status) {
                case 'success': return 'badge-success';
                case 'failed': return 'badge-danger';
                case 'warning': return 'badge-warning';
                default: return 'badge-info';
            }
        }

        function exportLogs(type = 'all') {
            // Create a temporary form to submit the request with proper session
            const form = document.createElement('form');
            form.method = 'GET';
            form.action = `${baseUrl}/admin/export-logs?type=${type}`;
            form.target = '_blank';

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        function cleanupLogs() {
            if (!confirm('Delete activity logs older than 90 days?')) {
                return;
            }

            const formData = new FormData();
            formData.append(csrfTokenName, csrfHash);

            fetch(`${baseUrl}/admin/activity-logs/cleanup`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                setCsrfFromResponse(data);
                if (data.success) {
                    alert(`Cleaned up ${data.count || 0} old log(s).`);
                    refreshLogs();
                } else {
                    alert(data.message || 'Failed to clean up logs.');
                }
            })
            .catch(error => {
                alert('Error cleaning up logs: ' + error.message);
            });
        }

        function exportSecurityReport(format = 'csv') {
            window.open(`${baseUrl}/admin/security-report?hours=24&format=${format}`, '_blank');
        }

        function refreshLogs() {
            location.reload();
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        // Close modal when clicking outside or pressing Escape
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        };
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal('logDetailsModal');
            }
        });
    </script>
</body>
</html>
