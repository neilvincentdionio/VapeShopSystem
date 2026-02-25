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
                        <a class="nav-link active" href="<?= site_url('/records') ?>">
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
        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h2><i class="bi bi-file-text"></i> Records Management</h2>
            </div>
            <div class="col-md-6 text-end">
                <a href="<?= site_url('/records/create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add New Record
                </a>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form method="GET" action="<?= site_url('/records') ?>">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search records..." 
                               value="<?= esc($search ?? '') ?>">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-search"></i> Search
                        </button>
                        <?php if ($search ?? null): ?>
                            <a href="<?= site_url('/records') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <small class="text-muted">
                    Showing <?= count($records) ?> of <?= $pager['totalRecords'] ?> records
                </small>
            </div>
        </div>

        <!-- Records Table -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($records)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <h5 class="mt-3 text-muted">No records found</h5>
                        <p class="text-muted">
                            <?php if ($search): ?>
                                No records match your search criteria.
                            <?php else: ?>
                                Start by adding your first record.
                            <?php endif; ?>
                        </p>
                        <?php if (!$search): ?>
                            <a href="<?= site_url('/records/create') ?>" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add First Record
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="25%">Title</th>
                                    <th width="45%">Description</th>
                                    <th width="15%">Created</th>
                                    <th width="10%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counter = ($pager['currentPage'] - 1) * $pager['perPage'] + 1; ?>
                                <?php foreach ($records as $record): ?>
                                    <tr>
                                        <td><?= $counter++ ?></td>
                                        <td>
                                            <strong><?= esc($record['title']) ?></strong>
                                        </td>
                                        <td>
                                            <?= character_limiter(esc($record['description'] ?? 'No description'), 100) ?>
                                        </td>
                                        <td>
                                            <small><?= date('M d, Y', strtotime($record['created_at'])) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="<?= site_url('/records/edit/' . $record['id']) ?>" 
                                                   class="btn btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php if ($user_role === 'admin'): ?>
                                                    <form method="POST" action="<?= site_url('/records/delete/' . $record['id']) ?>" 
                                                          style="display: inline;" 
                                                          onsubmit="return confirm('Are you sure you want to delete this record?')">
                                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pager['totalPages'] > 1): ?>
                        <nav aria-label="Records pagination">
                            <ul class="pagination justify-content-center">
                                <?php if ($pager['currentPage'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= site_url('/records?page=' . ($pager['currentPage'] - 1) . ($search ? '&search=' . urlencode($search) : '')) ?>">
                                            <i class="bi bi-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $pager['totalPages']; $i++): ?>
                                    <li class="page-item <?= $i == $pager['currentPage'] ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= site_url('/records?page=' . $i . ($search ? '&search=' . urlencode($search) : '')) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pager['currentPage'] < $pager['totalPages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= site_url('/records?page=' . ($pager['currentPage'] + 1) . ($search ? '&search=' . urlencode($search) : '')) ?>">
                                            Next <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
