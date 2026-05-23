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
                                <?php
                                $actionTypeOptions = \Config\ActivityLogTypes::filterOptions();
                                $inGroup = false;
                                foreach ($actionTypeOptions as $typeKey => $typeLabel):
                                    if ($typeLabel === ''):
                                        if ($inGroup) {
                                            echo '</optgroup>';
                                        }
                                        echo '<optgroup label="' . esc($typeKey) . '">';
                                        $inGroup = true;
                                        continue;
                                    endif;
                                ?>
                                <option value="<?= esc($typeKey) ?>" <?= selected($actionFilter, $typeKey) ?>><?= esc($typeLabel) ?></option>
                                <?php endforeach;
                                if ($inGroup) {
                                    echo '</optgroup>';
                                }
                                ?>
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
                                                $actionColors = \Config\ActivityLogTypes::badgeColors();
                                                ?>
                                                <span class="badge badge-<?= $actionColors[$log['action_type']] ?? 'secondary' ?>">
                                                    <?= esc($log['action_type_label'] ?? \Config\ActivityLogTypes::label($log['action_type'])) ?>
                                                </span>
                                            </td>
                                            <td><?= esc($log['ip_address'] ?? 'N/A') ?></td>
                                            <td>
                                                <span class="badge badge-<?= $log['status'] === 'success' ? 'success' : ($log['status'] === 'failed' ? 'danger' : 'warning') ?>">
                                                    <?= ucfirst($log['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" onclick="viewDetails(<?= (int) $log['id'] ?>)">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
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

const ACTION_TYPE_LABELS = <?= json_encode(\Config\ActivityLogTypes::labelsMap(), JSON_UNESCAPED_UNICODE) ?>;

function escapeHtml(value) {
    if (value === null || value === undefined) return '';
    return String(value)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function formatDetailLabel(key) {
    return String(key).replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}

function renderParsedDetails(parsedDetails) {
    if (!parsedDetails || Object.keys(parsedDetails).length === 0) {
        return '<p class="text-muted mb-0">No extra event data for this action.</p>';
    }
    let rows = '';
    for (const [key, value] of Object.entries(parsedDetails)) {
        const display = (value === null || value === '') ? '—' : (typeof value === 'object' ? JSON.stringify(value) : String(value));
        rows += `<tr><th style="width:38%">${escapeHtml(formatDetailLabel(key))}</th><td>${escapeHtml(display)}</td></tr>`;
    }
    return `<table class="table table-sm table-bordered mb-0"><tbody>${rows}</tbody></table>`;
}

function viewDetails(logId) {
    document.getElementById('logDetailsContent').innerHTML = '<div class="text-center py-4 text-muted">Loading...</div>';
    $('#logDetailsModal').modal('show');

    fetch('<?= site_url('/admin/activity-logs/details/') ?>' + logId)
        .then(response => response.json())
        .then(payload => {
            const log = payload.log || payload.data;
            if (!payload.success || !log) {
                document.getElementById('logDetailsContent').innerHTML =
                    `<div class="alert alert-danger mb-0">${escapeHtml(payload.message || 'Unable to load log.')}</div>`;
                return;
            }

            const typeLabel = log.action_type_label || ACTION_TYPE_LABELS[log.action_type] || log.action_type;
            const statusClass = log.status === 'success' ? 'success' : (log.status === 'failed' ? 'danger' : 'warning');
            const userHtml = log.user_id
                ? `${escapeHtml(log.user_name || ('User #' + log.user_id))}<br><small class="text-muted">${escapeHtml(log.user_email || '')}</small>`
                : '<span class="text-muted">System</span>';

            document.getElementById('logDetailsContent').innerHTML = `
                <div class="mb-3">
                    <span class="badge badge-info mr-1">${escapeHtml(typeLabel)}</span>
                    <span class="badge badge-${statusClass}">${escapeHtml(log.status)}</span>
                    <span class="badge badge-secondary">#${escapeHtml(log.id)}</span>
                    <h6 class="mt-2 mb-1">${escapeHtml(log.action)}</h6>
                    <small class="text-muted">${escapeHtml(new Date(log.created_at).toLocaleString())}</small>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted small">User</h6>
                        <p class="mb-3">${userHtml}</p>
                        <p class="mb-0"><strong>User ID:</strong> ${escapeHtml(log.user_id || '—')}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted small">Network</h6>
                        <p class="mb-1"><strong>IP:</strong> ${escapeHtml(log.ip_address || '—')}</p>
                        <p class="mb-0 small text-break"><strong>User Agent:</strong><br>${escapeHtml(log.user_agent || '—')}</p>
                    </div>
                </div>
                <hr>
                <h6 class="text-uppercase text-muted small">Event Data</h6>
                ${renderParsedDetails(log.parsed_details || {})}
            `;
        })
        .catch(() => {
            document.getElementById('logDetailsContent').innerHTML =
                '<div class="alert alert-danger mb-0">Error loading log details.</div>';
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
