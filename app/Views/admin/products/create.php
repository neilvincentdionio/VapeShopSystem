<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Add New Product') ?> - Quick Puff Vape Shop System</title>
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

        .form-card-header h3 i,
        .section-title i {
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

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border-left-color: #dc3545;
            color: #b02a37;
        }

        .alert ul {
            margin: 0.25rem 0 0 1rem;
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

        .col-flavor,
        .col-stock,
        .col-action {
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

        .image-preview {
            display: none;
            margin-top: 0.75rem;
            width: 140px;
            aspect-ratio: 1;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            object-fit: cover;
            background: #f8f9fa;
        }

        @media (max-width: 1200px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }

            .page-header-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-grid,
            .flavor-table-header,
            .flavor-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
    <?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
    <?= $this->include('admin/partials/sidebar') ?>

    <div class="main-content-wrapper">
        <div class="container">
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Please fix the following errors:</strong>
                        <ul>
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title">
                        <h1>Add Product</h1>
                        <p>Create a new product and manage flavor inventory</p>
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
                    <?= form_open_multipart('products/store') ?>

                        <div class="form-section">
                            <h4 class="section-title">Basic Information</h4>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="name">Product Name *</label>
                                    <input type="text" name="name" id="name" class="form-control" value="<?= esc(old('name') ?? '') ?>" placeholder="Enter product name" required>
                                </div>

                                <div class="form-group">
                                    <label for="category">Category *</label>
                                    <select name="category" id="category" class="form-control" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= esc($category) ?>" <?= old('category') === $category ? 'selected' : '' ?>>
                                                <?= esc($category) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="brand">Brand Name *</label>
                                    <input
                                        type="text"
                                        name="brand"
                                        id="brand"
                                        class="form-control"
                                        list="brandSuggestions"
                                        value="<?= esc(old('brand') ?? '') ?>"
                                        placeholder="e.g. ASPIRE, BLACK, X-VAPE"
                                        maxlength="100"
                                        required
                                    >
                                    <datalist id="brandSuggestions">
                                        <?php foreach ($brands ?? [] as $brandName): ?>
                                            <option value="<?= esc($brandName) ?>"></option>
                                        <?php endforeach; ?>
                                    </datalist>
                                    <small class="help-text">Shown in Product Management and filters</small>
                                </div>

                                <div class="form-group">
                                    <label for="unit_price">Cost Price (PHP) *</label>
                                    <input type="number" name="unit_price" id="unit_price" class="form-control" value="<?= esc(old('unit_price') ?? '0.00') ?>" step="0.01" min="0" required>
                                    <small class="help-text">Capital / cost price</small>
                                </div>

                                <div class="form-group">
                                    <label for="selling_price">Selling Price (PHP) *</label>
                                    <input type="number" name="selling_price" id="selling_price" class="form-control" value="<?= esc(old('selling_price') ?? '0.00') ?>" step="0.01" min="0" required>
                                    <small class="help-text">Price shown to customers</small>
                                </div>

                                <div class="form-group">
                                    <label for="is_active">Status *</label>
                                    <select name="is_active" id="is_active" class="form-control" required>
                                        <option value="1" <?= (string) old('is_active', '1') === '1' ? 'selected' : '' ?>>Active</option>
                                        <option value="0" <?= (string) old('is_active') === '0' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4 class="section-title">Stock & Specifications</h4>
                            <div class="form-grid">
                                <div class="form-group stock-quantity-field">
                                    <label for="stock_qty">Total Stock Quantity *</label>
                                    <input type="number" name="stock_qty" id="stock_qty" class="form-control" value="<?= esc(old('stock_qty') ?? '0') ?>" min="0" required>
                                    <small class="help-text">Total stock across all flavors/variants</small>
                                </div>

                                <div class="form-group puff-field" style="display: none;">
                                    <label for="puffs">Puffs Count</label>
                                    <input type="number" name="puffs" id="puffs" class="form-control" value="<?= esc(old('puffs') ?? '') ?>" min="0" placeholder="e.g. 8000">
                                    <small class="help-text">Number of puffs (for Pods/Disposable)</small>
                                </div>
                            </div>
                        </div>

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
                                    $existingFlavors = old('flavors', []);
                                    if (!is_array($existingFlavors) || $existingFlavors === []) {
                                        $existingFlavors = [['name' => '', 'stock' => 0]];
                                    }
                                    $flavorIndex = 1;
                                    foreach ($existingFlavors as $flavor):
                                    ?>
                                        <div class="flavor-row" data-flavor-row="<?= $flavorIndex ?>">
                                            <div class="col-flavor">
                                                <input type="hidden" name="flavors[<?= $flavorIndex ?>][id]" value="0">
                                                <input type="text" name="flavors[<?= $flavorIndex ?>][name]" value="<?= esc($flavor['name'] ?? $flavor['flavor'] ?? '') ?>" placeholder="e.g. Bacteria Monster" class="form-control flavor-name-input">
                                            </div>
                                            <div class="col-stock">
                                                <input type="number" name="flavors[<?= $flavorIndex ?>][stock]" value="<?= (int) ($flavor['stock'] ?? $flavor['stock_qty'] ?? 0) ?>" min="0" class="form-control flavor-stock-input">
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
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4 class="section-title"><i class="fas fa-image"></i> Product Image</h4>
                            <div class="form-group">
                                <label for="image">Upload New Image</label>
                                <input type="file" name="image" id="image" class="form-control" accept="image/*">
                                <small class="help-text">Optional. Choose JPG, PNG, WEBP, or GIF. Max 4MB.</small>
                                <img src="" alt="Product image preview" id="imagePreview" class="image-preview">
                            </div>
                        </div>

                        <div class="form-section">
                            <h4 class="section-title"><i class="fas fa-align-left"></i> Description</h4>
                            <div class="form-group">
                                <textarea name="description" id="description" class="form-control" rows="4" placeholder="Enter product description..."><?= esc(old('description') ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Save Product
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
        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('imagePreview');
        const categorySelect = document.getElementById('category');
        const puffField = document.querySelector('.puff-field');

        imageInput?.addEventListener('change', (event) => {
            const file = event.target.files?.[0];

            if (!file) {
                imagePreview.style.display = 'none';
                imagePreview.src = '';
                return;
            }

            imagePreview.src = URL.createObjectURL(file);
            imagePreview.style.display = 'block';
        });

        function toggleFlavorPuffFields() {
            const selectedCategory = categorySelect.value.toLowerCase();
            const flavorInventorySection = document.querySelector('.flavor-inventory-section');
            const stockQuantityField = document.querySelector('.stock-quantity-field');

            if (selectedCategory === 'pods' || selectedCategory === 'disposable' || selectedCategory === 'e-liquid') {
                puffField.style.display = selectedCategory === 'e-liquid' ? 'none' : 'block';
                flavorInventorySection.style.display = 'block';
                stockQuantityField.style.display = 'none';

                if (selectedCategory === 'e-liquid') {
                    document.getElementById('puffs').value = '';
                }

                updateTotalStock();
            } else {
                puffField.style.display = 'none';
                flavorInventorySection.style.display = 'none';
                stockQuantityField.style.display = 'block';
                document.getElementById('puffs').value = '';
                clearFlavorInventory();
            }
        }

        let flavorRowCount = <?= count($existingFlavors ?? [['name' => '', 'stock' => 0]]) ?>;

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
                totalStock += parseInt(input.value, 10) || 0;
            });

            const stockQtyField = document.getElementById('stock_qty');
            if (stockQtyField) {
                stockQtyField.value = totalStock;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const addFlavorBtn = document.getElementById('addFlavorBtn');
            if (addFlavorBtn) {
                addFlavorBtn.addEventListener('click', addFlavorRow);
            }

            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('flavor-stock-input')) {
                    updateTotalStock();
                }
            });
        });

        categorySelect?.addEventListener('change', toggleFlavorPuffFields);
        toggleFlavorPuffFields();
    </script>
</body>
</html>
