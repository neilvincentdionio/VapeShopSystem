<?php
helper('product');
$nicotineLevelOptions = product_nicotine_level_options();
?>
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

        .flavor-table-header,
        .flavor-row {
            grid-template-columns: 2fr 1fr auto;
        }

        .flavor-table-header {
            display: grid;
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

        .expiration-warning {
            display: none;
            align-items: flex-start;
            gap: 0.65rem;
            margin-top: 0.75rem;
            padding: 0.85rem 1rem;
            border-radius: 10px;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #991b1b;
            font-size: 0.9rem;
        }

        .expiration-warning.is-visible {
            display: flex;
        }

        .expiration-warning i {
            margin-top: 0.1rem;
        }

        .col-action {
            justify-content: center;
        }

        .flavor-inventory-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 0.75rem;
        }

        .flavor-collapse-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            min-height: 34px;
            padding: 0.4rem 0.75rem;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 0.86rem;
            font-weight: 600;
            cursor: pointer;
        }

        .flavor-collapse-btn:hover {
            background: #dbeafe;
        }

        .flavor-collapse-caret {
            font-size: 0.65rem;
            transition: transform 0.15s;
        }

        .flavor-inventory-body.is-collapsed .flavor-collapse-caret {
            transform: rotate(-90deg);
        }

        .flavor-inventory-body.is-collapsed .flavor-table,
        .flavor-inventory-body.is-collapsed .section-description {
            display: none;
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
                                    <label for="stock_qty" id="stockQtyLabel">Total Stock Quantity *</label>
                                    <input type="number" name="stock_qty" id="stock_qty" class="form-control" value="<?= esc(old('stock_qty') ?? '0') ?>" min="0" required>
                                    <small class="help-text" id="stockQtyHelp">Total stock across all flavors/variants</small>
                                </div>

                                <div class="form-group spec-field" style="display: none;">
                                    <label for="puffs" id="specFieldLabel">Puffs Count</label>
                                    <input type="number" name="puffs" id="puffs" class="form-control" value="<?= esc(old('puffs') ?? '') ?>" min="0" placeholder="e.g. 8000">
                                    <small class="help-text" id="specFieldHelp">Number of puffs (for Pods/Disposable)</small>
                                </div>

                                <div class="form-group disposable-spec-field" style="display: none;">
                                    <label for="battery_capacity">Battery Capacity (mAh)</label>
                                    <input type="number" name="battery_capacity" id="battery_capacity" class="form-control" value="<?= esc(old('battery_capacity') ?? '') ?>" min="0" placeholder="e.g. 650">
                                    <small class="help-text">Built-in battery size for this disposable device.</small>
                                </div>

                                <div class="form-group disposable-spec-field" style="display: none;">
                                    <label for="eliquid_capacity">E-Liquid Capacity (ML)</label>
                                    <input type="number" name="eliquid_capacity" id="eliquid_capacity" class="form-control" value="<?= esc(old('eliquid_capacity') ?? '') ?>" min="0" placeholder="e.g. 12">
                                    <small class="help-text">Pre-filled e-liquid volume in the device.</small>
                                </div>
                            </div>
                        </div>

                        <?php $product = []; include __DIR__ . '/partials/device_spec_fields.php'; ?>

                        <div class="form-section compliance-section" style="display: none;">
                            <h4 class="section-title"><i class="fas fa-shield-alt"></i> Nicotine & Expiration</h4>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="nicotine_level">Nicotine Level</label>
                                    <select name="nicotine_level" id="nicotine_level" class="form-control">
                                        <option value="">Select nicotine level</option>
                                        <?php foreach ($nicotineLevelOptions as $level): ?>
                                            <option value="<?= esc($level) ?>" <?= (string) old('nicotine_level') === $level ? 'selected' : '' ?>><?= esc($level) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="expires_at">Product Expiration Date</label>
                                    <input type="date" name="expires_at" id="expires_at" class="form-control" value="<?= esc(old('expires_at') ?? '') ?>">
                                    <small class="help-text">One expiration date for the entire product (all flavors).</small>
                                    <div class="expiration-warning<?= product_is_expired(old('expires_at')) ? ' is-visible' : '' ?>" id="expirationWarning" role="alert">
                                        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                                        <span id="expirationWarningText">This product is expired. Choose a future date before saving.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section flavor-inventory-section" style="display: none;">
                            <div class="section-header">
                                <h4 class="section-title"><i class="fas fa-flask"></i> Flavor Inventory</h4>
                            </div>
                            <div class="flavor-inventory-toolbar">
                                <button type="button" id="toggleFlavorInventoryBtn" class="flavor-collapse-btn" aria-expanded="false">
                                    <span id="flavorInventoryCount">0</span> Flavors
                                    <span class="flavor-collapse-caret" aria-hidden="true">▼</span>
                                </button>
                                <button type="button" id="addFlavorBtn" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus"></i> Add Flavor
                                </button>
                            </div>
                            <div class="flavor-inventory-body is-collapsed" id="flavorInventoryBody">
                            <p class="section-description">Manage individual flavor stocks. Total stock is calculated automatically from flavor quantities.</p>

                            <div class="flavor-table">
                                <div class="flavor-table-header">
                                    <div class="col-flavor">Flavor Name</div>
                                    <div class="col-stock" id="flavorStockHeader">Stock</div>
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
        const specField = document.querySelector('.spec-field');
        const disposableSpecFields = document.querySelectorAll('.disposable-spec-field');
        const deviceSpecSection = document.querySelector('.device-spec-section');
        const deviceTypeSelect = document.getElementById('device_type');
        const deviceFieldVisibility = <?= json_encode(array_combine(
            array_keys(product_device_type_options()),
            array_map(static fn (string $slug): array => device_type_field_visibility($slug), array_keys(product_device_type_options()))
        ), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const complianceSection = document.querySelector('.compliance-section');
        const specFieldLabel = document.getElementById('specFieldLabel');
        const specFieldHelp = document.getElementById('specFieldHelp');
        const puffsInput = document.getElementById('puffs');

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

        function stockUnitsForCategory(category) {
            const slug = String(category || '').toLowerCase().replace(/[^a-z0-9]+/g, '');

            if (slug.includes('eliquid') || slug.includes('liquid')) {
                return { singular: 'bottle', plural: 'bottles' };
            }
            if (slug.includes('disposable')) {
                return { singular: 'piece', plural: 'pieces' };
            }
            if (slug.includes('pod')) {
                return { singular: 'pod', plural: 'pods' };
            }
            if (slug.includes('device')) {
                return { singular: 'piece', plural: 'pieces' };
            }

            return { singular: 'unit', plural: 'units' };
        }

        function updateStockUnitLabels() {
            const units = stockUnitsForCategory(categorySelect?.value || '');
            const stockQtyLabel = document.getElementById('stockQtyLabel');
            const stockQtyHelp = document.getElementById('stockQtyHelp');
            const flavorStockHeader = document.getElementById('flavorStockHeader');

            if (stockQtyLabel) {
                stockQtyLabel.textContent = `Total Stock (${units.plural}) *`;
            }
            if (stockQtyHelp) {
                stockQtyHelp.textContent = `1 stock = 1 ${units.singular}. Total inventory across all flavors/variants.`;
            }
            if (flavorStockHeader) {
                flavorStockHeader.textContent = `Stock (${units.plural})`;
            }
        }

        function updateSpecFieldLabels() {
            const selectedCategory = (categorySelect?.value || '').toLowerCase();
            const isEliquid = selectedCategory === 'e-liquid' || selectedCategory.includes('liquid');

            if (specFieldLabel) {
                specFieldLabel.textContent = isEliquid ? 'Capacity (ML) *' : 'Puffs Count';
            }
            if (specFieldHelp) {
                specFieldHelp.textContent = isEliquid
                    ? 'Bottle capacity (e.g. 10ML).'
                    : 'Number of puffs (for Pods/Disposable).';
            }
            if (puffsInput) {
                puffsInput.placeholder = isEliquid ? 'e.g. 10' : 'e.g. 8000';
            }
        }

        function updateDeviceSpecFields() {
            const deviceType = deviceTypeSelect?.value || '';
            const visibility = deviceFieldVisibility[deviceType] || {};

            document.querySelectorAll('.device-spec-field').forEach((field) => {
                const key = field.dataset.deviceField || '';
                field.style.display = visibility[key] ? 'block' : 'none';
            });
        }

        function toggleFlavorPuffFields() {
            const selectedCategory = categorySelect.value.toLowerCase();
            const flavorInventorySection = document.querySelector('.flavor-inventory-section');
            const stockQuantityField = document.querySelector('.stock-quantity-field');

            updateStockUnitLabels();
            updateSpecFieldLabels();

            const isDisposable = selectedCategory === 'disposable';
            const isDevice = selectedCategory === 'device';

            if (isDevice) {
                if (specField) {
                    specField.style.display = 'none';
                }
                disposableSpecFields.forEach((field) => {
                    field.style.display = 'none';
                });
                if (complianceSection) {
                    complianceSection.style.display = 'none';
                }
                if (deviceSpecSection) {
                    deviceSpecSection.style.display = 'block';
                }
                flavorInventorySection.style.display = 'none';
                stockQuantityField.style.display = 'block';
                updateDeviceSpecFields();
                return;
            }

            if (deviceSpecSection) {
                deviceSpecSection.style.display = 'none';
            }

            if (selectedCategory === 'pods' || isDisposable || selectedCategory === 'e-liquid') {
                if (specField) {
                    specField.style.display = 'block';
                }
                disposableSpecFields.forEach((field) => {
                    field.style.display = isDisposable ? 'block' : 'none';
                });
                if (complianceSection) {
                    complianceSection.style.display = 'block';
                }
                flavorInventorySection.style.display = 'block';
                stockQuantityField.style.display = 'none';

                if (selectedCategory === 'e-liquid' && puffsInput && puffsInput.value === '') {
                    puffsInput.value = '10';
                }

                updateTotalStock();
            } else {
                if (specField) {
                    specField.style.display = 'none';
                }
                disposableSpecFields.forEach((field) => {
                    field.style.display = 'none';
                });
                if (complianceSection) {
                    complianceSection.style.display = 'none';
                }
                flavorInventorySection.style.display = 'none';
                stockQuantityField.style.display = 'block';
                if (puffsInput) {
                    puffsInput.value = '';
                }
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
            updateFlavorInventorySummary();
        }

        function removeFlavorRow(button) {
            const row = button.closest('.flavor-row');
            if (document.querySelectorAll('.flavor-row').length > 1) {
                row.remove();
                updateTotalStock();
                updateFlavorInventorySummary();
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

        function countNamedFlavors() {
            let count = 0;
            document.querySelectorAll('.flavor-name-input').forEach((input) => {
                if ((input.value || '').trim() !== '') {
                    count++;
                }
            });
            return count;
        }

        function updateFlavorInventorySummary() {
            const countNode = document.getElementById('flavorInventoryCount');
            const rowCount = document.querySelectorAll('.flavor-row').length;
            const namedCount = countNamedFlavors();
            const displayCount = namedCount > 0 ? namedCount : rowCount;
            if (countNode) {
                countNode.textContent = String(displayCount);
            }
        }

        function setFlavorInventoryCollapsed(collapsed) {
            const body = document.getElementById('flavorInventoryBody');
            const toggle = document.getElementById('toggleFlavorInventoryBtn');
            if (!body || !toggle) {
                return;
            }
            body.classList.toggle('is-collapsed', collapsed);
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const toggleFlavorInventoryBtn = document.getElementById('toggleFlavorInventoryBtn');
            if (toggleFlavorInventoryBtn) {
                toggleFlavorInventoryBtn.addEventListener('click', () => {
                    const body = document.getElementById('flavorInventoryBody');
                    setFlavorInventoryCollapsed(!body?.classList.contains('is-collapsed'));
                });
            }

            const addFlavorBtn = document.getElementById('addFlavorBtn');
            if (addFlavorBtn) {
                addFlavorBtn.addEventListener('click', () => {
                    addFlavorRow();
                    updateFlavorInventorySummary();
                    setFlavorInventoryCollapsed(false);
                });
            }

            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('flavor-stock-input')) {
                    updateTotalStock();
                }
                if (e.target.classList.contains('flavor-name-input')) {
                    updateFlavorInventorySummary();
                }
            });

            updateFlavorInventorySummary();
            setFlavorInventoryCollapsed(document.querySelectorAll('.flavor-row').length > 2);
        });

        function updateExpirationWarning() {
            const expiresInput = document.getElementById('expires_at');
            const warning = document.getElementById('expirationWarning');
            if (!expiresInput || !warning) {
                return;
            }

            const value = expiresInput.value;
            const isExpired = value !== '' && value < new Date().toISOString().slice(0, 10);
            warning.classList.toggle('is-visible', isExpired);
        }

        categorySelect?.addEventListener('change', toggleFlavorPuffFields);
        deviceTypeSelect?.addEventListener('change', updateDeviceSpecFields);
        document.getElementById('expires_at')?.addEventListener('change', updateExpirationWarning);
        document.getElementById('expires_at')?.addEventListener('input', updateExpirationWarning);
        toggleFlavorPuffFields();
        updateExpirationWarning();
    </script>
</body>
</html>
