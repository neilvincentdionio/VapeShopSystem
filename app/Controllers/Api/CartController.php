<?php

namespace App\Controllers\Api;

use App\Models\CartItemModel;
use App\Models\CartModel;
use App\Models\ProductModel;

class CartController extends BaseApiController
{
    private CartModel $cartModel;
    private CartItemModel $cartItemModel;
    private ProductModel $productModel;

    public function __construct()
    {
        $this->cartModel = new CartModel();
        $this->cartItemModel = new CartItemModel();
        $this->productModel = new ProductModel();
    }

    public function index()
    {
        return $this->successResponse($this->loadCart($this->currentUserId()), 'Cart loaded');
    }

    public function create()
    {
        $input = $this->request->getJSON(true) ?? [];
        $productId = (int) ($input['product_id'] ?? 0);
        $quantity = max(1, (int) ($input['quantity'] ?? 1));
        $variantId = (int) ($input['variant_id'] ?? 0);

        $product = $this->productModel->getProductById($productId, true);
        if (!$product) {
            return $this->errorResponse('Product not found.', [], 404);
        }

        $cartId = $this->cartModel->getOrCreateCartId($this->currentUserId());

        $builder = $this->cartItemModel->where('cart_id', $cartId)->where('product_id', $productId);
        if ($variantId > 0) {
            $builder->where('variant_id', $variantId);
        } else {
            $builder->where('variant_id', null);
        }
        $existing = $builder->first();

        if (is_array($existing)) {
            $newQty = (int) $existing['quantity'] + $quantity;
            $this->cartItemModel->update((int) $existing['id'], ['quantity' => $newQty]);
        } else {
            $this->cartItemModel->insert([
                'cart_id' => $cartId,
                'product_id' => $productId,
                'variant_id' => $variantId > 0 ? $variantId : null,
                'quantity' => $quantity,
            ]);
        }

        return $this->successResponse($this->loadCart($this->currentUserId()), 'Item added to cart', 201);
    }

    public function update(int $id)
    {
        $input = $this->request->getJSON(true) ?? [];
        $quantity = (int) ($input['quantity'] ?? 0);

        $item = $this->findOwnedItem($id);
        if (!$item) {
            return $this->errorResponse('Cart item not found.', [], 404);
        }

        if ($quantity <= 0) {
            $this->cartItemModel->delete($id);
            return $this->successResponse($this->loadCart($this->currentUserId()), 'Item removed');
        }

        $this->cartItemModel->update($id, ['quantity' => $quantity]);

        return $this->successResponse($this->loadCart($this->currentUserId()), 'Cart updated');
    }

    public function delete(int $id)
    {
        $item = $this->findOwnedItem($id);
        if (!$item) {
            return $this->errorResponse('Cart item not found.', [], 404);
        }

        $this->cartItemModel->delete($id);
        return $this->successResponse($this->loadCart($this->currentUserId()), 'Item removed');
    }

    private function findOwnedItem(int $id): ?array
    {
        $item = $this->cartItemModel->find($id);
        if (!is_array($item)) {
            return null;
        }

        $cart = $this->cartModel->find((int) $item['cart_id']);
        if (!is_array($cart) || (int) $cart['user_id'] !== $this->currentUserId()) {
            return null;
        }

        return $item;
    }

    private function loadCart(int $userId): array
    {
        $cartId = $this->cartModel->getOrCreateCartId($userId);

        $items = $this->cartItemModel
            ->select('cart_items.id, cart_items.product_id, cart_items.variant_id, cart_items.quantity, p.name, p.category, p.price, p.stock_qty, p.image_url')
            ->join('products p', 'p.id = cart_items.product_id', 'inner')
            ->where('cart_items.cart_id', $cartId)
            ->findAll();

        $total = 0.0;
        foreach ($items as &$item) {
            $item['price'] = (float) $item['price'];
            $item['subtotal'] = round($item['price'] * (int) $item['quantity'], 2);
            $item['image'] = !empty($item['image_url']) ? base_url(ltrim((string) $item['image_url'], '/')) : null;
            $total += $item['subtotal'];
        }

        return [
            'items' => $items,
            'total' => round($total, 2),
        ];
    }
}
