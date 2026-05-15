<?php

namespace App\Controllers\Api;

use App\Models\CartItemModel;
use App\Models\CartModel;
use App\Models\OrderModel;
use App\Models\ProductModel;
use App\Models\UserModel;

class OrderController extends BaseApiController
{
    private OrderModel $orderModel;
    private CartModel $cartModel;
    private CartItemModel $cartItemModel;
    private ProductModel $productModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->cartModel = new CartModel();
        $this->cartItemModel = new CartItemModel();
        $this->productModel = new ProductModel();
        $this->userModel = new UserModel();
    }

    public function create()
    {
        $userId = $this->currentUserId();
        $cartId = $this->cartModel->getOrCreateCartId($userId);
        $cartItems = $this->cartItemModel
            ->select('cart_items.*, p.name, p.price')
            ->join('products p', 'p.id = cart_items.product_id', 'inner')
            ->where('cart_items.cart_id', $cartId)
            ->findAll();

        if ($cartItems === []) {
            return $this->errorResponse('Cart is empty.', [], 422);
        }

        $items = [];
        $total = 0.0;

        foreach ($cartItems as $item) {
            $qty = (int) $item['quantity'];
            $price = (float) $item['price'];
            $total += ($qty * $price);
            $items[] = [
                'id' => (int) $item['product_id'],
                'name' => (string) $item['name'],
                'qty' => $qty,
                'price' => $price,
                'variant_id' => isset($item['variant_id']) ? (int) $item['variant_id'] : null,
            ];
        }

        $user = $this->userModel->find($userId);

        $payload = $this->request->getJSON(true) ?? [];
        $shipmentAddress = (string) ($payload['shipping_address'] ?? ($user['address'] ?? ''));
        $contactNumber = (string) ($payload['contact_number'] ?? ($user['phone_number'] ?? ''));

        $db = db_connect();
        $db->transStart();

        if (!$this->productModel->reserveStockForItems($items, 'order', null, $userId)) {
            $db->transRollback();
            return $this->errorResponse('Insufficient stock for one or more items.', [], 422);
        }

        $orderId = $this->orderModel->createOrder([
            'customer_id' => $userId,
            'reference_number' => 'MOB-' . date('YmdHis') . '-' . random_int(100, 999),
            'title' => 'Mobile App Order',
            'description' => 'Order placed via Android customer app',
            'order_date' => date('Y-m-d'),
            'status' => 'pending',
            'notes' => 'SOURCE:MOBILE_APP',
        ], $items, [
            'method' => 'cash',
            'status' => 'unpaid',
            'amount' => round($total, 2),
        ], [
            'status' => 'to_pay',
            'shipping_address' => $shipmentAddress,
            'contact_number' => $contactNumber,
        ]);

        if (!$orderId) {
            $db->transRollback();
            return $this->errorResponse('Failed to create order.', [], 500);
        }

        $this->cartItemModel->where('cart_id', $cartId)->delete();
        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->errorResponse('Order transaction failed.', [], 500);
        }

        return $this->successResponse($this->orderModel->getOrder((int) $orderId), 'Order created successfully', 201);
    }

    public function index()
    {
        $orders = $this->orderModel->getCustomerOrders($this->currentUserId());
        return $this->successResponse($orders, 'Orders loaded');
    }

    public function show(int $id)
    {
        $order = $this->orderModel->getOrder($id);
        if (!$order || (int) ($order['created_by'] ?? 0) !== $this->currentUserId()) {
            return $this->errorResponse('Order not found.', [], 404);
        }

        return $this->successResponse($order, 'Order loaded');
    }
}
