<?= $this->extend('admin/dashboard/dashboard_layout') ?>

<?= $this->section('title') ?>Activity Logs<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Activity Logs</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" onclick="location.reload()">
                            <i class="fas fa-sync"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Activity Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Success</span>
                                    <span class="info-box-number"><?= $activityStats['by_status']['success']['count'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Failed</span>
                                    <span class="info-box-number"><?= $activityStats['by_status']['failed']['count'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-exclamation"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Warnings</span>
                                    <span class="info-box-number"><?= $activityStats['by_status']['warning']['count'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Last 24h</span>
                                    <span class="info-box-number"><?= $activityStats['last_24h'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Controls -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-control" id="actionFilter">
                                <option value="">All Actions</option>
                                <option value="LOGIN_SUCCESS" <?= selected($actionFilter, 'LOGIN_SUCCESS') ?>>Login Success</option>
                                <option value="LOGIN_FAILED" <?= selected($actionFilter, 'LOGIN_FAILED') ?>>Login Failed</option>
                                <option value="LOGOUT" <?= selected($actionFilter, 'LOGOUT') ?>>Logout</option>
                                <option value="PROFILE_UPDATE" <?= selected($actionFilter, 'PROFILE_UPDATE') ?>>Profile Update</option>
                                <option value="PASSWORD_CHANGE" <?= selected($actionFilter, 'PASSWORD_CHANGE') ?>>Password Change</option>
                                <option value="MFA_ENABLED" <?= selected($actionFilter, 'MFA_ENABLED') ?>>MFA Enabled</option>
                                <option value="MFA_DISABLED" <?= selected($actionFilter, 'MFA_DISABLED') ?>>MFA Disabled</option>
                                <option value="ACCOUNT_CREATED" <?= selected($actionFilter, 'ACCOUNT_CREATED') ?>>Account Created</option>
                                <option value="ACCOUNT_DELETED" <?= selected($actionFilter, 'ACCOUNT_DELETED') ?>>Account Deleted</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="success" <?= selected($statusFilter, 'success') ?>>Success</option>
                                <option value="failed" <?= selected($statusFilter, 'failed') ?>>Failed</option>
                                <option value="warning" <?= selected($statusFilter, 'warning') ?>>Warning</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="userFilter" placeholder="Filter by user email..." value="<?= esc($userFilter) ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary" onclick="filterLogs()">Filter</button>
                            <button type="button" class="btn btn-secondary" onclick="clearFilters()">Clear</button>
                            <button type="button" class="btn btn-warning" onclick="cleanupOldLogs()">Cleanup Old Logs</button>
                        </div>
                    </div>

                    <!-- Activity Logs Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Action Type</th>
                                    <th>IP Address</th>
                                    <th>Status</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No activity logs found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td><?= date('M j, Y H:i:s', strtotime($log['created_at'])) ?></td>
                                            <td>
                                                <?php if ($log['user_id']): ?>
                                                    <?= esc($log['user_name'] ?? 'Unknown') ?>
                                                    <br>
                                                    <small class="text-muted"><?= esc($log['user_email'] ?? 'N/A') ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">System</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc($log['action']) ?></td>
                                            <td>
                                                <?php
                                                $actionColors = [
                                                    'LOGIN_SUCCESS' => 'success',
                                                    'LOGIN_FAILED' => 'danger',
                                                    'LOGOUT' => 'info',
                                                    'PROFILE_UPDATE' => 'primary',
                                                    'PASSWORD_CHANGE' => 'warning',
                                                    'MFA_ENABLED' => 'success',
                                                    'MFA_DISABLED' => 'warning',
                                                    'ACCOUNT_CREATED' => 'success',
                                                    'ACCOUNT_DELETED' => 'danger'
                                                ];
                                                ?>
                                                <span class="badge badge-<?= $actionColors[$log['action_type']] ?? 'secondary' ?>">
                                                    <?= str_replace('_', ' ', $log['action_type']) ?>
                                                </span>
                                            </td>
                                            <td><?= esc($log['ip_address'] ?? 'N/A') ?></td>
                                            <td>
                                                <span class="badge badge-<?= $log['status'] === 'success' ? 'success' : ($log['status'] === 'failed' ? 'danger' : 'warning') ?>">
                                                    <?= ucfirst($log['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($log['details']): ?>
                                                    <button type="button" class="btn btn-sm btn-info" onclick="viewDetails('<?= $log['id'] ?>')">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
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

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="logDetailsContent">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function filterLogs() {
    const action = document.getElementById('actionFilter').value;
    const status = document.getElementById('statusFilter').value;
    const user = document.getElementById('userFilter').value;
    
    const params = new URLSearchParams();
    if (action) params.append('action', action);
    if (status) params.append('status', status);
    if (user) params.append('user', user);
    
    window.location.href = '<?= site_url('/admin/activity-logs') ?>?' + params.toString();
}

function clearFilters() {
    window.location.href = '<?= site_url('/admin/activity-logs') ?>';
}

function viewDetails(logId) {
    fetch('<?= site_url('/admin/activity-logs/details/') ?>' + logId)
        .then(response => response.json())
        .then(data => {
            let detailsHtml = '';
            if (data.details) {
                try {
                    const details = JSON.parse(data.details);
                    detailsHtml = '<pre>' + JSON.stringify(details, null, 2) + '</pre>';
                } catch (e) {
                    detailsHtml = '<pre>' + data.details + '</pre>';
                }
            } else {
                detailsHtml = '<p class="text-muted">No additional details available</p>';
            }
            
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <strong>Timestamp:</strong> ${new Date(data.created_at).toLocaleString()}<br>
                        <strong>User:</strong> ${data.user_name || 'N/A'} (${data.user_email || 'N/A'})<br>
                        <strong>Action:</strong> ${data.action}<br>
                        <strong>Action Type:</strong> ${data.action_type}<br>
                        <strong>Status:</strong> <span class="badge badge-${data.status === 'success' ? 'success' : (data.status === 'failed' ? 'danger' : 'warning')}">${data.status}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>IP Address:</strong> ${data.ip_address || 'N/A'}<br>
                        <strong>User Agent:</strong> <small>${data.user_agent || 'N/A'}</small><br>
                        <strong>User ID:</strong> ${data.user_id || 'N/A'}<br>
                    </div>
                </div>
                <hr>
                <h6>Additional Details:</h6>
                ${detailsHtml}
            `;
            document.getElementById('logDetailsContent').innerHTML = content;
            $('#logDetailsModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading log details');
        });
}

function cleanupOldLogs() {
    if (confirm('Are you sure you want to clean up old logs (older than 90 days)?')) {
        fetch('<?= site_url('/admin/activity-logs/cleanup') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Cleaned up ${data.count} old logs`);
                location.reload();
            } else {
                alert('Error cleaning up logs: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error cleaning up logs');
        });
    }
}
</script>
<?= $this->endSection() ?>
