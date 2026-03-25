<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Edit Product') ?> - E-Commerce Vape Shop System</title>
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
            padding: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            background: #f8f9fa;
        }

        .form-card-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333333;
        }

        .form-card-body {
            padding: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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
                <h3>Product Details</h3>
            </div>
            <div class="form-card-body">
                <?= form_open_multipart('products/update/' . $product['id']) ?>
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
                                            <?= old('category', $product['category']) === $category ? 'selected' : '' ?>>
                                        <?= esc($category) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($validation) && $validation->hasError('category')): ?>
                                <div class="invalid-feedback"><?= $validation->getError('category') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="price">Price (₱) *</label>
                            <input type="number" name="price" id="price" class="form-control" 
                                   value="<?= old('price', $product['price']) ?>" 
                                   step="0.01" min="0" required>
                            <?php if (isset($validation) && $validation->hasError('price')): ?>
                                <div class="invalid-feedback"><?= $validation->getError('price') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="stock">Stock Quantity *</label>
                            <input type="number" name="stock" id="stock" class="form-control" 
                                   value="<?= old('stock', $product['stock']) ?>" 
                                   min="0" required>
                            <?php if (isset($validation) && $validation->hasError('stock')): ?>
                                <div class="invalid-feedback"><?= $validation->getError('stock') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="active" <?= old('status', $product['status']) === 'active' ? 'selected' : '' ?>>
                                    Active
                                </option>
                                <option value="inactive" <?= old('status', $product['status']) === 'inactive' ? 'selected' : '' ?>>
                                    Inactive
                                </option>
                            </select>
                            <?php if (isset($validation) && $validation->hasError('status')): ?>
                                <div class="invalid-feedback"><?= $validation->getError('status') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="image">Product Image</label>
                            <input type="file" name="image" id="image" class="form-control" 
                                   accept="image/*">
                            <small class="text-muted">Leave empty to keep current image</small>
                            <?php if ($product['image']): ?>
                                <br><small class="text-muted">Current: <?= esc($product['image']) ?></small>
                            <?php endif; ?>
                            <?php if (isset($validation) && $validation->hasError('image')): ?>
                                <div class="invalid-feedback"><?= $validation->getError('image') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" class="form-control" 
                                  rows="4"><?= old('description', $product['description']) ?></textarea>
                        <?php if (isset($validation) && $validation->hasError('description')): ?>
                            <div class="invalid-feedback"><?= $validation->getError('description') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Product
                        </button>
                        <a href="<?= site_url('products') ?>" class="btn btn-secondary" style="margin-left: 1rem;">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</body>
</html>




