<?= $this->include('partials/header') ?>

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

<style>
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
</style>

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

<?= $this->include('partials/footer') ?>
