<?= $this->include('partials/header') ?>

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
            <i class="fas fa-exclamation-circle"></i>
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
                <label for="name" class="form-label">
                    Product Name <span class="required">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       class="form-control <?= session()->getFlashdata('errors.name') ? 'is-invalid' : '' ?>" 
                       value="<?= old('name') ?>" 
                       placeholder="Enter product name" 
                       required>
                <?php if (session()->getFlashdata('errors.name')): ?>
                    <div class="invalid-feedback">
                        <?= session()->getFlashdata('errors.name') ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group col-md-4">
                <label for="category" class="form-label">
                    Category <span class="required">*</span>
                </label>
                <select name="category" 
                        id="category" 
                        class="form-control <?= session()->getFlashdata('errors.category') ? 'is-invalid' : '' ?>" 
                        required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= esc($category) ?>" <?= old('category') === $category ? 'selected' : '' ?>>
                            <?= esc($category) ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="Devices">Devices</option>
                    <option value="Pods">Pods</option>
                    <option value="E-Liquid">E-Liquid</option>
                    <option value="Disposable">Disposable</option>
                    <option value="Accessories">Accessories</option>
                </select>
                <?php if (session()->getFlashdata('errors.category')): ?>
                    <div class="invalid-feedback">
                        <?= session()->getFlashdata('errors.category') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="form-group">
            <label for="description" class="form-label">
                Description
            </label>
            <textarea name="description" 
                      id="description" 
                      class="form-control <?= session()->getFlashdata('errors.description') ? 'is-invalid' : '' ?>" 
                      rows="4" 
                      placeholder="Enter product description (optional)"><?= old('description') ?></textarea>
            <?php if (session()->getFlashdata('errors.description')): ?>
                <div class="invalid-feedback">
                    <?= session()->getFlashdata('errors.description') ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="price" class="form-label">
                    Price (₱) <span class="required">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" 
                           name="price" 
                           id="price" 
                           class="form-control <?= session()->getFlashdata('errors.price') ? 'is-invalid' : '' ?>" 
                           value="<?= old('price') ?>" 
                           placeholder="0.00" 
                           step="0.01" 
                           min="0" 
                           required>
                </div>
                <?php if (session()->getFlashdata('errors.price')): ?>
                    <div class="invalid-feedback">
                        <?= session()->getFlashdata('errors.price') ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group col-md-4">
                <label for="stock" class="form-label">
                    Stock Quantity <span class="required">*</span>
                </label>
                <input type="number" 
                       name="stock" 
                       id="stock" 
                       class="form-control <?= session()->getFlashdata('errors.stock') ? 'is-invalid' : '' ?>" 
                       value="<?= old('stock') ?>" 
                       placeholder="0" 
                       min="0" 
                       required>
                <?php if (session()->getFlashdata('errors.stock')): ?>
                    <div class="invalid-feedback">
                        <?= session()->getFlashdata('errors.stock') ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group col-md-4">
                <label for="status" class="form-label">
                    Status <span class="required">*</span>
                </label>
                <select name="status" 
                        id="status" 
                        class="form-control <?= session()->getFlashdata('errors.status') ? 'is-invalid' : '' ?>" 
                        required>
                    <option value="">Select status</option>
                    <option value="active" <?= old('status') === 'active' ? 'selected' : '' ?>>
                        Active
                    </option>
                    <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>
                        Inactive
                    </option>
                </select>
                <?php if (session()->getFlashdata('errors.status')): ?>
                    <div class="invalid-feedback">
                        <?= session()->getFlashdata('errors.status') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="form-group">
            <label for="image" class="form-label">
                Product Image
            </label>
            <div class="image-upload-area">
                <input type="file" 
                       name="image" 
                       id="image" 
                       class="form-control <?= session()->getFlashdata('errors.image') ? 'is-invalid' : '' ?>" 
                       accept="image/*">
                <?php if (session()->getFlashdata('errors.image')): ?>
                    <div class="invalid-feedback">
                        <?= session()->getFlashdata('errors.image') ?>
                    </div>
                <?php endif; ?>
                <div class="image-preview" id="imagePreview">
                    <div class="preview-placeholder">
                        <i class="fas fa-image"></i>
                        <p>Image preview will appear here</p>
                    </div>
                </div>
            </div>
            <small class="form-text text-muted">
                Accepted formats: JPG, JPEG, PNG, WebP. Maximum file size: 2MB.
            </small>
        </div>
        
        <div class="form-actions">
            <a href="<?= site_url('products') ?>" class="btn btn-outline">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Add Product
            </button>
        </div>
    </form>
</div>

<style>
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
    
    .form-group.col-md-8 {
        flex: 2;
    }
    
    .form-group.col-md-4 {
        flex: 1;
    }
    
    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #333;
    }
    
    .required {
        color: #f44336;
    }
    
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
    
    .form-control.is-invalid {
        border-color: #f44336;
    }
    
    .invalid-feedback {
        color: #f44336;
        font-size: 0.8rem;
        margin-top: 0.25rem;
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
        transition: border-color 0.2s ease;
    }
    
    .image-upload-area:hover {
        border-color: #00bcd4;
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
    
    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            gap: 0;
        }
        
        .product-form {
            padding: 1rem;
        }
        
        .form-actions {
            flex-direction: column;
        }
    }
</style>

<script>
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('imagePreview');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" style="max-width: 100%; max-height: 200px; object-fit: contain;">';
        }
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '<div class="preview-placeholder"><i class="fas fa-image"></i><p>Image preview will appear here</p></div>';
    }
});
</script>

<?= $this->include('partials/footer') ?>
