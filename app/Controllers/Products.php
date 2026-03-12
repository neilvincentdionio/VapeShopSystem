<?php

namespace App\Controllers;

use App\Models\ProductModel;

class Products extends BaseController
{
    protected $session;
    protected $productModel;

    public function __construct()
    {
        $this->session = session();
        $this->productModel = new ProductModel();
        helper(['text', 'form']);
    }

    /**
     * Display all products (Admin)
     */
    public function index()
    {
        $products = $this->productModel->getAllProducts();
        $data = [
            'title' => 'Product Management',
            'products' => $products,
            'categories' => $this->productModel->getCategories(),
            'lowStockProducts' => $this->productModel->getLowStockProducts(),
            'totalProducts' => $this->productModel->countAllProducts(),
            'activeProducts' => $this->productModel->countActiveProducts(),
        ];

        return view('admin/products/index', $data);
    }

    /**
     * Show create product form
     */
    public function create()
    {
        $data = [
            'title' => 'Add New Product',
            'categories' => $this->productModel->getCategoryOptions(),
        ];

        return view('admin/products/create', $data);
    }

    /**
     * Store new product
     */
    public function store()
    {
        $validation = $this->validate([
            'name' => 'required|min_length[3]|max_length[255]',
            'category' => 'required|in_list[Devices,Pods,E-Liquid,Disposable,Accessories]',
            'description' => 'max_length[1000]',
            'price' => 'required|numeric|greater_than_equal_to[0]',
            'stock' => 'required|integer|greater_than_equal_to[0]',
            'status' => 'required|in_list[active,inactive]',
            'image' => 'uploaded[image]|max_size[image,2048]|is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png,image/webp]'
        ]);

        if (!$validation) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Handle image upload
        $imageFile = $this->request->getFile('image');
        $imageName = null;

        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            $imageName = $imageFile->getRandomName();
            $imageFile->move('uploads/products', $imageName);
        }

        $productData = [
            'name' => $this->request->getPost('name'),
            'category' => $this->request->getPost('category'),
            'description' => $this->request->getPost('description'),
            'price' => $this->request->getPost('price'),
            'stock' => $this->request->getPost('stock'),
            'status' => $this->request->getPost('status'),
            'image' => $imageName
        ];

        if ($this->productModel->insert($productData)) {
            return redirect()->to('/products')->with('success', 'Product created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create product.');
    }

    /**
     * Show edit product form
     */
    public function edit($id)
    {
        $product = $this->productModel->find($id);

        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        $data = [
            'title' => 'Edit Product',
            'product' => $product,
            'categories' => $this->productModel->getCategoryOptions(),
        ];

        return view('admin/products/edit', $data);
    }

    /**
     * Update product
     */
    public function update($id)
    {
        $product = $this->productModel->find($id);

        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        $validationRules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'category' => 'required|in_list[Devices,Pods,E-Liquid,Disposable,Accessories]',
            'description' => 'max_length[1000]',
            'price' => 'required|numeric|greater_than_equal_to[0]',
            'stock' => 'required|integer|greater_than_equal_to[0]',
            'status' => 'required|in_list[active,inactive]'
        ];

        // Only validate image if a new one is uploaded
        if ($this->request->getFile('image')->getSize() > 0) {
            $validationRules['image'] = 'max_size[image,2048]|is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png,image/webp]';
        }

        $validation = $this->validate($validationRules);

        if (!$validation) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Handle image upload
        $imageFile = $this->request->getFile('image');
        $imageName = $product['image']; // Keep existing image by default

        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            // Delete old image if exists
            if ($product['image'] && file_exists('uploads/products/' . $product['image'])) {
                unlink('uploads/products/' . $product['image']);
            }

            $imageName = $imageFile->getRandomName();
            $imageFile->move('uploads/products', $imageName);
        }

        $productData = [
            'name' => $this->request->getPost('name'),
            'category' => $this->request->getPost('category'),
            'description' => $this->request->getPost('description'),
            'price' => $this->request->getPost('price'),
            'stock' => $this->request->getPost('stock'),
            'status' => $this->request->getPost('status'),
            'image' => $imageName
        ];

        if ($this->productModel->update($id, $productData)) {
            return redirect()->to('/products')->with('success', 'Product updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update product.');
    }

    /**
     * Delete product
     */
    public function delete($id)
    {
        $product = $this->productModel->find($id);

        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        // Delete product image if exists
        if ($product['image'] && file_exists('uploads/products/' . $product['image'])) {
            unlink('uploads/products/' . $product['image']);
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
        $product = $this->productModel->find($id);

        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        $newStatus = $product['status'] === 'active' ? 'inactive' : 'active';

        if ($this->productModel->update($id, ['status' => $newStatus])) {
            $statusText = $newStatus === 'active' ? 'activated' : 'deactivated';
            return redirect()->to('/products')->with('success', "Product {$statusText} successfully.");
        }

        return redirect()->to('/products')->with('error', 'Failed to update product status.');
    }
}
