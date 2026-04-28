<?= $this->extend('admin/dashboard/dashboard_layout') ?>

<?= $this->section('title') ?>Session Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Session Management</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" onclick="location.reload()">
                            <i class="fas fa-sync"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Session Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-user-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Active Sessions</span>
                                    <span class="info-box-number"><?= $sessionStats['active'] ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-secondary"><i class="fas fa-user-times"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Inactive Sessions</span>
                                    <span class="info-box-number"><?= $sessionStats['inactive'] ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Expired Sessions</span>
                                    <span class="info-box-number"><?= $sessionStats['expired'] ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Sessions</span>
                                    <span class="info-box-number"><?= $sessionStats['total'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Controls -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <select class="form-control" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="active" <?= selected($statusFilter, 'active') ?>>Active</option>
                                <option value="inactive" <?= selected($statusFilter, 'inactive') ?>>Inactive</option>
                                <option value="expired" <?= selected($statusFilter, 'expired') ?>>Expired</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="userFilter" placeholder="Filter by user email..." value="<?= esc($userFilter) ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary" onclick="filterSessions()">Filter</button>
                            <button type="button" class="btn btn-secondary" onclick="clearFilters()">Clear</button>
                            <button type="button" class="btn btn-warning" onclick="cleanupOldSessions()">Cleanup Old Sessions</button>
                        </div>
                    </div>

                    <!-- Sessions Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Session ID</th>
                                    <th>IP Address</th>
                                    <th>Login Time</th>
                                    <th>Last Activity</th>
                                    <th>Logout Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($sessions)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No sessions found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($sessions as $session): ?>
                                        <tr>
                                            <td>
                                                <?= esc($session['user_name'] ?? 'Unknown') ?>
                                                <br>
                                                <small class="text-muted"><?= esc($session['user_email'] ?? 'N/A') ?></small>
                                            </td>
                                            <td>
                                                <small><?= esc(substr($session['session_id'], 0, 20)) ?>...</small>
                                            </td>
                                            <td><?= esc($session['ip_address'] ?? 'N/A') ?></td>
                                            <td><?= date('M j, Y H:i:s', strtotime($session['login_time'])) ?></td>
                                            <td><?= date('M j, Y H:i:s', strtotime($session['last_activity'])) ?></td>
                                            <td><?= $session['logout_time'] ? date('M j, Y H:i:s', strtotime($session['logout_time'])) : '-' ?></td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'active' => 'success',
                                                    'inactive' => 'secondary',
                                                    'expired' => 'warning'
                                                ];
                                                ?>
                                                <span class="badge badge-<?= $statusClass[$session['status']] ?? 'secondary' ?>">
                                                    <?= ucfirst($session['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" onclick="viewSessionDetails('<?= $session['id'] ?>')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($session['status'] === 'active'): ?>
                                                    <button type="button" class="btn btn-sm btn-warning" onclick="endSession('<?= $session['id'] ?>')">
                                                        <i class="fas fa-stop"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if (isset($pager)): ?>
                        <?= $pager->links() ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Session Details Modal -->
<div class="modal fade" id="sessionDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Session Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="sessionDetailsContent">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function filterSessions() {
    const status = document.getElementById('statusFilter').value;
    const user = document.getElementById('userFilter').value;
    
    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (user) params.append('user', user);
    
    window.location.href = '<?= site_url('/admin/session-logs') ?>?' + params.toString();
}

function clearFilters() {
    window.location.href = '<?= site_url('/admin/session-logs') ?>';
}

function viewSessionDetails(sessionId) {
    fetch('<?= site_url('/admin/session-logs/details/') ?>' + sessionId)
        .then(response => response.json())
        .then(data => {
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <strong>User:</strong> ${data.user_name || 'N/A'}<br>
                        <strong>Email:</strong> ${data.user_email || 'N/A'}<br>
                        <strong>Session ID:</strong> <code>${data.session_id}</code><br>
                        <strong>IP Address:</strong> ${data.ip_address || 'N/A'}<br>
                        <strong>Status:</strong> <span class="badge badge-${data.status === 'active' ? 'success' : (data.status === 'expired' ? 'warning' : 'secondary')}">${data.status}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Login Time:</strong> ${new Date(data.login_time).toLocaleString()}<br>
                        <strong>Last Activity:</strong> ${new Date(data.last_activity).toLocaleString()}<br>
                        <strong>Logout Time:</strong> ${data.logout_time ? new Date(data.logout_time).toLocaleString() : 'N/A'}<br>
                        <strong>Duration:</strong> ${calculateDuration(data.login_time, data.logout_time || data.last_activity)}<br>
                        <strong>User Agent:</strong> <small>${data.user_agent || 'N/A'}</small>
                    </div>
                </div>
            `;
            document.getElementById('sessionDetailsContent').innerHTML = content;
            $('#sessionDetailsModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading session details');
        });
}

function endSession(sessionId) {
    if (confirm('Are you sure you want to end this session?')) {
        fetch('<?= site_url('/admin/session-logs/end/') ?>' + sessionId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Session ended successfully');
                location.reload();
            } else {
                alert('Error ending session: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error ending session');
        });
    }
}

function cleanupOldSessions() {
    if (confirm('Are you sure you want to clean up old sessions (older than 30 days)?')) {
        fetch('<?= site_url('/admin/session-logs/cleanup') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Cleaned up ${data.count} old sessions`);
                location.reload();
            } else {
                alert('Error cleaning up sessions: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error cleaning up sessions');
        });
    }
}

function calculateDuration(startTime, endTime) {
    const start = new Date(startTime);
    const end = new Date(endTime);
    const diff = end - start;
    
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    
    return `${hours}h ${minutes}m`;
}
</script>
<?= $this->endSection() ?>
