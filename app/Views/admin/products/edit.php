<?php
$selectedCategory = trim((string) old('category', $product['category'] ?? ''));
$selectedCategoryAliases = [
    'device' => 'Device',
    'devices' => 'Device',
    'e-liquid' => 'E-Liquid',
    'eliquid' => 'E-Liquid',
];
$selectedCategoryKey = strtolower(str_replace([' ', '_'], '-', $selectedCategory));
$selectedCategory = $selectedCategoryAliases[$selectedCategoryKey] ?? $selectedCategory;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Edit Product') ?> - Quick Puff Vape Shop System</title>
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

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }

        .form-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .form-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
        }

        .form-card-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-card-header h3 i {
            color: #27c56f;
        }

        .form-card-body {
            padding: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
        }

        @media (max-width: 1200px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333333;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: border-color 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #27c56f;
            box-shadow: 0 0 0 3px rgba(39, 197, 111, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
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

        .invalid-feedback {
            color: #dc3545;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        .is-invalid {
            border-color: #dc3545 !important;
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
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .main-content-wrapper {
            background: #ffffff;
            min-height: 100vh;
            width: 100%;
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }

        .form-section:last-of-type {
            border-bottom: none;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: #27c56f;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .section-description {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 1.25rem;
        }

        .help-text {
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 0.25rem;
            display: block;
        }

        .flavor-table {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
        }

        .flavor-table-header {
            display: grid;
            grid-template-columns: 2fr 1fr auto;
            gap: 1rem;
            padding: 0.75rem 1rem;
            background: #e9ecef;
            border-radius: 6px;
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
        }

        .flavor-row {
            display: grid;
            grid-template-columns: 2fr 1fr auto;
            gap: 1rem;
            align-items: center;
            padding: 0.5rem 0;
        }

        .col-flavor, .col-stock, .col-action {
            display: flex;
            align-items: center;
        }

        .col-action {
            justify-content: center;
        }

        .btn-sm {
            padding: 0.4rem 0.75rem;
            font-size: 0.85rem;
        }

        .btn-lg {
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            padding-top: 1rem;
            margin-top: 1rem;
            border-top: 1px solid #e9ecef;
        }

        .current-image {
            margin-top: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: #e7f3ff;
            border-radius: 6px;
            border-left: 3px solid #0d6efd;
        }
    </style>
<?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
    <?= $this->include('admin/partials/sidebar') ?>

    <div class="main-content-wrapper">
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
                    <h1>Edit Product</h1>
                    <p>Update product information</p>
                </div>
                <div class="page-actions">
                    <a href="<?= site_url('products') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Products
                    </a>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <h3><i class="fas fa-info-circle"></i> Product Information</h3>
            </div>
            <div class="form-card-body">
                <?= form_open_multipart('products/update/' . $product['id']) ?>
                    
                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <h4 class="section-title">Basic Information</h4>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">Product Name *</label>
                                <input type="text" name="name" id="name" class="form-control" 
                                       value="<?= old('name', $product['name']) ?>" required>
                                <?php if (isset($validation) && $validation->hasError('name')): ?>
                                    <div class="invalid-feedback"><?= $validation->getError('name') ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="category">Category *</label>
                                <select name="category" id="category" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= esc($category) ?>" 
                                                <?= $selectedCategory === $category ? 'selected' : '' ?>>
                                            <?= esc($category) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($validation) && $validation->hasError('category')): ?>
                                    <div class="invalid-feedback"><?= $validation->getError('category') ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="brand">Brand Name *</label>
                                <input
                                    type="text"
                                    name="brand"
                                    id="brand"
                                    class="form-control"
                                    list="brandSuggestions"
                                    value="<?= esc(old('brand', $product['brand'] ?? '')) ?>"
                                    placeholder="e.g. ASPIRE, BLACK, X-VAPE"
                                    maxlength="100"
                                    required
                                >
                                <datalist id="brandSuggestions">
                                    <?php foreach ($brands ?? [] as $brandName): ?>
                                        <option value="<?= esc($brandName) ?>"></option>
                                    <?php endforeach; ?>
                                </datalist>
                                <?php if (isset($validation) && $validation->hasError('brand')): ?>
                                    <div class="invalid-feedback"><?= $validation->getError('brand') ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="unit_price">Cost Price (PHP) *</label>
                                <input type="number" name="unit_price" id="unit_price" class="form-control"
                                       value="<?= old('unit_price', $product['unit_price'] ?? $product['price'] ?? 0) ?>"
                                       step="0.01" min="0" required>
                                <small class="help-text">Capital / cost price</small>
                                <?php if (isset($validation) && $validation->hasError('unit_price')): ?>
                                    <div class="invalid-feedback"><?= $validation->getError('unit_price') ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="selling_price">Selling Price (PHP) *</label>
                                <input type="number" name="selling_price" id="selling_price" class="form-control"
                                       value="<?= old('selling_price', $product['selling_price'] ?? $product['price'] ?? 0) ?>"
                                       step="0.01" min="0" required>
                                <small class="help-text">Price shown to customers</small>
                                <?php if (isset($validation) && $validation->hasError('selling_price')): ?>
                                    <div class="invalid-feedback"><?= $validation->getError('selling_price') ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="is_active">Status *</label>
                                <select name="is_active" id="is_active" class="form-control" required>
                                    <option value="1" <?= (string) old('is_active', $product['is_active']) === '1' ? 'selected' : '' ?>>Active</option>
                                    <option value="0" <?= (string) old('is_active', $product['is_active']) === '0' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                                <?php if (isset($validation) && $validation->hasError('is_active')): ?>
                                    <div class="invalid-feedback"><?= $validation->getError('is_active') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Stock & Puffs Section -->
                    <div class="form-section">
                        <h4 class="section-title">Stock & Specifications</h4>
                        <div class="form-grid">
                            <div class="form-group stock-quantity-field">
                                <label for="stock_qty">Total Stock Quantity *</label>
                                <input type="number" name="stock_qty" id="stock_qty" class="form-control" 
                                       value="<?= old('stock_qty', $product['stock_qty']) ?>" 
                                       min="0" required>
                                <small class="help-text">Total stock across all flavors/variants</small>
                                <?php if (isset($validation) && $validation->hasError('stock_qty')): ?>
                                    <div class="invalid-feedback"><?= $validation->getError('stock_qty') ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group puff-field" style="display: none;">
                                <label for="puffs">Puffs Count</label>
                                <input type="number" name="puffs" id="puffs" class="form-control" 
                                       value="<?= old('puffs', $product['puffs'] ?? '') ?>" 
                                       min="0" placeholder="e.g. 8000">
                                <small class="help-text">Number of puffs (for Pods/Disposable)</small>
                                <?php if (isset($validation) && $validation->hasError('puffs')): ?>
                                    <div class="invalid-feedback"><?= $validation->getError('puffs') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Flavor Inventory Section - Initially Hidden -->
                    <div class="form-section flavor-inventory-section" style="display: none;">
                        <div class="section-header">
                            <h4 class="section-title"><i class="fas fa-flask"></i> Flavor Inventory</h4>
                            <button type="button" id="addFlavorBtn" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Add Flavor
                            </button>
                        </div>
                        <p class="section-description">Manage individual flavor stocks. Total stock is calculated automatically from flavor quantities.</p>
                        
                        <div class="flavor-table">
                            <div class="flavor-table-header">
                                <div class="col-flavor">Flavor Name</div>
                                <div class="col-stock">Stock</div>
                                <div class="col-action">Action</div>
                            </div>
                            <div class="flavor-rows" id="flavorRows">
                                <?php 
                                $existingFlavors = old('flavors', $product['flavors'] ?? []);
                                if (!is_array($existingFlavors)) {
                                    $existingFlavors = [];
                                }
                                if (!empty($existingFlavors)): 
                                    $flavorIndex = 1;
                                    foreach ($existingFlavors as $flavor): 
                                ?>
                                    <div class="flavor-row" data-flavor-row="<?= $flavorIndex ?>">
                                        <div class="col-flavor">
                                            <input type="hidden" name="flavors[<?= $flavorIndex ?>][id]" value="<?= (int) ($flavor['id'] ?? 0) ?>">
                                            <input type="text" name="flavors[<?= $flavorIndex ?>][name]" value="<?= esc($flavor['name'] ?? $flavor['flavor'] ?? $flavor['flavor_name'] ?? '') ?>" placeholder="e.g. Bacteria Monster" class="form-control flavor-name-input">
                                        </div>
                                        <div class="col-stock">
                                            <input type="number" name="flavors[<?= $flavorIndex ?>][stock]" value="<?= (int) ($flavor['stock'] ?? $flavor['stock_qty'] ?? $flavor['flavor_stock'] ?? 0) ?>" min="0" class="form-control flavor-stock-input">
                                        </div>
                                        <div class="col-action">
                                            <button type="button" class="btn btn-danger btn-sm remove-flavor-btn" onclick="removeFlavorRow(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php 
                                    $flavorIndex++;
                                    endforeach; 
                                else: 
                                ?>
                                    <div class="flavor-row" data-flavor-row="1">
                                        <div class="col-flavor">
                                            <input type="hidden" name="flavors[1][id]" value="0">
                                            <input type="text" name="flavors[1][name]" placeholder="e.g. Bacteria Monster" class="form-control flavor-name-input">
                                        </div>
                                        <div class="col-stock">
                                            <input type="number" name="flavors[1][stock]" value="0" min="0" class="form-control flavor-stock-input">
                                        </div>
                                        <div class="col-action">
                                            <button type="button" class="btn btn-danger btn-sm remove-flavor-btn" onclick="removeFlavorRow(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Product Image Section -->
                    <div class="form-section">
                        <h4 class="section-title"><i class="fas fa-image"></i> Product Image</h4>
                        <div class="form-group">
                            <label for="image">Upload New Image</label>
                            <input type="file" name="image" id="image" class="form-control" accept="image/*">
                            <small class="help-text">Leave empty to keep current image</small>
                            <?php if ($product['image']): ?>
                                <div class="current-image">
                                    <small>Current: <strong><?= esc($product['image']) ?></strong></small>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($validation) && $validation->hasError('image')): ?>
                                <div class="invalid-feedback"><?= $validation->getError('image') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Description Section -->
                    <div class="form-section">
                        <h4 class="section-title"><i class="fas fa-align-left"></i> Description</h4>
                        <div class="form-group">
                            <textarea name="description" id="description" class="form-control" rows="4" placeholder="Enter product description..."><?= old('description', $product['description']) ?></textarea>
                            <?php if (isset($validation) && $validation->hasError('description')): ?>
                                <div class="invalid-feedback"><?= $validation->getError('description') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Update Product
                        </button>
                        <a href="<?= site_url('products') ?>" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
    </div>
<script>
        const categorySelect = document.getElementById('category');
        const puffField = document.querySelector('.puff-field');

        // Handle category selection to show/hide flavor and puff fields
        function toggleFlavorPuffFields() {
            const selectedCategory = categorySelect.value.toLowerCase();
            const flavorInventorySection = document.querySelector('.flavor-inventory-section');
            const stockQuantityField = document.querySelector('.stock-quantity-field');
            
            if (selectedCategory === 'pods' || selectedCategory === 'disposable' || selectedCategory === 'e-liquid') {
                puffField.style.display = selectedCategory === 'e-liquid' ? 'none' : 'block';
                flavorInventorySection.style.display = 'block';
                stockQuantityField.style.display = 'none'; // Hide individual stock field for flavor-based products
                if (selectedCategory === 'e-liquid') {
                    document.getElementById('puffs').value = '';
                }
            } else {
                puffField.style.display = 'none';
                flavorInventorySection.style.display = 'none';
                stockQuantityField.style.display = 'block'; // Show individual stock field for non-flavor products
                // Clear the values when hidden
                document.getElementById('puffs').value = '';
                // Clear flavor inventory
                clearFlavorInventory();
            }
        }

        // Flavor inventory management
        let flavorRowCount = <?= !empty($product['flavors']) ? count($product['flavors']) : 1 ?>;

        function addFlavorRow() {
            flavorRowCount++;
            const flavorRows = document.getElementById('flavorRows');
            const newRow = document.createElement('div');
            newRow.className = 'flavor-row';
            newRow.setAttribute('data-flavor-row', flavorRowCount);
            
            newRow.innerHTML = `
                <div class="col-flavor">
                    <input type="hidden" name="flavors[${flavorRowCount}][id]" value="0">
                    <input type="text" name="flavors[${flavorRowCount}][name]" placeholder="e.g. Bacteria Monster" class="form-control flavor-name-input">
                </div>
                <div class="col-stock">
                    <input type="number" name="flavors[${flavorRowCount}][stock]" value="0" min="0" class="form-control flavor-stock-input">
                </div>
                <div class="col-action">
                    <button type="button" class="btn btn-danger btn-sm remove-flavor-btn" onclick="removeFlavorRow(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            flavorRows.appendChild(newRow);
            updateTotalStock();
        }

        function removeFlavorRow(button) {
            const row = button.closest('.flavor-row');
            if (document.querySelectorAll('.flavor-row').length > 1) {
                row.remove();
                updateTotalStock();
            }
        }

        function clearFlavorInventory() {
            const flavorRows = document.getElementById('flavorRows');
            flavorRows.innerHTML = `
                <div class="flavor-row" data-flavor-row="1">
                    <div class="col-flavor">
                        <input type="hidden" name="flavors[1][id]" value="0">
                        <input type="text" name="flavors[1][name]" placeholder="e.g. Bacteria Monster" class="form-control flavor-name-input">
                    </div>
                    <div class="col-stock">
                        <input type="number" name="flavors[1][stock]" value="0" min="0" class="form-control flavor-stock-input">
                    </div>
                    <div class="col-action">
                        <button type="button" class="btn btn-danger btn-sm remove-flavor-btn" onclick="removeFlavorRow(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            flavorRowCount = 1;
        }

        function updateTotalStock() {
            const stockInputs = document.querySelectorAll('.flavor-stock-input');
            let totalStock = 0;
            stockInputs.forEach(input => {
                totalStock += parseInt(input.value) || 0;
            });
            
            // Update the stock_qty field for form submission
            const stockQtyField = document.getElementById('stock_qty');
            if (stockQtyField) {
                stockQtyField.value = totalStock;
            }
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Add flavor button
            const addFlavorBtn = document.getElementById('addFlavorBtn');
            if (addFlavorBtn) {
                addFlavorBtn.addEventListener('click', addFlavorRow);
            }

            // Update total stock when flavor stock changes
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('flavor-stock-input')) {
                    updateTotalStock();
                }
            });
        });

        // Add event listener for category change
        categorySelect?.addEventListener('change', toggleFlavorPuffFields);

        // Initial check on page load
        toggleFlavorPuffFields();
    </script>
</body>
</html>




