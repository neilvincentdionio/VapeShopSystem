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
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-dialog {
            background: white;
            border-radius: 10px;
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-body { padding: 1.5rem; }
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
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
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= htmlspecialchars(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error"><?= htmlspecialchars(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <div class="welcome-section">
            <h2>Activity Logs</h2>
            <p>Monitor and track user activities across the system.</p>
            
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
                        <option value="LOGIN_SUCCESS">Login Success</option>
                        <option value="LOGIN_FAILED">Login Failed</option>
                        <option value="LOGOUT">Logout</option>
                        <option value="PROFILE_UPDATE">Profile Update</option>
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
                            <th>Action</th>
                            <th>Action Type</th>
                            <th>Status</th>
                            <th>IP Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentLogs as $log): ?>
                        <tr data-action-type="<?= $log['action_type'] ?>" data-status="<?= $log['status'] ?>" data-date="<?= date('Y-m-d', strtotime($log['created_at'])) ?>">
                            <td><?= date('M j, Y H:i:s', strtotime($log['created_at'])) ?></td>
                            <td>
                                <span style="display: block; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= esc($log['action']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge-info"><?= esc($log['action_type']) ?></span>
                            </td>
                            <td>
                                <?php
                                $badgeClass = $log['status'] === 'success' ? 'badge-success' : 
                                             ($log['status'] === 'failed' ? 'badge-danger' : 'badge-warning');
                                ?>
                                <span class="<?= $badgeClass ?>"><?= esc($log['status']) ?></span>
                            </td>
                            <td><?= esc($log['ip_address']) ?></td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="viewLogDetails(<?= $log['id'] ?>)">
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
    <div class="modal" id="logDetailsModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h5 class="card-title">Activity Log Details</h5>
                <button class="modal-close" onclick="closeModal('logDetailsModal')">&times;</button>
            </div>
            <div class="modal-body" id="logDetailsContent">
                <div style="text-align: center;">
                    <div style="border: 3px solid #f3f3f3; border-top: 3px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto;"></div>
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
        
        function viewLogDetails(logId) {
            // Show modal
            document.getElementById('logDetailsModal').classList.add('show');
            
            // Show loading
            document.getElementById('logDetailsContent').innerHTML = '<div style="text-align: center;"><div style="border: 3px solid #f3f3f3; border-top: 3px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto;"></div></div>';
            
            fetch(`${baseUrl}/admin/get-log-details/${logId}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        document.getElementById('logDetailsContent').innerHTML = `<div class="alert alert-error">${data.message || 'Unknown error'}</div>`;
                        return;
                    }
                    
                    const log = data.log;
                    
                    let html = `
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <h6>Log Information</h6>
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><strong>ID:</strong></td><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;">${log.id}</td></tr>
                                    <tr><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><strong>User ID:</strong></td><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;">${log.user_id || 'N/A'}</td></tr>
                                    <tr><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><strong>Action:</strong></td><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;">${log.action}</td></tr>
                                    <tr><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><strong>Action Type:</strong></td><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><span class="badge-info">${log.action_type}</span></td></tr>
                                    <tr><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><strong>Status:</strong></td><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><span class="${getStatusBadgeClass(log.status)}">${log.status}</span></td></tr>
                                    <tr><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><strong>IP Address:</strong></td><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;">${log.ip_address}</td></tr>
                                    <tr><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><strong>Created At:</strong></td><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;">${new Date(log.created_at).toLocaleString()}</td></tr>
                                </table>
                            </div>
                            <div>
                                <h6>Additional Details</h6>
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><strong>User Agent:</strong></td><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><small>${log.user_agent || 'N/A'}</small></td></tr>
                                    <tr><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><strong>Details:</strong></td><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;">${log.details || 'N/A'}</td></tr>
                                </table>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('logDetailsContent').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('logDetailsContent').innerHTML = '<div class="alert alert-error">Error loading log details</div>';
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
            window.open(`${baseUrl}/admin/export-logs?type=${type}`, '_blank');
        }

        function refreshLogs() {
            location.reload();
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        }
    </script>
</body>
</html>
