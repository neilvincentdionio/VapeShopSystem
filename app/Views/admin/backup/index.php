<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Database Backup Management') ?> - Quick Puff Vape Shop System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            color: #333333;
        }
        .container-fluid {
            max-width: none;
            margin: 0;
            padding: 1.35rem;
        }
        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }
        .card-header {
            padding: .9rem 1.1rem;
            border-bottom: 1px solid #eef2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .card-title {
            font-size: 1.12rem;
            font-weight: 700;
        }
        .card-copy {
            color: #6b7280;
            font-size: .78rem;
            margin-top: .2rem;
        }
        .card-body {
            padding: 1rem;
        }
        .card-tools {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
        }
        .btn {
            border: 0;
            border-radius: 999px;
            padding: .58rem .85rem;
            font-size: .8rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            font-weight: 600;
            transition: transform .18s ease, box-shadow .18s ease, opacity .18s ease;
        }
        .backup-page .btn {
            border-radius: 999px !important;
            padding: .58rem .85rem !important;
            font-size: .8rem !important;
        }
        .btn-sm { padding: .5rem .75rem; font-size: .76rem; }
        .backup-page .btn-sm { padding: .5rem .75rem !important; font-size: .76rem !important; }
        .btn-primary { background: #27c56f; color: #fff; }
        .btn-warning { background: #f59e0b; color: #fff; }
        .btn-danger { background: #ef4444; color: #fff; }
        .btn-secondary { background: #e5e7eb; color: #111827; }
        .btn:disabled { opacity: .7; cursor: not-allowed; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 10px 20px rgba(15, 23, 42, 0.12); }
        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .mb-4 { margin-bottom: .95rem; }
        .col-12 { width: 100%; }
        .col-md-3 { flex: 1 1 220px; }
        .info-box {
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: .72rem;
            display: flex;
            align-items: center;
            gap: .7rem;
            min-height: 78px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }
        .info-box-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
        }
        .bg-info,
        .bg-success,
        .bg-warning,
        .bg-primary {
            background: #cbd5e1;
            color: #475569;
        }
        .info-box-text {
            display: block;
            color: #6b7280;
            font-size: .7rem;
            margin-bottom: .12rem;
        }
        .info-box-number {
            font-size: .9rem;
            font-weight: 700;
            word-break: break-word;
        }
        .text-sm { font-size: .72rem; }
        .table-responsive { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: .66rem .72rem;
            border-bottom: 1px solid #eef2f7;
            text-align: left;
            vertical-align: middle;
        }
        th {
            font-size: .66rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #6b7280;
            font-weight: 700;
            white-space: nowrap;
        }
        td {
            font-size: .8rem;
            color: #334155;
        }
        .col-filename { width: 44%; }
        .col-actions { width: 220px; text-align: right; }
        .filename {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: .72rem;
            color: #0f172a;
            word-break: break-all;
        }
        .created-at { white-space: nowrap; color: #475569; font-size: .75rem; }
        .text-center { text-align: center; }
        .empty-state {
            text-align: center;
            color: #6b7280;
            padding: 1.4rem 1rem;
            font-size: .92rem;
        }
        .badge {
            display: inline-flex;
            border-radius: 999px;
            padding: .18rem .5rem;
            font-size: .62rem;
            font-weight: 700;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: .03em;
        }
        .badge-success { background: #16a34a; }
        .badge-secondary { background: #64748b; }
        .btn-group {
            display: inline-flex;
            gap: .4rem;
            flex-wrap: nowrap;
            align-items: center;
            justify-content: flex-end;
        }
        .btn-group-sm .btn {
            padding: .42rem .72rem;
            min-width: 78px;
            font-size: .7rem;
            white-space: nowrap;
        }
        .btn-label {
            line-height: 1;
        }
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            z-index: 1000;
            padding: 1rem;
            align-items: center;
            justify-content: center;
        }
        .modal.show { display: flex; }
        .modal-dialog {
            width: 100%;
            max-width: 520px;
        }
        .modal-content {
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(15, 23, 42, 0.2);
        }
        .modal-header, .modal-body, .modal-footer {
            padding: 1rem 1.25rem;
        }
        .modal-header, .modal-footer {
            border-bottom: 1px solid #eef2f7;
        }
        .modal-footer {
            border-bottom: 0;
            border-top: 1px solid #eef2f7;
            display: flex;
            justify-content: flex-end;
            gap: .75rem;
        }
        .form-group { margin-bottom: 1rem; }
        .form-control {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            padding: .75rem .9rem;
            font: inherit;
        }
        .form-text, .text-muted {
            color: #6b7280;
            font-size: .85rem;
        }
        .input-group {
            display: flex;
            gap: .5rem;
            align-items: center;
        }
        .input-group-append .input-group-text {
            display: inline-flex;
            align-items: center;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            padding: .75rem .9rem;
            background: #f8fafc;
            color: #475569;
        }
        .close {
            background: transparent;
            border: 0;
            font-size: 1.5rem;
            cursor: pointer;
            color: #64748b;
        }
        @media (max-width: 768px) {
            .container-fluid {
                padding: 1rem;
            }
            .card-header {
                flex-direction: column;
                align-items: stretch;
            }
            .card-tools {
                width: 100%;
            }
            .card-tools .btn {
                flex: 1 1 auto;
            }
            .btn-group {
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
            }
            .btn-group-sm .btn {
                flex: 1 1 calc(50% - .4rem);
                min-width: 0;
            }
            .col-actions {
                width: auto;
                text-align: left;
            }
            .created-at {
                white-space: normal;
            }
        }
    </style>
<?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body class="backup-page">
<?= $this->include('admin/partials/sidebar') ?>
<div class="container-fluid">
    <?php
    $pageHeaderTitle = 'Database Backup Management';
    $pageHeaderSubtitle = 'Create, download, restore, and delete database backups from one place.';
    ?>
    <?= $this->include('admin/partials/page_header') ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" id="createBackupBtn">Create Backup</button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-database"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Backups</span>
                                    <span class="info-box-number"><?= $stats['total_backups'] ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-hdd"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Size</span>
                                    <span class="info-box-number"><?= $stats['total_size_formatted'] ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Latest Backup</span>
                                    <span class="info-box-number"><?= $stats['latest_backup'] ? date('M d, g:i A', strtotime($stats['latest_backup']['created_at'])) : 'None' ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-folder"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Backup Path</span>
                                    <span class="info-box-number text-sm"><?= basename($stats['backup_path']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Backups Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="col-filename">Filename</th>
                                    <th>Size</th>
                                    <th>Created</th>
                                    <th>Type</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($backups)): ?>
                                    <tr>
                                        <td colspan="5" class="empty-state">No backups found yet. Click <strong>Create Backup</strong> to generate one.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($backups as $backup): ?>
                                        <tr>
                                            <td class="filename"><?= htmlspecialchars($backup['filename']) ?></td>
                                            <td><?= esc(formatBytes((int) $backup['size'])) ?></td>
                                            <td class="created-at"><?= date('M d, Y g:i:s A', strtotime($backup['created_at'])) ?></td>
                                            <td>
                                                <span class="badge badge-<?= $backup['type'] === 'gz' ? 'success' : 'secondary' ?>">
                                                    <?= strtoupper($backup['type']) ?>
                                                </span>
                                            </td>
                                            <td class="col-actions">
                                                <div class="btn-group btn-group-sm" role="group" aria-label="Backup actions">
                                                    <a href="<?= site_url('backup/download/' . $backup['filename']) ?>" 
                                                       class="btn btn-primary" title="Download">
                                                        <span class="btn-label">Download</span>
                                                    </a>
                                                    <button type="button" class="btn btn-warning restore-btn" 
                                                            data-filename="<?= htmlspecialchars($backup['filename']) ?>" 
                                                            title="Restore">
                                                        <span class="btn-label">Restore</span>
                                                    </button>
                                                    <button type="button" class="btn btn-danger delete-btn" 
                                                            data-filename="<?= htmlspecialchars($backup['filename']) ?>" 
                                                            title="Delete">
                                                        <span class="btn-label">Delete</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Backup Modal -->
<div class="modal fade" id="createBackupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Backup</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="createBackupForm">
                    <div class="form-group">
                        <label for="backup_name">Backup Name (optional)</label>
                        <input type="text" class="form-control" id="backup_name" name="backup_name" 
                               placeholder="Leave empty for auto-generated name">
                        <small class="form-text text-muted">
                            If not provided, name will be generated automatically (e.g., backup_2026-04-09_14-00-00)
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmCreateBackup">Create Backup</button>
            </div>
        </div>
    </div>
</div>

<!-- Restore Confirmation Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Restore Database</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to restore the database from this backup?</p>
                <p class="text-danger">
                    <strong>Warning:</strong> This will replace all current data with the backup data. 
                    This action cannot be undone!
                </p>
                <p><strong>Backup file:</strong> <span id="restore_filename"></span></p>
                <div class="form-group" style="margin-top: 1rem;">
                    <label style="display: inline-flex; align-items: center; gap: .55rem; cursor: pointer;">
                        <input type="checkbox" id="create_safety_backup" checked>
                        <span>Create a safety backup before restoring</span>
                    </label>
                    <small class="form-text text-muted">
                        Recommended. A fresh backup of the current database will be created before restore starts.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmRestore">Restore</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Backup</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this backup file?</p>
                <p class="text-danger">
                    <strong>Warning:</strong> This action cannot be undone!
                </p>
                <p><strong>Backup file:</strong> <span id="delete_filename"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfName = '<?= csrf_token() ?>';
    const csrfHash = '<?= csrf_hash() ?>';

    function showModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('show');
        }
    }

    function hideModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('show');
        }
    }

    function postForm(url, payload, onSuccess, onDone, button, buttonText, busyText) {
        if (button) {
            button.disabled = true;
            button.textContent = busyText;
        }

        const body = new URLSearchParams(payload);
        body.append(csrfName, csrfHash);

        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            },
            body: body.toString(),
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (response) {
                if (response.success) {
                    onSuccess(response);
                    return;
                }

                alert('Error: ' + (response.error || response.message || 'Request failed.'));
            })
            .catch(function () {
                alert('Request failed. Please try again.');
            })
            .finally(function () {
                if (button) {
                    button.disabled = false;
                    button.textContent = buttonText;
                }

                if (typeof onDone === 'function') {
                    onDone();
                }
            });
    }

    document.getElementById('createBackupBtn')?.addEventListener('click', function () {
        showModal('createBackupModal');
    });

    document.getElementById('confirmCreateBackup')?.addEventListener('click', function () {
        const button = this;
        const backupName = document.getElementById('backup_name')?.value || '';
        postForm(
            '<?= site_url('backup/create') ?>',
            { backup_name: backupName },
            function () {
                hideModal('createBackupModal');
                window.location.reload();
            },
            null,
            button,
            'Create Backup',
            'Creating...'
        );
    });

    document.querySelectorAll('.restore-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('restore_filename').textContent = this.dataset.filename || '';
            showModal('restoreModal');
        });
    });

    document.getElementById('confirmRestore')?.addEventListener('click', function () {
        const button = this;
        postForm(
            '<?= site_url('backup/restore') ?>',
            {
                backup_file: document.getElementById('restore_filename').textContent,
                create_safety_backup: document.getElementById('create_safety_backup')?.checked ? '1' : '0'
            },
            function (response) {
                hideModal('restoreModal');
                alert(response.message || 'Database restored successfully!');
                window.location.reload();
            },
            null,
            button,
            'Restore',
            'Restoring...'
        );
    });

    document.querySelectorAll('.delete-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('delete_filename').textContent = this.dataset.filename || '';
            showModal('deleteModal');
        });
    });

    document.getElementById('confirmDelete')?.addEventListener('click', function () {
        const button = this;
        postForm(
            '<?= site_url('backup/delete') ?>',
            { backup_file: document.getElementById('delete_filename').textContent },
            function () {
                hideModal('deleteModal');
                window.location.reload();
            },
            null,
            button,
            'Delete',
            'Deleting...'
        );
    });

    document.querySelectorAll('[data-dismiss="modal"], .close').forEach(function (button) {
        button.addEventListener('click', function () {
            const modal = this.closest('.modal');
            if (modal) {
                modal.classList.remove('show');
            }
        });
    });

    document.querySelectorAll('.modal').forEach(function (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                modal.classList.remove('show');
            }
        });
    });
});
</script>

<?php
// Helper function to format bytes
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
?>
</body>
</html>
