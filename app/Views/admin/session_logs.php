<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Session Logs') ?> - Quick Puff Vape Shop System</title>
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
            <h2>Session Logs</h2>
            <p>Monitor and manage user sessions in the system.</p>
            
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value"><?= $activeSessions ?? 0 ?></div>
                    <div>Active Sessions</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $sessionStats['total'] ?? 0 ?></div>
                    <div>Total Sessions</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $sessionStats['inactive'] ?? 0 ?></div>
                    <div>Inactive</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $sessionStats['expired'] ?? 0 ?></div>
                    <div>Expired</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-icon" style="background:#e3f2fd;color:#2196f3;">SL</div>
                <div class="card-title">Active Sessions</div>
                <button class="btn btn-primary" onclick="refreshSessions()" style="margin-left: auto;">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                        <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                    </svg>
                    Refresh
                </button>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>IP Address</th>
                            <th>Login Time</th>
                            <th>Last Activity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td><?= esc($session['name'] ?? 'Unknown') ?></td>
                            <td><?= esc($session['email']) ?></td>
                            <td><?= esc($session['ip_address']) ?></td>
                            <td><?= date('M j, Y H:i', strtotime($session['login_time'])) ?></td>
                            <td><?= date('M j, Y H:i', strtotime($session['last_activity'])) ?></td>
                            <td>
                                <span class="badge-success"><?= esc($session['status']) ?></span>
                            </td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="viewSessionDetails('<?= $session['session_id'] ?>')" title="Session ID: <?= $session['session_id'] ?>">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                                    </svg>
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="endSession('<?= $session['session_id'] ?>')">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M5 3.5h6A1.5 1.5 0 0 1 12.5 5v6a1.5 1.5 0 0 1-1.5 1.5H5A1.5 1.5 0 0 1 3.5 11V5A1.5 1.5 0 0 1 5 3.5z"/>
                                        <path d="M4.5 8a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5z"/>
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

    <!-- Session Details Modal -->
    <div class="modal" id="sessionDetailsModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h5 class="card-title">Session Details</h5>
                <button class="modal-close" onclick="closeModal('sessionDetailsModal')">&times;</button>
            </div>
            <div class="modal-body" id="sessionDetailsContent">
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
        
        function viewSessionDetails(sessionId) {
            console.log('Loading session details for:', sessionId);
            
            // Show modal
            document.getElementById('sessionDetailsModal').classList.add('show');
            
            // Show loading
            document.getElementById('sessionDetailsContent').innerHTML = '<div style="text-align: center;"><div style="border: 3px solid #f3f3f3; border-top: 3px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto;"></div></div>';
            
            // Fetch session details
            const encodedSessionId = encodeURIComponent(sessionId);
            fetch(`${baseUrl}/admin/get-session-details/${encodedSessionId}`)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Session data:', data);
                    
                    if (!data.success) {
                        document.getElementById('sessionDetailsContent').innerHTML = `<div class="alert alert-error">${data.message || 'Unknown error'}</div>`;
                        return;
                    }
                    
                    const session = data.session;
                    const userLogs = data.userLogs || [];
                    
                    let html = `
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <h6>Session Information</h6>
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><strong>User ID:</strong></td><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;">${session.user_id || 'N/A'}</td></tr>
                                    <tr><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><strong>Session ID:</strong></td><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><code>${session.session_id || 'N/A'}</code></td></tr>
                                    <tr><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><strong>IP Address:</strong></td><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;">${session.ip_address || 'N/A'}</td></tr>
                                    <tr><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><strong>Login Time:</strong></td><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;">${session.login_time ? new Date(session.login_time).toLocaleString() : 'N/A'}</td></tr>
                                    <tr><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><strong>Last Activity:</strong></td><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;">${session.last_activity ? new Date(session.last_activity).toLocaleString() : 'N/A'}</td></tr>
                                    <tr><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><strong>Status:</strong></td><td style="padding: 0.5rem; border-bottom: 1px solid #f0f0f0;"><span class="badge-success">${session.status || 'N/A'}</span></td></tr>
                                </table>
                            </div>
                            <div>
                                <h6>Recent Activity</h6>
                                <div style="max-height: 200px; overflow-y: auto;">
                    `;
                    
                    if (userLogs.length > 0) {
                        userLogs.forEach(log => {
                            html += `
                                <div style="border-bottom: 1px solid #f0f0f0; padding: 0.5rem 0; margin-bottom: 0.5rem;">
                                    <small style="color: #666;">${log.created_at ? new Date(log.created_at).toLocaleString() : 'N/A'}</small><br>
                                    <strong>${log.action_type || 'N/A'}</strong><br>
                                    ${log.action || 'N/A'}
                                </div>
                            `;
                        });
                    } else {
                        html += '<p style="color: #666;">No recent activity found</p>';
                    }
                    
                    html += `
                                </div>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('sessionDetailsContent').innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading session details:', error);
                    document.getElementById('sessionDetailsContent').innerHTML = '<div class="alert alert-error">Error loading session details: ' + error.message + '</div>';
                });
        }

        function endSession(sessionId) {
            if (!confirm('Are you sure you want to end this session?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('session_id', sessionId);
            
            const encodedSessionId = encodeURIComponent(sessionId);
            
            fetch(`${baseUrl}/admin/end-session/${encodedSessionId}`, {
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
                if (data.success) {
                    alert('Session ended successfully');
                    refreshSessions();
                } else {
                    alert(data.message || 'Failed to end session');
                }
            })
            .catch(error => {
                console.error('Error ending session:', error);
                alert('Error ending session: ' + error.message);
            });
        }

        function refreshSessions() {
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
