<?php

namespace App\Controllers\Api;

use App\Models\ProductModel;

class ProductController extends BaseApiController
{
    private ProductModel $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
    }

    public function index()
    {
        $products = $this->productModel->getCustomerProducts();
        $items = array_map(fn (array $p): array => $this->mapProduct($p), $products);

        return $this->successResponse($items, 'Products loaded');
    }

    public function show(int $id)
    {
        $product = $this->productModel->getProductById($id, true);
        if (!$product) {
            return $this->errorResponse('Product not found.', [], 404);
        }

        return $this->successResponse($this->mapProduct($product), 'Product loaded');
    }

    public function categories()
    {
        $categories = $this->productModel->getCategoryOptions();
        return $this->successResponse($categories, 'Categories loaded');
    }

    private function mapProduct(array $p): array
    {
        $image = (string) ($p['image'] ?? $p['image_url'] ?? '');
        $imageUrl = str_starts_with($image, 'http') ? $image : ($image !== '' ? base_url(ltrim($image, '/')) : null);

        return [
            'id' => (int) ($p['id'] ?? 0),
            'name' => (string) ($p['name'] ?? ''),
            'category' => (string) ($p['category'] ?? ''),
            'price' => (float) ($p['price'] ?? 0),
            'stock' => (int) ($p['stock'] ?? $p['stock_qty'] ?? 0),
            'image' => $imageUrl,
            'brand' => $p['brand'] ?? null,
            'flavor' => $p['flavor'] ?? null,
            'nicotine_level' => $p['nicotine_level'] ?? null,
            'expires_at' => $p['expires_at'] ?? null,
            'puffs' => isset($p['puffs']) ? (int) $p['puffs'] : null,
            'battery_capacity' => isset($p['battery_capacity']) ? (int) $p['battery_capacity'] : null,
            'eliquid_capacity' => isset($p['eliquid_capacity']) ? (int) $p['eliquid_capacity'] : null,
            'device_type' => $p['device_type'] ?? null,
            'wattage_range' => $p['wattage_range'] ?? null,
            'charging_port' => $p['charging_port'] ?? null,
            'compatibility' => $p['compatibility'] ?? null,
            'variants' => $p['variants'] ?? [],
        ];
    }
}
