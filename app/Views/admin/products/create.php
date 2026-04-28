<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Add New Product') ?> - Quick Puff Vape Shop System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --main-font: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        body {
            font-family: var(--main-font);
            background: #ffffff;
            min-height: 100vh;
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
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #e0e0e0;
            color: #666;
        }

        .btn-outline:hover {
            background: #f8f9fa;
            border-color: #27c56f;
            color: #27c56f;
        }

        .form-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            border: 1px solid #e0e0e0;
            overflow: hidden;
        }

        .form-card-header {
            padding: 1.5rem 2rem;
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
        }

        .form-card-header h3 {
            margin: 0 0 0.5rem 0;
            color: #333;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .form-card-header p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }

        .product-form {
            padding: 2rem;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group.col-md-8 { flex: 2; }
        .form-group.col-md-4 { flex: 1; }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .required { color: #f44336; }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: border-color 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #00bcd4;
            box-shadow: 0 0 0 3px rgba(0, 188, 212, 0.1);
        }

        .input-group {
            display: flex;
            align-items: stretch;
        }

        .input-group-text {
            padding: 0.75rem;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-right: none;
            border-radius: 8px 0 0 8px;
            font-weight: 600;
        }

        .input-group .form-control {
            border-radius: 0 8px 8px 0;
        }

        .image-upload-area {
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
        }

        .image-preview {
            margin-top: 1rem;
            min-height: 200px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
        }

        .preview-placeholder {
            text-align: center;
            color: #666;
        }

        .preview-placeholder i {
            font-size: 48px;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #e0e0e0;
        }

        .alert {
            margin: 1rem 2rem;
            padding: 1rem 1.25rem;
            border-radius: 8px;
            border-left: 4px solid;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border-left-color: #dc3545;
            color: #dc3545;
        }

        @media (max-width: 768px) {
            .form-row { flex-direction: column; gap: 0; }
            .product-form { padding: 1rem; }
            .form-actions { flex-direction: column; }
            .page-header-content { flex-direction: column; align-items: flex-start; }
        }
    </style>
    <?= $this->include('admin/partials/sidebar_styles') ?>
</head>
<body>
    <?= $this->include('admin/partials/sidebar') ?>

    <div class="container">
        <div class="page-header">
            <div class="page-header-content">
                <div class="page-title">
                    <h1>Add New Product</h1>
                    <p>Add a new product to your inventory</p>
                </div>
                <div class="page-actions">
                    <a href="<?= site_url('products') ?>" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to Products
                    </a>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <h3>Product Information</h3>
                <p>Fill in the details below to add a new product</p>
            </div>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger">
                    <strong>Please fix the following errors:</strong>
                    <ul style="margin-top: 10px; margin-bottom: 0;">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('products/store') ?>" method="post" enctype="multipart/form-data" class="product-form">
                <?= csrf_field() ?>

                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label for="name" class="form-label">Product Name <span class="required">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" value="<?= old('name') ?>" placeholder="Enter product name" required>
                    </div>

                    <div class="form-group col-md-4">
                        <label for="category" class="form-label">Category <span class="required">*</span></label>
                        <select name="category" id="category" class="form-control" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= esc($category) ?>" <?= old('category') === $category ? 'selected' : '' ?>>
                                    <?= esc($category) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="4" placeholder="Enter product description (optional)"><?= old('description') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="price" class="form-label">Price (PHP) <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">PHP</span>
                            <input type="number" name="price" id="price" class="form-control" value="<?= old('price') ?>" placeholder="0.00" step="0.01" min="0" required>
                        </div>
                    </div>

                    <div class="form-group col-md-4">
                        <label for="stock" class="form-label">Stock Quantity <span class="required">*</span></label>
                        <input type="number" name="stock" id="stock" class="form-control" value="<?= old('stock') ?>" placeholder="0" min="0" required>
                    </div>

                    <div class="form-group col-md-4">
                        <label for="status" class="form-label">Status <span class="required">*</span></label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="">Select status</option>
                            <option value="active" <?= old('status') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="image" class="form-label">Product Image</label>
                    <div class="image-upload-area">
                        <input type="file" name="image" id="image" class="form-control" accept="image/*">
                        <div class="image-preview" id="imagePreview">
                            <div class="preview-placeholder">
                                <i class="fas fa-image"></i>
                                <p>Image preview will appear here</p>
                            </div>
                        </div>
                    </div>
                    <small>Accepted formats: JPG, JPEG, PNG, WebP. Maximum file size: 2MB.</small>
                </div>

                <div class="form-actions">
                    <a href="<?= site_url('products') ?>" class="btn btn-outline"><i class="fas fa-times"></i> Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('imagePreview');
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                preview.innerHTML = '<img src="' + event.target.result + '" style="max-width: 100%; max-height: 200px; object-fit: contain;">';
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '<div class="preview-placeholder"><i class="fas fa-image"></i><p>Image preview will appear here</p></div>';
        }
    });
    </script>
</body>
</html>
