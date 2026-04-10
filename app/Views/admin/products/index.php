<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Product Management') ?> - Quick Puff Vape Shop System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 1rem;
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
            min-width: 0;
        }
        .navbar-menu {
            display: flex;
            align-items: center;
            gap: .65rem;
            flex-wrap: wrap;
        }
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
            white-space: nowrap;
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
        .nav-right {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: .8rem;
            flex-wrap: wrap;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: .55rem;
            color: #333333;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .user-name {
            max-width: 170px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 500;
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #27c56f, #7ef0b2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: .9rem;
        }
        .badge {
            background: #f8f9fa;
            color: #6c757d;
            padding: .25rem .5rem;
            border-radius: 12px;
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
            text-decoration: none;
            padding: .5rem 1rem;
            border-radius: 5px;
            border: none;
            font-size: .9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all .3s;
        }
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        .page-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .page-title h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #333333;
            margin-bottom: 0.5rem;
        }

        .page-title p {
            color: #666666;
            font-size: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #27c56f;
            color: white;
        }

        .btn-primary:hover {
            background: #23b064;
            transform: translateY(-1px);
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: rgba(39, 197, 111, 0.1);
            color: #27c56f;
        }

        .stat-icon.success { background: rgba(39, 197, 111, 0.1); color: #27c56f; }
        .stat-icon.warning { background: rgba(255, 152, 0, 0.1); color: #ff9800; }
        .stat-icon.info { background: rgba(0, 188, 212, 0.1); color: #00bcd4; }

        .stat-content h3 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333333;
            margin-bottom: 0.25rem;
        }

        .stat-content p {
            color: #666666;
            font-size: 0.9rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: rgba(39, 197, 111, 0.1);
            border-left-color: #27c56f;
            color: #27c56f;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border-left-color: #dc3545;
            color: #dc3545;
        }

        .alert-warning {
            background: rgba(255, 152, 0, 0.1);
            border-left-color: #ff9800;
            color: #ff9800;
        }

        .alert.alert-warning {
            display: block;
            padding: 1.15rem 1.4rem;
            border-radius: 14px;
            border: 1px solid #ffd8a8;
            border-left: 4px solid #ff9800;
            background: linear-gradient(135deg, #fff8ef, #fff3df);
            color: #9a5a00;
            box-shadow: 0 2px 10px rgba(255, 152, 0, 0.08);
        }

        .alert.alert-warning > i {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            vertical-align: middle;
            background: rgba(255, 152, 0, 0.12);
            color: #ff9800;
        }

        .alert.alert-warning > strong {
            color: #cc6b00;
            font-weight: 700;
        }

        .alert.alert-warning ul {
            list-style: none;
            margin: 1rem 0 0 3.3rem !important;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.75rem;
        }

        .alert.alert-warning li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.85rem 1rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid rgba(255, 152, 0, 0.16);
            color: #5c3b00;
            font-weight: 600;
            line-height: 1.45;
        }

        .alert.alert-warning li::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ff9800;
            flex: 0 0 auto;
        }

        .data-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .data-card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .data-card-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333333;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #333333;
            border-bottom: 1px solid #e0e0e0;
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .data-table tr:hover {
            background: #f8f9fa;
        }

        .product-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        
        .product-thumbnail-placeholder {
            width: 50px;
            height: 50px;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 18px;
        }
        
        .category-badge {
            background: rgba(0, 188, 212, 0.1);
            color: #00bcd4;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .stock-badge {
            background: rgba(76, 175, 80, 0.1);
            color: #4caf50;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .stock-badge.low-stock {
            background: rgba(255, 152, 0, 0.1);
            color: #ff9800;
        }
        
        .status-badge.active {
            background: rgba(76, 175, 80, 0.1);
            color: #4caf50;
        }
        
        .status-badge.inactive {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }
        
        .action-buttons {
            display: flex;
            gap: 4px;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #e0e0e0;
            color: #666;
            transition: all 0.2s ease;
        }

        .btn-outline:hover {
            background: #f8f9fa;
            border-color: #27c56f;
            color: #27c56f;
            transform: translateY(-1px);
        }

        .btn-warning {
            background: #ff9800;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-warning:hover {
            background: #f57c00;
            transform: translateY(-1px);
        }

        .btn-success {
            background: #27c56f;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-success:hover {
            background: #23b064;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        
        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: #666;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            color: #ccc;
        }

        @media (max-width: 1200px) {
            .navbar {
                padding: 1rem;
            }
            .navbar-content {
                grid-template-columns: 1fr;
                align-items: stretch;
                gap: .8rem;
            }
            .navbar-center {
                order: 2;
            }
            .nav-right {
                order: 3;
                justify-content: space-between;
            }
            .user-info {
                justify-content: flex-start;
            }
        }

        @media (max-width: 768px) {
            .navbar-menu a,
            .nav-dropdown-btn {
                padding: .45rem .75rem;
            }
            .nav-right { justify-content: space-between; }
            .user-name { max-width: 120px; }
            .container { padding: 0 1rem; }
            .page-header-content {
                flex-direction: column;
                align-items: flex-start;
            }
            .alert.alert-warning {
                padding: 1rem;
                line-height: 1.6;
            }
            .alert.alert-warning > i {
                width: 34px;
                height: 34px;
                margin-right: 0.6rem;
            }
            .alert.alert-warning ul {
                grid-template-columns: 1fr;
                margin-left: 0 !important;
            }
        }
    </style>
<?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
    <?= $this->include('admin/partials/sidebar') ?>

    <div class="container">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <div class="page-header-content">
                <div class="page-title">
                    <h1>Product Management</h1>
                    <p>Manage your vape shop inventory</p>
                </div>
                <div class="page-actions">
                    <a href="<?= site_url('products/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Product
                    </a>
                </div>
            </div>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $totalProducts ?></h3>
                    <p>Total Products</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $activeProducts ?></h3>
                    <p>Active Products</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3><?= count($lowStockProducts) ?></h3>
                    <p>Low Stock Items</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-content">
                    <h3><?= count($categories) ?></h3>
                    <p>Categories</p>
                </div>
            </div>
        </div>

        <?php if (!empty($lowStockProducts)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Low Stock Alert:</strong> The following products have low stock (≤10 items):
                <ul style="margin-top: 10px; margin-bottom: 0;">
                    <?php foreach ($lowStockProducts as $product): ?>
                        <li><?= esc($product['name']) ?> - <?= $product['stock'] ?> left</li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="data-card">
            <div class="data-card-header">
                <h3>Products List</h3>
                <div class="card-actions">
                    <button class="btn btn-sm btn-outline" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-box-open"></i>
                                        <h4>No products found</h4>
                                        <p>Start by adding your first product to the inventory.</p>
                                        <a href="<?= site_url('products/create') ?>" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Add Product
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <?php if ($product['image']): ?>
                                            <img src="<?= base_url('uploads/products/' . $product['image']) ?>" 
                                                 alt="<?= esc($product['name']) ?>" 
                                                 class="product-thumbnail">
                                        <?php else: ?>
                                            <div class="product-thumbnail-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= esc($product['name']) ?></strong>
                                        <?php if ($product['description']): ?>
                                            <br><small class="text-muted"><?= character_limiter(esc($product['description']), 50) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="category-badge"><?= esc($product['category']) ?></span>
                                    </td>
                                    <td>
                                        <strong>₱<?= number_format($product['price'], 2) ?></strong>
                                    </td>
                                    <td>
                                        <span class="stock-badge <?= $product['stock'] <= 10 ? 'low-stock' : '' ?>">
                                            <?= $product['stock'] ?> units
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= $product['status'] ?>">
                                            <?= ucfirst($product['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?= date('M d, Y', strtotime($product['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?= site_url('customer/product/' . $product['id']) ?>" 
                                               class="btn btn-sm btn-outline" 
                                               target="_blank" 
                                               title="View on customer side">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= site_url('products/edit/' . $product['id']) ?>" 
                                               class="btn btn-sm btn-outline" 
                                               title="Edit product">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="toggleStatus(<?= $product['id'] ?>)" 
                                                    class="btn btn-sm <?= $product['status'] === 'active' ? 'btn-warning' : 'btn-success' ?>" 
                                                    title="<?= $product['status'] === 'active' ? 'Deactivate' : 'Activate' ?> product">
                                                <i class="fas <?= $product['status'] === 'active' ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                            </button>
                                            <button onclick="deleteProduct(<?= $product['id'] ?>, '<?= esc($product['name']) ?>')" 
                                                    class="btn btn-sm btn-danger" 
                                                    title="Delete product">
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

    <script>
    function toggleStatus(id) {
        if (confirm('Are you sure you want to toggle the status of this product?')) {
            window.location.href = '<?= site_url('products/toggle-status/') ?>' + id;
        }
    }

    function deleteProduct(id, name) {
        if (confirm('Are you sure you want to delete "' + name + '"? This action cannot be undone.')) {
            window.location.href = '<?= site_url('products/delete/') ?>' + id;
        }
    }
    </script>
</body>
</html>




