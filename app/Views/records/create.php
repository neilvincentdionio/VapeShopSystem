<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= site_url('/dashboard') ?>">Lab Exercise 3</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('/dashboard') ?>">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('/records') ?>">
                            <i class="bi bi-file-text"></i> Records
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= esc($user_name) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= site_url('/dashboard/profile') ?>">Profile</a></li>
                            <li><a class="dropdown-item" href="<?= site_url('/dashboard/settings') ?>">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= site_url('/auth/logout') ?>">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= site_url('/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= site_url('/records') ?>">Records</a></li>
                <li class="breadcrumb-item active">Create New Record</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="bi bi-plus-circle"></i> Create New Record</h2>
                <p class="text-muted">Fill in the form below to create a new record.</p>
            </div>
        </div>

        <!-- Create Form -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-file-text"></i> Record Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= session()->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?= site_url('/records/store') ?>" id="createRecordForm">
                            <?= csrf_field() ?>
                            
                            <!-- Title Field -->
                            <div class="mb-3">
                                <label for="title" class="form-label">
                                    Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control <?= isset($validation) && $validation->hasError('title') ? 'is-invalid' : '' ?>" 
                                       id="title" 
                                       name="title" 
                                       value="<?= old('title') ?>" 
                                       placeholder="Enter record title"
                                       required>
                                <?php if (isset($validation) && $validation->hasError('title')): ?>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('title') ?>
                                    </div>
                                <?php endif; ?>
                                <small class="form-text text-muted">
                                    Title must be at least 3 characters long and contain only letters, numbers, and spaces.
                                </small>
                            </div>

                            <!-- Description Field -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control <?= isset($validation) && $validation->hasError('description') ? 'is-invalid' : '' ?>" 
                                          id="description" 
                                          name="description" 
                                          rows="5" 
                                          placeholder="Enter record description (optional)"><?= old('description') ?></textarea>
                                <?php if (isset($validation) && $validation->hasError('description')): ?>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('description') ?>
                                    </div>
                                <?php endif; ?>
                                <small class="form-text text-muted">
                                    Maximum 1000 characters. <?= 1000 - strlen(old('description')) ?> characters remaining.
                                </small>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-between">
                                <a href="<?= site_url('/records') ?>" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Cancel
                                </a>
                                <div>
                                    <button type="reset" class="btn btn-outline-secondary me-2">
                                        <i class="bi bi-arrow-clockwise"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Create Record
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Guidelines</h6>
                    </div>
                    <div class="card-body">
                        <h6>Record Requirements:</h6>
                        <ul class="small">
                            <li>Title is required and must be unique</li>
                            <li>Title must be 3-255 characters</li>
                            <li>Description is optional (max 1000 characters)</li>
                            <li>Only alphanumeric characters and spaces allowed in title</li>
                        </ul>

                        <h6 class="mt-3">Tips:</h6>
                        <ul class="small">
                            <li>Use descriptive titles for easy searching</li>
                            <li>Provide detailed descriptions for better context</li>
                            <li>Records are automatically timestamped</li>
                        </ul>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?= site_url('/records') ?>" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-list"></i> View All Records
                            </a>
                            <a href="<?= site_url('/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-house"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Character Counter -->
    <script>
        document.getElementById('description').addEventListener('input', function() {
            const remaining = 1000 - this.value.length;
            const counter = this.nextElementSibling.nextElementSibling;
            if (counter) {
                counter.textContent = `Maximum 1000 characters. ${remaining} characters remaining.`;
            }
        });
    </script>
</body>
</html>
