<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Database Backup Management</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" id="createBackupBtn">
                            <i class="fas fa-plus"></i> Create Backup
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" id="cleanupBtn">
                            <i class="fas fa-trash"></i> Cleanup Old
                        </button>
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
                                    <span class="info-box-number"><?= $stats['latest_backup'] ? date('M d, H:i', strtotime($stats['latest_backup']['created_at'])) : 'None' ?></span>
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
                                    <th>Filename</th>
                                    <th>Size</th>
                                    <th>Created</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($backups)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No backups found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($backups as $backup): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($backup['filename']) ?></td>
                                            <td><?= $this->formatBytes($backup['size']) ?></td>
                                            <td><?= date('M d, Y H:i:s', strtotime($backup['created_at'])) ?></td>
                                            <td>
                                                <span class="badge badge-<?= $backup['type'] === 'gz' ? 'success' : 'secondary' ?>">
                                                    <?= strtoupper($backup['type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= site_url('backup/download/' . $backup['filename']) ?>" 
                                                       class="btn btn-primary" title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-warning restore-btn" 
                                                            data-filename="<?= htmlspecialchars($backup['filename']) ?>" 
                                                            title="Restore">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger delete-btn" 
                                                            data-filename="<?= htmlspecialchars($backup['filename']) ?>" 
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
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

<!-- Cleanup Modal -->
<div class="modal fade" id="cleanupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cleanup Old Backups</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="cleanupForm">
                    <div class="form-group">
                        <label for="keep_count">Keep Last</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="keep_count" name="keep_count" 
                                   value="10" min="1" max="50">
                            <div class="input-group-append">
                                <span class="input-group-text">backups</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Older backups will be deleted. Only the most recent backups will be kept.
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmCleanup">Cleanup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Create Backup
    $('#createBackupBtn').click(function() {
        $('#createBackupModal').modal('show');
    });

    $('#confirmCreateBackup').click(function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');
        
        $.ajax({
            url: '<?= site_url('backup/create') ?>',
            method: 'POST',
            data: $('#createBackupForm').serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#createBackupModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function() {
                alert('Error creating backup');
            },
            complete: function() {
                btn.prop('disabled', false).html('Create Backup');
            }
        });
    });

    // Restore Backup
    $('.restore-btn').click(function() {
        var filename = $(this).data('filename');
        $('#restore_filename').text(filename);
        $('#restoreModal').modal('show');
    });

    $('#confirmRestore').click(function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Restoring...');
        
        $.ajax({
            url: '<?= site_url('backup/restore') ?>',
            method: 'POST',
            data: {
                backup_file: $('#restore_filename').text()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#restoreModal').modal('hide');
                    alert('Database restored successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function() {
                alert('Error restoring backup');
            },
            complete: function() {
                btn.prop('disabled', false).html('Restore');
            }
        });
    });

    // Delete Backup
    $('.delete-btn').click(function() {
        var filename = $(this).data('filename');
        $('#delete_filename').text(filename);
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').click(function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
        
        $.ajax({
            url: '<?= site_url('backup/delete') ?>',
            method: 'POST',
            data: {
                backup_file: $('#delete_filename').text()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#deleteModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function() {
                alert('Error deleting backup');
            },
            complete: function() {
                btn.prop('disabled', false).html('Delete');
            }
        });
    });

    // Cleanup Old Backups
    $('#cleanupBtn').click(function() {
        $('#cleanupModal').modal('show');
    });

    $('#confirmCleanup').click(function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cleaning...');
        
        $.ajax({
            url: '<?= site_url('backup/cleanup') ?>',
            method: 'POST',
            data: $('#cleanupForm').serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#cleanupModal').modal('hide');
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function() {
                alert('Error cleaning up backups');
            },
            complete: function() {
                btn.prop('disabled', false).html('Cleanup');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>

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
