<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\ReviewModel;
use App\Libraries\NotificationService;

class Products extends BaseController
{
    private const PRODUCT_IMAGE_UPLOAD_DIR = 'uploads/products';

    protected $session;
    protected $productModel;
    protected $reviewModel;
    protected NotificationService $notificationService;

    public function __construct()
    {
        $this->session = session();
        $this->productModel = new ProductModel();
        $this->reviewModel = new ReviewModel();
        $this->notificationService = new NotificationService();
        helper(['text', 'form']);
    }

    /**
     * Display all products (Admin)
     */
    public function index()
    {
        $guard = $this->enforcePermission('view_products');
        if ($guard !== true) {
            return $guard;
        }

        $products = $this->productModel->getAllProducts();
        $productIds = array_values(array_unique(array_map(static fn (array $product): int => (int) ($product['id'] ?? 0), $products)));
        $data = [
            'title' => 'Product Management',
            'products' => $products,
            'categories' => $this->productModel->getCategories(),
            'lowStockProducts' => $this->productModel->getLowStockProducts(),
            'totalProducts' => $this->productModel->countAllProducts(),
            'activeProducts' => $this->productModel->countActiveProducts(),
            'reviewSummaries' => $this->reviewModel->getAdminReviewDataForProducts($productIds),
            'reviewNotification' => $this->reviewModel->getAdminReviewNotificationSummary(),
        ];

        return view('admin/products/index', $data);
    }

    /**
     * Show create product form
     */
    public function create()
    {
        $guard = $this->enforcePermission('create_products');
        if ($guard !== true) {
            return $guard;
        }

        $data = [
            'title' => 'Add New Product',
            'categories' => $this->productModel->getCategoryOptions(),
            'brands' => $this->productModel->getBrandOptions(),
        ];

        return view('admin/products/create', $data);
    }

    /**
     * Show read-only product details for admins.
     */
    public function view($id)
    {
        $guard = $this->enforcePermission('view_products');
        if ($guard !== true) {
            return $guard;
        }

        $product = $this->productModel->getProductBaseById($id);

        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        $variants = $this->productModel->getProductVariants((int) $id);

        $data = [
            'title' => 'View Product',
            'product' => $product,
            'variants' => $variants,
            'reviewSummary' => $this->reviewModel->getProductReviewSummary((int) $id),
            'productReviews' => $this->reviewModel->getReviewsForProduct((int) $id),
        ];

        return view('admin/products/view', $data);
    }

    /**
     * Store new product
     */
    public function store()
    {
        $guard = $this->enforcePermission('create_products');
        if ($guard !== true) {
            return $guard;
        }

        $validation = $this->validate([
            'name' => 'required|min_length[3]|max_length[255]',
            'category' => 'required|in_list[Device,Pods,E-Liquid,Disposable]',
            'brand' => 'required|max_length[100]',
            'flavor' => 'permit_empty|max_length[100]',
            'unit_price' => 'required|numeric|greater_than_equal_to[0]',
            'selling_price' => 'required|numeric|greater_than_equal_to[0]',
            'puffs' => 'permit_empty|integer|greater_than_equal_to[0]',
            'stock_qty' => 'required|integer|greater_than_equal_to[0]',
            'is_active' => 'required|in_list[0,1]',
            'image' => 'permit_empty|max_size[image,4096]|is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png,image/webp,image/gif]'
        ]);

        if (!$validation) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $imageFile = $this->request->getFile('image');
        $imageName = $this->storeProductImage($imageFile);
        $pricing = $this->resolveProductPricing();

        $productData = [
            'name' => $this->request->getPost('name'),
            'category' => $this->request->getPost('category'),
            'brand' => $this->request->getPost('brand'),
            'flavor' => $this->request->getPost('flavor'),
            'unit_price' => $pricing['unit_price'],
            'selling_price' => $pricing['selling_price'],
            'price' => $pricing['price'],
            'puffs' => $this->request->getPost('puffs') ?: null,
            'stock_qty' => $this->request->getPost('stock_qty'),
            'is_active' => $this->request->getPost('is_active') ?: 1,
            'image_url' => $imageName
        ];

        // Handle flavors for products that use flavor inventory
        $flavors = $this->normalizeFlavorInput($this->request->getPost('flavors') ?? []);
        $usesFlavorInventory = $this->usesFlavorInventory((string) $this->request->getPost('category'));

        if ($usesFlavorInventory) {
            // Calculate total stock from flavors
            $totalStock = array_sum(array_column($flavors, 'stock'));
            $productData['stock_qty'] = $totalStock;

            // Flavor rows are stored in product_variants.
            $productData['flavor'] = null;
        }

        $productId = $this->productModel->insert($productData);

        if (
            $productId
            && (
                ! $usesFlavorInventory
                || $this->productModel->syncProductVariants((int) $productId, $flavors, [
                    'price' => $productData['selling_price'],
                    'puffs' => $productData['puffs'],
                    'is_active' => $productData['is_active'],
                ])
            )
        ) {
            $this->rememberProductImage(
                (string) $productData['name'],
                $imageName
            );

            return redirect()->to('/products')->with('success', 'Product created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create product.');
    }

    /**
     * Show edit product form
     */
    public function edit($id)
    {
        $guard = $this->enforcePermission('update_products');
        if ($guard !== true) {
            return $guard;
        }

        $product = $this->productModel->getProductBaseById($id);

        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        $product['flavors'] = $this->formatFlavorRows($this->productModel->getProductVariants((int) $id));

        // Backward compatibility for products saved before product_variants existed.
        if ($product['flavors'] === [] && !empty($product['flavor'])) {
            $decodedFlavors = json_decode($product['flavor'], true);
            if (is_array($decodedFlavors)) {
                $product['flavors'] = $this->formatLegacyFlavorRows($decodedFlavors);
            }
        }

        $data = [
            'title' => 'Edit Product',
            'product' => $product,
            'categories' => $this->productModel->getCategoryOptions(),
            'brands' => $this->productModel->getBrandOptions(),
        ];

        return view('admin/products/edit', $data);
    }

    /**
     * Update product
     */
    public function update($id)
    {
        $guard = $this->enforcePermission('update_products');
        if ($guard !== true) {
            return $guard;
        }

        $product = $this->productModel->find($id);

        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        $validationRules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'category' => 'required|in_list[Device,Pods,E-Liquid,Disposable]',
            'brand' => 'required|max_length[100]',
            'flavor' => 'permit_empty|max_length[100]',
            'unit_price' => 'required|numeric|greater_than_equal_to[0]',
            'selling_price' => 'required|numeric|greater_than_equal_to[0]',
            'puffs' => 'permit_empty|integer|greater_than_equal_to[0]',
            'stock_qty' => 'required|integer|greater_than_equal_to[0]',
            'is_active' => 'required|in_list[0,1]'
        ];

        // Only validate image if a new one is uploaded
        if ($this->request->getFile('image')->getSize() > 0) {
            $validationRules['image'] = 'max_size[image,2048]|is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png,image/webp]';
        }

        $validation = $this->validate($validationRules);

        if (!$validation) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $imageFile = $this->request->getFile('image');
        $imageName = $product['image_url']; // Keep existing image by default

        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            $imageName = $this->storeProductImage($imageFile);
        }

        $pricing = $this->resolveProductPricing();

        $productData = [
            'name' => $this->request->getPost('name'),
            'category' => $this->request->getPost('category'),
            'brand' => $this->request->getPost('brand'),
            'flavor' => $this->request->getPost('flavor'),
            'unit_price' => $pricing['unit_price'],
            'selling_price' => $pricing['selling_price'],
            'price' => $pricing['price'],
            'puffs' => $this->request->getPost('puffs') ?: null,
            'stock_qty' => $this->request->getPost('stock_qty'),
            'is_active' => $this->request->getPost('is_active') ?: 1,
            'image_url' => $imageName
        ];

        // Handle flavors for products that use flavor inventory
        $flavors = $this->normalizeFlavorInput($this->request->getPost('flavors') ?? []);
        $usesFlavorInventory = $this->usesFlavorInventory((string) $this->request->getPost('category'));
        
        if ($usesFlavorInventory) {
            // Calculate total stock from flavors
            $totalStock = array_sum(array_column($flavors, 'stock'));
            $productData['stock_qty'] = $totalStock;

            // Flavor rows are stored in product_variants.
            $productData['flavor'] = null;

            if (
                $this->productModel->update($id, $productData)
                && $this->productModel->syncProductVariants((int) $id, $flavors, [
                    'price' => $productData['selling_price'],
                    'puffs' => $productData['puffs'],
                    'is_active' => $productData['is_active'],
                ])
            ) {
                $this->rememberProductImage(
                    (string) $productData['name'],
                    $imageName,
                    (string) ($product['name'] ?? '')
                );

                return redirect()->to('/products')->with('success', 'Product updated successfully.');
            }
        } else {
            // Update regular product
            if (
                $this->productModel->update($id, $productData)
                && $this->productModel->syncProductVariants((int) $id, [])
            ) {
                $this->rememberProductImage(
                    (string) $productData['name'],
                    $imageName,
                    (string) ($product['name'] ?? '')
                );

                return redirect()->to('/products')->with('success', 'Product updated successfully.');
            }
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update product.');
    }

    /**
     * Delete product
     */
    public function delete($id)
    {
        $guard = $this->enforcePermission('delete_products');
        if ($guard !== true) {
            return $guard;
        }

        $product = $this->productModel->find($id);

        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        if ($this->productModel->delete($id)) {
            return redirect()->to('/products')->with('success', 'Product deleted successfully.');
        }

        return redirect()->to('/products')->with('error', 'Failed to delete product.');
    }

    /**
     * Toggle product status (active/inactive)
     */
    public function toggleStatus($id)
    {
        $guard = $this->enforcePermission('update_products');
        if ($guard !== true) {
            return $guard;
        }

        $product = $this->productModel->find($id);

        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        $newStatus = (int) $product['is_active'] === 1 ? 0 : 1;

        if ($this->productModel->update($id, ['is_active' => $newStatus])) {
            $statusText = $newStatus === 1 ? 'activated' : 'deactivated';
            return redirect()->to('/products')->with('success', "Product {$statusText} successfully.");
        }

        return redirect()->to('/products')->with('error', 'Failed to update product status.');
    }

    public function replyReview($reviewId)
    {
        $guard = $this->enforcePermission('view_products');
        if ($guard !== true) {
            return $guard;
        }

        $reviewId = (int) $reviewId;
        $reply = trim((string) $this->request->getPost('admin_reply'));
        $review = $this->reviewModel->find($reviewId);

        if (! $review) {
            return redirect()->to('/products')->with('error', 'Review not found.');
        }

        $saved = $this->reviewModel->update($reviewId, [
            'admin_reply' => $reply !== '' ? mb_substr($reply, 0, 1000) : null,
            'replied_by' => $reply !== '' ? (int) $this->session->get('user_id') : null,
            'replied_at' => $reply !== '' ? date('Y-m-d H:i:s') : null,
        ]);

        if (! $saved) {
            return redirect()->back()->with('error', 'Failed to save admin reply.');
        }
        if ($reply !== '') {
            $this->notificationService->notifyUsers([(int) ($review['user_id'] ?? 0)], [
                'category' => 'messages',
                'type' => 'review_reply',
                'title' => 'Admin replied to your review',
                'message' => 'An admin replied to your product review.',
                'link' => site_url('customer/product/' . (int) ($review['product_id'] ?? 0) . '?review_id=' . $reviewId . '#admin-reply-' . $reviewId),
                'related_type' => 'review',
                'related_id' => $reviewId,
            ]);
        }

        return redirect()->to('/products/view/' . (int) ($review['product_id'] ?? 0))
            ->with('success', $reply !== '' ? 'Reply saved.' : 'Reply removed.');
    }

    private function enforcePermission(string $permissionName)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        if (!$this->hasPermission($permissionName)) {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Insufficient permission.');
        }

        return true;
    }

    private function storeProductImage($imageFile): ?string
    {
        if (! $imageFile || ! $imageFile->isValid() || $imageFile->hasMoved()) {
            return null;
        }

        $uploadPath = FCPATH . self::PRODUCT_IMAGE_UPLOAD_DIR;
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        $imageName = $imageFile->getRandomName();
        $imageFile->move($uploadPath, $imageName);

        return $imageName;
    }

    private function rememberProductImage(string $productName, ?string $imageName, string $oldProductName = ''): void
    {
        $productName = trim($productName);
        $imageName = normalize_product_image_path($imageName, true);

        if ($productName === '' || $imageName === null) {
            return;
        }

        $mapPath = WRITEPATH . 'product_image_seed_map.json';
        $map = [];

        if (is_file($mapPath)) {
            $decoded = json_decode((string) file_get_contents($mapPath), true);
            if (is_array($decoded)) {
                $map = $decoded;
            }
        }

        $oldProductName = trim($oldProductName);
        if ($oldProductName !== '' && $oldProductName !== $productName) {
            unset($map[$oldProductName]);
        }

        $map[$productName] = $imageName;
        ksort($map, SORT_NATURAL | SORT_FLAG_CASE);

        file_put_contents(
            $mapPath,
            json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function usesFlavorInventory(string $category): bool
    {
        return in_array(strtolower($category), ['pods', 'disposable', 'e-liquid'], true);
    }

    private function normalizeFlavorInput($flavors): array
    {
        if (! is_array($flavors)) {
            return [];
        }

        $normalized = [];

        foreach ($flavors as $flavor) {
            if (! is_array($flavor)) {
                continue;
            }

            $name = trim((string) ($flavor['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $normalized[] = [
                'id' => (int) ($flavor['id'] ?? 0),
                'name' => $name,
                'stock' => max(0, (int) ($flavor['stock'] ?? 0)),
            ];
        }

        return $normalized;
    }

    private function formatFlavorRows(array $variants): array
    {
        return array_map(static function (array $variant): array {
            return [
                'id' => (int) ($variant['id'] ?? 0),
                'name' => $variant['flavor'] ?? '',
                'stock' => (int) ($variant['stock_qty'] ?? 0),
            ];
        }, array_values(array_filter($variants, static fn (array $variant): bool => trim((string) ($variant['flavor'] ?? '')) !== '')));
    }

    private function formatLegacyFlavorRows(array $flavors): array
    {
        return array_values(array_filter(array_map(static function ($flavor): array {
            if (! is_array($flavor)) {
                return ['id' => 0, 'name' => '', 'stock' => 0];
            }

            $name = $flavor['name'] ?? $flavor['flavor_name'] ?? '';

            return [
                'id' => 0,
                'name' => $name,
                'stock' => (int) ($flavor['stock'] ?? $flavor['flavor_stock'] ?? 0),
            ];
        }, $flavors), static fn (array $flavor): bool => trim((string) ($flavor['name'] ?? '')) !== ''));
    }

    private function resolveProductPricing(): array
    {
        $unitPrice = (float) $this->request->getPost('unit_price');
        $sellingPrice = (float) $this->request->getPost('selling_price');

        return [
            'unit_price' => $unitPrice,
            'selling_price' => $sellingPrice,
            'price' => $sellingPrice,
        ];
    }
}
