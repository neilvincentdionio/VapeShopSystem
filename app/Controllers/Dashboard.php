<?php

namespace App\Controllers;

use App\Models\DashboardModel;
use App\Models\UserModel;
use App\Models\ProductModel;
use App\Models\OrderModel;
use App\Libraries\SecurityAuditService;

class Dashboard extends BaseController
{
    protected $session;
    protected $dashboardModel;
    protected $userModel;
    protected $productModel;
    protected $orderModel;
    protected $securityAuditService;

    public function __construct()
    {
        $this->session = session();
        $this->dashboardModel = new DashboardModel();
        $this->userModel = new UserModel();
        $this->productModel = new ProductModel();
        $this->orderModel = new OrderModel();
        $this->securityAuditService = new SecurityAuditService();
    }

    /**
     * Check if user is logged in
     */
    private function checkAuth()
    {
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/login');
        }
        return true;
    }

    /**
     * Check session timeout and refresh activity timestamp.
     */
    private function checkSessionTimeout()
    {
        $lastActivity = $this->session->get('last_activity');
        $timeout = 30 * 60; // 30 minutes

        if ($lastActivity && (time() - $lastActivity) > $timeout) {
            $this->session->destroy();
            return redirect()->to('/login')
                ->with('error', 'Session expired. Please login again.');
        }

        $this->session->set('last_activity', time());
        return true;
    }

    /**
     * Allow access only for authenticated customer users.
     */
    private function checkCustomerAccess()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $sessionCheck = $this->checkSessionTimeout();
        if ($sessionCheck !== true) {
            return $sessionCheck;
        }

        if ((string) $this->session->get('user_role') !== 'customer') {
            return redirect()->to('/dashboard')
                ->with('error', 'Access denied. Customer area only.');
        }

        return true;
    }

    /**
     * Shared data for customer pages.
     *
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    private function getCustomerPageData(string $pageTitle, string $activePage, array $extra = []): array
    {
        $userRole = (string) $this->session->get('user_role');
        $shopName = $this->session->get('user_shop_name');
        $analyticsToday = $this->dashboardModel->getAnalytics('today', $userRole, $shopName);

        return array_merge([
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $userRole,
            'user_shop_name' => $this->session->get('user_shop_name'),
            'page_title' => $pageTitle,
            'active_page' => $activePage,
            'orders_today' => $analyticsToday['orders'] ?? 0,
            'revenue_today' => $analyticsToday['revenue'] ?? '&#8369;0.00',
            'recent_orders' => $analyticsToday['orders'] ?? 0,
            'growth_rate' => $this->dashboardModel->getGrowthRate($userRole, $shopName),
        ], $extra);
    }

    /**
     * Show dashboard
     */
    public function index()
    {
        // Check authentication
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $sessionCheck = $this->checkSessionTimeout();
        if ($sessionCheck !== true) {
            return $sessionCheck;
        }

        // Get user role information
        $userRole = $this->session->get('user_role');
        
        if (!$userRole) {
            return redirect()->to('/login')->with('error', 'Invalid user session.');
        }

        if ($userRole === 'customer') {
            return redirect()->to('/customer/home');
        }

        // Get dashboard analytics based on user role
        $shopName = $this->session->get('user_shop_name');
        $analytics = $this->dashboardModel->getAnalytics('today', $userRole, $shopName);
        $userStats = $this->dashboardModel->getUserActivityStats();
        $systemMetrics = $this->dashboardModel->getSystemMetrics();
        $monthlyAnalytics = $this->dashboardModel->getAnalytics('month', $userRole, $shopName);
        
        $totalProducts = $this->dashboardModel->getTotalProducts($userRole, $shopName);
        $recentRegistrations = $this->dashboardModel->getRecentRegistrations();

        $data = [
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $userRole,
            'user_shop_name' => $this->session->get('user_shop_name'),
            'page_title' => 'Admin Dashboard',
            // Real analytics data
            'total_products' => $totalProducts,
            'orders_today' => $analytics['orders'],
            'revenue_today' => $analytics['revenue'],
            'system_uptime' => $systemMetrics['uptime'],
            'active_sessions' => $analytics['active_sessions'],
            'new_users' => $analytics['new_users'],
            'recent_registrations' => $recentRegistrations,
            'user_stats' => $userStats,
            'system_metrics' => $systemMetrics,
            // Performance metrics
            'system_performance' => $analytics['orders'] > 0 ? '100%' : '0%',
            'notifications' => $this->getNotifications($analytics['orders'], $analytics['revenue']),
            'growth_rate' => $this->dashboardModel->getGrowthRate($userRole, $shopName),
            'recent_orders' => $analytics['orders'],
            'monthly_revenue' => $monthlyAnalytics['revenue']
        ];

        return view('admin/dashboard/index', $data);
    }

    /**
     * Customer store home page.
     */
    public function customerHome()
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        return view('customer/home', $this->getCustomerPageData('Store Home', 'home'));
    }

    /**
     * Customer products page.
     */
    public function customerProducts()
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        // Get search and filter parameters
        $search = $this->request->getGet('search') ?? '';
        $category = $this->request->getGet('category') ?? 'all';
        $allowedCategories = $this->productModel->getCategoryOptions();
        if ($category !== 'all' && !in_array($category, $allowedCategories, true)) {
            $category = 'all';
        }

        // Get products from database
        if (!empty($search)) {
            $products = $this->productModel->searchProducts($search, $category);
        } elseif ($category !== 'all') {
            $products = $this->productModel->getProductsByCategory($category);
        } else {
            $products = $this->productModel->getActiveProducts();
        }

        $categories = $allowedCategories;

        $ageAllowed = $this->canCustomerPurchase();
        $cart = $this->getCustomerCart();

        return view('customer/products', $this->getCustomerPageData('Products', 'products', [
            'products' => $products,
            'categories' => $categories,
            'search' => $search,
            'selectedCategory' => $category,
            'age_allowed' => $ageAllowed,
            'cart_items' => $cart['items'],
            'cart_total' => $cart['total'],
        ]));
    }

    /**
     * Customer orders page.
     */
    public function customerOrders()
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $userId = (int) $this->session->get('user_id');

        $activeTab = $this->request->getGet('tab') ?? 'all';
        $validTabs = ['all', 'to_pay', 'to_ship', 'to_receive', 'completed', 'cancelled', 'return_refund', 'failed_delivery'];

        if (!in_array($activeTab, $validTabs, true)) {
            $activeTab = 'all';
        }

        $orders = $this->orderModel->getCustomerOrders($userId, $activeTab === 'all' ? null : $activeTab);
        $statusCounts = $this->orderModel->getCustomerStatusCounts($userId);

        return view('customer/orders', $this->getCustomerPageData('Orders', 'orders', [
            'orders' => $orders,
            'activeTab' => $activeTab,
            'statusCounts' => $statusCounts,
        ]));
    }

    /**
     * Customer cart page.
     */
    public function customerCart()
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $cart = $this->getCustomerCart();
        $cartItems = $cart['items'];
        $estimatedTotal = $cart['total'];

        return view('customer/cart', $this->getCustomerPageData('Cart', 'cart', [
            'cart_items' => $cartItems,
            'estimated_total' => $estimatedTotal,
        ]));
    }

    /**
     * Customer: Add product to cart (AJAX).
     */
    public function customerCartAdd()
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $productId = (int) $this->request->getPost('product_id');
        $quantity = (int) ($this->request->getPost('quantity') ?? 1);
        if ($productId <= 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Invalid product.',
            ]);
        }
        if ($quantity <= 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Invalid quantity.',
            ]);
        }

        if (! $this->canCustomerPurchase()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Age verification required (18+).',
                'ageVerificationUrl' => site_url('customer/age-verification'),
            ]);
        }

        $product = $this->productModel->getProductById($productId, true);
        if (! $product) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Product not found.',
            ]);
        }

        if ((int) $product['stock'] <= 0) {
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => 'This product is out of stock.',
            ]);
        }

        $cart = $this->getCustomerCart();
        $items = $cart['raw_items'];
        $currentQty = (int) ($items[(string) $productId] ?? 0);
        $requestedQty = $currentQty + $quantity;

        if ($requestedQty > (int) $product['stock']) {
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => 'Insufficient stock.',
                'available' => (int) $product['stock'],
            ]);
        }

        $items[(string) $productId] = $requestedQty;
        $this->setCustomerCartRawItems($items);

        $cartCount = array_sum(array_map(static fn ($v) => (int) $v, $items));

        return $this->response->setStatusCode(200)->setJSON([
            'success' => true,
            'message' => 'Added to cart.',
            'cart_count' => $cartCount,
        ]);
    }

    /**
     * Customer: Update quantity in cart (non-AJAX form).
     */
    public function customerCartUpdate()
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $productId = (int) $this->request->getPost('product_id');
        $quantity = (int) ($this->request->getPost('quantity') ?? 0);

        if ($productId <= 0) {
            return redirect()->to('/customer/cart')->with('error', 'Invalid product.');
        }

        $product = $this->productModel->getProductById($productId, true);
        if (! $product) {
            return redirect()->to('/customer/cart')->with('error', 'Product not found.');
        }

        $items = $this->getCustomerCart()['raw_items'];
        if ($quantity <= 0) {
            unset($items[(string) $productId]);
        } else {
            $quantity = min($quantity, (int) $product['stock']);
            $items[(string) $productId] = $quantity;
        }

        $this->setCustomerCartRawItems($items);

        return redirect()->to('/customer/cart')->with('success', 'Cart updated successfully.');
    }

    /**
     * Customer: Remove item from cart (non-AJAX form).
     */
    public function customerCartRemove()
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $productId = (int) $this->request->getPost('product_id');
        if ($productId <= 0) {
            return redirect()->to('/customer/cart')->with('error', 'Invalid product.');
        }

        $items = $this->getCustomerCart()['raw_items'];
        unset($items[(string) $productId]);
        $this->setCustomerCartRawItems($items);

        return redirect()->to('/customer/cart')->with('success', 'Item removed from cart.');
    }

    /**
     * Customer: Direct order processing (skip checkout)
     */
    public function customerDirectOrder()
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        if (! $this->canCustomerPurchase()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Age verification required (18+).',
                'redirect' => site_url('customer/age-verification'),
            ]);
        }

        $cart = $this->getCustomerCart();
        $cartItems = $cart['items'];
        $total = (float) $cart['total'];

        if (count($cartItems) === 0 || $total <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Your cart is empty.',
            ]);
        }

        $customer = $this->userModel->find((int) $this->session->get('user_id'));
        $customerId = (int) ($customer['id'] ?? 0);
        if ($customerId <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Customer account not found.',
            ]);
        }

        $referenceNumber = $this->generateReceiptNumber();
        $orderItems = $this->mapCartItemsToOrderItems($cartItems);

        foreach ($cartItems as $item) {
            $product = $this->productModel->getProductById((int) $item['id'], true);
            if (! $product || (int) $product['stock'] < (int) $item['quantity']) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => 'Insufficient stock for one of the items.',
                ]);
            }
        }

        $orderId = $this->orderModel->createOrder(
            [
                'customer_id' => $customerId,
                'reference_number' => $referenceNumber,
                'title' => 'Direct Order',
                'description' => 'Customer direct order purchase.',
                'order_date' => date('Y-m-d'),
                'status' => 'pending',
            ],
            $orderItems,
            [
                'method' => 'cash',
                'status' => 'pending',
                'amount' => round($total, 2),
            ],
            $this->buildCustomerShipmentData($customer, [
                'status' => 'to_pay',
            ])
        );

        if (! $orderId) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Failed to create order.',
            ]);
        }

        $this->clearCustomerCart();

        return $this->response->setStatusCode(200)->setJSON([
            'success' => true,
            'message' => 'Order processed successfully!',
            'redirect' => site_url('customer/orders'),
        ]);
    }

    /**
     * Customer: Checkout (cashiering system).
     */
    public function customerCheckout()
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        if (! $this->canCustomerPurchase()) {
            return redirect()->to('/customer/age-verification')->with('error', 'Age verification required (18+).');
        }

        $cart = $this->getCustomerCart();
        if (count($cart['items']) === 0) {
            return redirect()->to('/customer/products')->with('error', 'Your cart is empty.');
        }

        return view('customer/checkout', $this->getCustomerPageData('Checkout', 'cart', [
            'cart_items' => $cart['items'],
            'estimated_total' => $cart['total'],
        ]));
    }

    /**
     * Customer: Checkout submit with payment method selection.
     */
    public function customerCheckoutSubmit()
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        if (! $this->canCustomerPurchase()) {
            return redirect()->to('/customer/age-verification')->with('error', 'Age verification required (18+).');
        }

        $paymentMethod = $this->request->getPost('payment_method');
        if (! $paymentMethod || ! in_array($paymentMethod, ['cash_on_delivery', 'gcash'])) {
            return redirect()->to('/customer/checkout')->with('error', 'Please select a valid payment method.');
        }

        $cart = $this->getCustomerCart();
        $cartItems = $cart['items'];
        $total = (float) $cart['total'];

        if (count($cartItems) === 0 || $total <= 0) {
            return redirect()->to('/customer/products')->with('error', 'Your cart is empty.');
        }

        $customer = $this->userModel->find((int) $this->session->get('user_id'));
        $customerId = (int) ($customer['id'] ?? 0);
        if ($customerId <= 0) {
            return redirect()->to('/customer/checkout')->with('error', 'Customer account not found.');
        }

        $referenceNumber = $this->generateReceiptNumber();
        $orderItems = $this->mapCartItemsToOrderItems($cartItems);
        $db = \Config\Database::connect();

        // Prepare payment data based on method
        $paymentData = [];
        $orderData = [
            'customer_id' => $customerId,
            'reference_number' => $referenceNumber,
            'order_date' => date('Y-m-d'),
        ];

        if ($paymentMethod === 'cash_on_delivery') {
            $orderData['title'] = 'Cash on Delivery Order';
            $orderData['description'] = 'Customer order with Cash on Delivery payment.';
            $orderData['status'] = 'pending';
            $orderData['notes'] = 'PAYMENT_METHOD:COD';

            $paymentData = [
                'method' => 'cash',
                'status' => 'unpaid',
                'amount' => round($total, 2),
                'amount_received' => null,
                'change_amount' => null,
            ];

            $shipmentData = $this->buildCustomerShipmentData($customer, [
                'status' => 'to_pay',
            ]);
        } elseif ($paymentMethod === 'gcash') {
            $gcashReference = trim((string) ($this->request->getPost('gcash_reference') ?? ''));
            if ($gcashReference === '' || strlen($gcashReference) < 6) {
                return redirect()->to('/customer/products')->with('error', 'Please enter a valid GCash reference number after payment.');
            }

            $orderData['title'] = 'GCash Payment Order';
            $orderData['description'] = 'Customer order with GCash payment.';
            $orderData['status'] = 'pending';
            $orderData['notes'] = 'PAYMENT_METHOD:GCASH;GCASH_NUMBER:+639365879409;GCASH_REF:' . $gcashReference;

            $paymentData = [
                'method' => 'gcash',
                'status' => 'paid',
                'amount' => round($total, 2),
                'amount_received' => round($total, 2),
                'change_amount' => 0.00,
                'paid_at' => date('Y-m-d H:i:s'),
            ];

            $shipmentData = $this->buildCustomerShipmentData($customer, [
                'status' => 'to_ship',
            ]);
        }

        $db->transStart();

        try {
            $orderId = $this->orderModel->createOrder(
                $orderData,
                $orderItems,
                $paymentData,
                $shipmentData
            );

            if (! $orderId) {
                throw new \RuntimeException('Failed to create order.');
            }

            // For GCash (paid), reserve/deduct stock immediately.
            // For COD (unpaid), stock is deducted on payment confirmation/admin checkout.
            if (
                $paymentMethod === 'gcash'
                && ! $this->productModel->reserveStockForItems(
                    $orderItems,
                    'order',
                    (int) $orderId,
                    (int) $this->session->get('user_id')
                )
            ) {
                throw new \RuntimeException('Insufficient stock for one of the selected items.');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Checkout transaction failed.');
            }

            $this->clearCustomerCart();

            $redirectTab = $paymentMethod === 'cash_on_delivery' ? 'to_pay' : 'to_ship';
            $successMessage = $paymentMethod === 'cash_on_delivery'
                ? 'Order placed successfully. COD payment is pending.'
                : 'GCash transaction successful. Your order is marked as paid.';
            return redirect()->to(site_url('customer/orders?tab=' . $redirectTab))
                ->with('success', $successMessage);
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->to('/customer/checkout')->with('error', $e->getMessage());
        }
    }

    /**
     * Customer: Receipt (printable).
     */
    public function customerReceipt($id)
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $orderId = (int) $id;
        if ($orderId <= 0) {
            return redirect()->to('/customer/products')->with('error', 'Invalid receipt.');
        }

        $order = $this->orderModel->getOrder($orderId);

        if (! $order || (int) ($order['created_by'] ?? 0) !== (int) $this->session->get('user_id')) {
            return redirect()->to('/customer/products')->with('error', 'Receipt not found.');
        }

        return view('customer/receipt', $this->getCustomerPageData('Receipt', 'cart', [
            'receipt' => $order,
        ]));
    }

    /**
     * Customer: 18+ age verification page.
     */
    public function customerAgeVerification()
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        if ($this->canCustomerPurchase()) {
            return redirect()->to('/customer/products')->with('success', 'You are already verified as 18+.');
        }

        return view('customer/age_verification', $this->getCustomerPageData('Age Verification', 'products', []));
    }

    /**
     * Customer: Confirm 18+ using date of birth.
     */
    public function customerAgeVerificationSubmit()
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $birthDate = (string) ($this->request->getPost('birth_date') ?? '');
        if ($birthDate === '') {
            return redirect()->back()->withInput()->with('error', 'Please enter your birth date.');
        }

        try {
            $birth = new \DateTime($birthDate);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Invalid birth date.');
        }

        $now = new \DateTime('today');
        $age = (int) $birth->diff($now)->y;

        if ($age < 18) {
            return redirect()->back()->withInput()->with('error', 'You must be at least 18 years old to purchase vape products.');
        }

        $userId = (int) $this->session->get('user_id');
        if ($userId <= 0) {
            return redirect()->to('/login')->with('error', 'Session expired. Please login again.');
        }

        $this->userModel->update($userId, ['legal_age_confirmed' => 1]);
        $this->session->set('age_verified', 1);

        return redirect()->to('/customer/products')->with('success', 'Age verification successful. You can now purchase vape products.');
    }

    /**
     * Build customer cart items from the raw session items.
     *
     * @return array{raw_items: array<string,int>, items: array<int,array<string,mixed>>, total: float}
     */
    private function getCustomerCart(): array
    {
        $rawCart = $this->session->get('cart');
        $rawItems = [];

        if (is_array($rawCart) && isset($rawCart['items']) && is_array($rawCart['items'])) {
            $rawItems = $rawCart['items'];
        }

        // Normalize: productId => positive int quantity
        $normalized = [];
        foreach ($rawItems as $pid => $qty) {
            $pid = (string) $pid;
            $qty = (int) $qty;
            if ($pid !== '' && $qty > 0) {
                $normalized[$pid] = $qty;
            }
        }

        $items = [];
        $total = 0.0;

        foreach ($normalized as $pid => $qty) {
            $product = $this->productModel->getProductById((int) $pid, true);
            if (! $product) {
                continue;
            }

            $lineTotal = (float) $product['price'] * (int) $qty;
            $total += $lineTotal;

            $items[] = [
                'id' => (int) $product['id'],
                'name' => (string) $product['name'],
                'price' => (float) $product['price'],
                'image' => (string) ($product['image'] ?? ''),
                'quantity' => (int) $qty,
                'amount' => round($lineTotal, 2),
                'stock' => (int) $product['stock'],
            ];
        }

        return [
            'raw_items' => $normalized,
            'items' => $items,
            'total' => round($total, 2),
        ];
    }

    /**
     * Persist raw cart items (productId => qty) in session.
     */
    private function setCustomerCartRawItems(array $items): void
    {
        $normalized = [];
        foreach ($items as $pid => $qty) {
            $pid = (string) $pid;
            $qty = (int) $qty;
            if ($pid !== '' && $qty > 0) {
                $normalized[$pid] = $qty;
            }
        }

        $this->session->set('cart', ['items' => $normalized]);
    }

    private function clearCustomerCart(): void
    {
        $this->session->remove('cart');
    }

    private function canCustomerPurchase(): bool
    {
        $userId = (int) $this->session->get('user_id');
        if ($userId <= 0) {
            return false;
        }

        $user = $this->userModel->find($userId);
        $legalAgeConfirmed = (int) ($user['legal_age_confirmed'] ?? 0) === 1;
        $sessionVerified = (int) $this->session->get('age_verified', 0) === 1;

        return $legalAgeConfirmed || $sessionVerified;
    }

    /**
     * Convert cart rows into normalized order item payloads.
     *
     * @param array<int, array<string, mixed>> $cartItems
     * @return array<int, array<string, mixed>>
     */
    private function mapCartItemsToOrderItems(array $cartItems): array
    {
        return array_map(static function (array $item): array {
            return [
                'id' => (int) ($item['id'] ?? 0),
                'qty' => (int) ($item['quantity'] ?? $item['qty'] ?? 0),
                'name' => (string) ($item['name'] ?? ('Product #' . (string) ($item['id'] ?? ''))),
                'unit_price' => (float) ($item['price'] ?? $item['unit_price'] ?? 0),
            ];
        }, $cartItems);
    }

    /**
     * Build a readable shipping address from the normalized user fields.
     */
    private function buildCustomerAddressString(array $customer): string
    {
        $parts = [];
        foreach (['address_line', 'barangay', 'city', 'province', 'postal_code', 'country'] as $field) {
            $value = trim((string) ($customer[$field] ?? ''));
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        return implode(', ', $parts);
    }

    /**
     * Return normalized customer info used by the order pages.
     */
    private function getOrderCustomerInfo(?int $customerId): ?array
    {
        if (($customerId ?? 0) <= 0) {
            return null;
        }

        $customer = $this->userModel->find((int) $customerId);
        if (! $customer) {
            return null;
        }

        $address = $this->buildCustomerAddressString($customer);

        return [
            'id' => (int) $customer['id'],
            'name' => (string) ($customer['name'] ?? ''),
            'email' => (string) ($customer['email'] ?? ''),
            'phone' => (string) ($customer['phone_number'] ?? $customer['phone'] ?? ''),
            'address' => $address !== '' ? $address : (string) ($customer['address'] ?? ''),
        ];
    }

    /**
     * Prepare shipment data using the customer's saved contact details.
     *
     * @param array<string, mixed>|null $customer
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function buildCustomerShipmentData(?array $customer, array $overrides = []): array
    {
        $shipmentData = [];

        if ($customer !== null) {
            $address = $this->buildCustomerAddressString($customer);
            if ($address !== '') {
                $shipmentData['shipping_address'] = $address;
            }

            $phoneNumber = trim((string) ($customer['phone_number'] ?? $customer['phone'] ?? ''));
            if ($phoneNumber !== '') {
                $shipmentData['contact_number'] = $phoneNumber;
            }
        }

        foreach ($overrides as $key => $value) {
            if ($value !== null && $value !== '') {
                $shipmentData[$key] = $value;
            }
        }

        return $shipmentData;
    }

    private function generateReceiptNumber(): string
    {
        $datePart = date('Ymd');
        $randomPart = random_int(1000, 9999);
        return 'RCPT-' . $datePart . '-' . $randomPart;
    }

    /**
     * Product details page for customers
     */
    public function productDetails($id)
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $product = $this->productModel->getProductById($id, true);

        if (!$product) {
            return redirect()->to('/customer/products')->with('error', 'Product not found.');
        }

        return view('customer/product_details', $this->getCustomerPageData('Product Details', 'products', [
            'product' => $product,
            'age_allowed' => $this->canCustomerPurchase(),
        ]));
    }

    /**
     * Admin orders page
     */
    public function adminOrders()
    {
        // Check authentication and admin access
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $sessionCheck = $this->checkSessionTimeout();
        if ($sessionCheck !== true) {
            return $sessionCheck;
        }

        if ($this->session->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Admin privileges required.');
        }

        $orders = $this->orderModel->getAdminOrders();
        $orderDetails = [];

        foreach ($orders as $order) {
            $customerInfo = $this->getOrderCustomerInfo(isset($order['created_by']) ? (int) $order['created_by'] : null);
            $deliveryStatus = $order['delivery_status'] ?? 'to_pay';
            $displayStatus = in_array($deliveryStatus, ['completed', 'cancelled'], true) ? $deliveryStatus : 'pending';
            $normalizedPayment = $this->normalizeOrderPayment($order);

            $orderDetails[] = [
                'id' => $order['id'],
                'reference_number' => $order['reference_number'],
                'date' => $order['date'],
                'total_amount' => $order['total_amount'],
                'payment_method' => $normalizedPayment['method'],
                'payment_status' => $normalizedPayment['status'],
                'notes' => $order['notes'] ?? '',
                'status' => $displayStatus,
                'delivery_status' => $deliveryStatus,
                'tracking_number' => $order['tracking_number'],
                'shipping_address' => $order['shipping_address'] ?: ($customerInfo['address'] ?? 'Not provided'),
                'contact_number' => $order['contact_number'] ?: ($customerInfo['phone'] ?? 'Not provided'),
                'items' => $order['items'] ?? [],
                'customer' => $customerInfo,
            ];
        }

        $data = [
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $this->session->get('user_role'),
            'user_shop_name' => $this->session->get('user_shop_name'),
            'page_title' => 'Orders Management',
            'orders' => $orderDetails,
        ];

        return view('admin/orders/index', $data);
    }

    /**
     * Admin checkout for pending orders
     */
    public function adminCheckout($orderId = null)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $sessionCheck = $this->checkSessionTimeout();
        if ($sessionCheck !== true) {
            return $sessionCheck;
        }

        if ($this->session->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Admin privileges required.');
        }

        $order = $this->orderModel->getOrder((int) $orderId);

        if (! $order || ($order['delivery_status'] ?? 'to_pay') !== 'to_pay') {
            return redirect()->to('/orders')->with('error', 'Order not found or already processed.');
        }

        $orderItems = $order['items'] ?? [];
        if ($orderItems === []) {
            return redirect()->to('/orders')->with('error', 'Order items not found.');
        }

        $data = [
            'user_name' => $this->session->get('user_name') ?? 'Admin',
            'user_email' => $this->session->get('user_email') ?? 'admin@vapeshop.com',
            'user_role' => $this->session->get('user_role') ?? 'admin',
            'user_shop_name' => $this->session->get('user_shop_name') ?? 'Vape Shop',
            'page_title' => 'Order Checkout',
            'order' => $order,
            'items' => $orderItems,
            'total' => (float) $order['total_amount'],
            'reference_number' => $order['reference_number'],
        ];

        return view('admin/orders/checkout', $data);
    }

    /**
     * Admin checkout submit - process the order and update stock
     */
    public function adminCheckoutSubmit($orderId = null)
    {
        // Check authentication and admin access
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $sessionCheck = $this->checkSessionTimeout();
        if ($sessionCheck !== true) {
            return $sessionCheck;
        }

        if ($this->session->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Admin privileges required.');
        }

        if (!$orderId) {
            return redirect()->to('/orders')->with('error', 'Order ID is required.');
        }

        $order = $this->orderModel->getOrder((int) $orderId);

        if (! $order) {
            return redirect()->to('/orders')->with('error', 'Order not found.');
        }

        if (($order['delivery_status'] ?? 'to_pay') !== 'to_pay') {
            return redirect()->to('/orders')->with('error', 'Order is already processed.');
        }

        $orderItems = $order['items'] ?? [];
        if (empty($orderItems)) {
            return redirect()->to('/orders')->with('error', 'Order items not found.');
        }

        $ageVerified = $this->request->getPost('age_verified');
        $paymentMethod = $this->request->getPost('payment_method');
        $amountReceived = (float) $this->request->getPost('amount_received');

        if (! $ageVerified) {
            return redirect()->to('/orders/checkout/' . $orderId)->with('error', 'Age verification is required.');
        }

        if (! $paymentMethod) {
            return redirect()->to('/orders/checkout/' . $orderId)->with('error', 'Payment method is required.');
        }

        if ($amountReceived < (float) $order['total_amount']) {
            return redirect()->to('/orders/checkout/' . $orderId)->with('error', 'Amount received is insufficient.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            if (! $this->productModel->reserveStockForItems(
                $orderItems,
                'order',
                (int) $orderId,
                (int) $this->session->get('user_id')
            )) {
                throw new \RuntimeException('Insufficient stock for one of the order items.');
            }

            $customer = $this->userModel->find((int) ($order['created_by'] ?? 0));
            $description = trim((string) ($order['description'] ?? ''));
            if ($description !== '') {
                $description .= ' - Processed by admin';
            } else {
                $description = 'Processed by admin';
            }
            $updateOk = $this->orderModel->updateOrder(
                (int) $orderId,
                [
                    'status' => 'completed',
                    'description' => $description,
                ],
                [
                    'method' => $paymentMethod,
                    'status' => 'paid',
                    'amount' => round((float) $order['total_amount'], 2),
                    'amount_received' => round($amountReceived, 2),
                    'change_amount' => round($amountReceived - (float) $order['total_amount'], 2),
                    'paid_at' => date('Y-m-d H:i:s'),
                ],
                $this->buildCustomerShipmentData($customer, [
                    'status' => 'to_ship',
                ])
            );

            if (! $updateOk) {
                throw new \RuntimeException('Failed to update order status.');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Checkout processing failed.');
            }

            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'message' => 'Payment processed successfully! Order completed and stock updated.',
                'order_id' => (int) $orderId,
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Settings page
     */
    public function settings()
    {
        // Check authentication
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $sessionCheck = $this->checkSessionTimeout();
        if ($sessionCheck !== true) {
            return $sessionCheck;
        }

        $userRole = (string) $this->session->get('user_role');

        if ($userRole === 'customer') {
            // For customers, redirect to profile
            return redirect()->to('/dashboard/profile');
        }

        // For admin, show settings page
        $data = [
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $userRole,
            'user_shop_name' => $this->session->get('user_shop_name'),
            'page_title' => 'Settings'
        ];

        return view('admin/dashboard/settings', $data);
    }

    /**
     * Update delivery status (AJAX endpoint for admin)
     */
    public function updateDeliveryStatus()
    {
        // Check authentication and admin access
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $sessionCheck = $this->checkSessionTimeout();
        if ($sessionCheck !== true) {
            return $sessionCheck;
        }

        if ($this->session->get('user_role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin privileges required.'
            ]);
        }

        $input = $this->request->getJSON();
        $orderId = (int) ($input->order_id ?? 0);
        $newStatus = $input->status ?? '';

        if (!$orderId || !$newStatus) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invalid request parameters. Order ID: ' . $orderId . ', Status: ' . $newStatus
            ]);
        }

        $validStatuses = ['to_pay', 'to_ship', 'to_receive', 'completed', 'failed_delivery'];
        if (!in_array($newStatus, $validStatuses)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invalid delivery status: ' . $newStatus . '. Valid statuses are: ' . implode(', ', $validStatuses)
            ]);
        }

        $order = $this->orderModel->getOrder($orderId);

        if (! $order) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Order not found.',
            ]);
        }

        $paymentMethod = strtolower((string) ($order['payment_method'] ?? 'cash'));
        $orderNotes = (string) ($order['notes'] ?? '');
        $isCodOrder = in_array($paymentMethod, ['cash', 'cod', 'cash_on_delivery'], true)
            || str_contains($orderNotes, 'PAYMENT_METHOD:COD');

        if (
            ($order['delivery_status'] ?? 'to_pay') === 'to_pay'
            && $newStatus === 'to_ship'
            && ($order['payment_status'] ?? 'unpaid') !== 'paid'
            && ! $isCodOrder
        ) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Process checkout first before shipping this order.',
            ]);
        }

        $customer = $this->userModel->find((int) ($order['created_by'] ?? 0));
        $shipmentData = $this->buildCustomerShipmentData($customer, []);
        if ($newStatus === 'to_ship' && empty($order['tracking_number'])) {
            $shipmentData['tracking_number'] = $this->generateTrackingNumber();
        }

        try {
            // COD: mark as paid only when parcel is delivered.
            if ($newStatus === 'completed' && $isCodOrder && ($order['payment_status'] ?? 'unpaid') !== 'paid') {
                $result = $this->orderModel->updateOrder(
                    $orderId,
                    ['status' => 'completed'],
                    [
                        'status' => 'paid',
                        'amount' => round((float) ($order['total_amount'] ?? 0), 2),
                        'amount_received' => round((float) ($order['total_amount'] ?? 0), 2),
                        'change_amount' => 0.00,
                        'paid_at' => date('Y-m-d H:i:s'),
                    ],
                    array_merge($shipmentData, [
                        'status' => 'completed',
                        'delivered_at' => date('Y-m-d H:i:s'),
                    ])
                );
            } else {
                $result = $this->orderModel->updateDeliveryStatus($orderId, $newStatus, $shipmentData);
            }

            if ($result) {
                return $this->response->setStatusCode(200)->setJSON([
                    'success' => true,
                    'message' => 'Delivery status updated successfully.',
                ]);
            }

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Failed to update delivery status.',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Delivery status update error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'An error occurred while updating the delivery status.',
            ]);
        }
    }

    /**
     * Get delivery information for an order
     */
    public function getDeliveryInfo($orderId)
    {
        // Check authentication and admin access
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        if ($this->session->get('user_role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin privileges required.'
            ]);
        }

        $order = $this->orderModel->getOrder((int) $orderId);

        if (! $order) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Order not found.',
            ]);
        }

        $customerPhone = '';
        $customerAddress = '';
        if (! empty($order['created_by'])) {
            $customer = $this->userModel->find($order['created_by']);
            if ($customer !== null) {
                $customerPhone = $customer['phone_number'] ?? $customer['phone'] ?? '';
                $customerAddress = $this->buildCustomerAddressString($customer);
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'tracking_number' => $order['tracking_number'] ?? '',
            'shipping_address' => $order['shipping_address'] ?: $customerAddress,
            'contact_number' => $order['contact_number'] ?: $customerPhone,
        ]);
    }

    /**
     * Save delivery information for an order
     */
    public function saveDeliveryInfo()
    {
        // Check authentication and admin access
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        if ($this->session->get('user_role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin privileges required.'
            ]);
        }

        $input = $this->request->getJSON();
        $orderId = (int) ($input->order_id ?? 0);

        if (!$orderId) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invalid order ID.'
            ]);
        }

        $order = $this->orderModel->getOrder($orderId);

        if (! $order) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Order not found.',
            ]);
        }

        try {
            $shipmentData = [];

            if (! empty($input->tracking_number)) {
                $shipmentData['tracking_number'] = trim((string) $input->tracking_number);
            }

            if (! empty($input->shipping_address)) {
                $shipmentData['shipping_address'] = trim((string) $input->shipping_address);
            }

            if (! empty($input->contact_number)) {
                $shipmentData['contact_number'] = trim((string) $input->contact_number);
            }

            if (! empty($input->delivery_notes)) {
                $currentNotes = trim((string) ($order['shipment_notes'] ?? $order['notes'] ?? ''));
                $newNote = trim((string) $input->delivery_notes);
                $shipmentData['notes'] = $currentNotes === ''
                    ? $newNote
                    : $currentNotes . PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . $newNote;
            }

            if (! empty($shipmentData['tracking_number']) && ($order['delivery_status'] ?? 'to_pay') === 'to_pay') {
                $shipmentData['status'] = 'to_ship';
            }

            if ($shipmentData === []) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'No changes to save.',
                ]);
            }

            $result = isset($shipmentData['status'])
                ? $this->orderModel->updateDeliveryStatus($orderId, (string) $shipmentData['status'], $shipmentData)
                : $this->orderModel->updateOrder($orderId, [], [], $shipmentData);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Delivery information saved successfully.',
                ]);
            }

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Failed to save delivery information.',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Save delivery info error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'An error occurred while saving delivery information.',
            ]);
        }
    }

    /**
     * Admin order details view with delivery management
     */
    public function viewAdminOrderDetails($orderId)
    {
        // Check authentication and admin access
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $sessionCheck = $this->checkSessionTimeout();
        if ($sessionCheck !== true) {
            return $sessionCheck;
        }

        if ($this->session->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Admin privileges required.');
        }

        $order = $this->orderModel->getOrder((int) $orderId);

        if (! $order) {
            return redirect()->to('/orders')->with('error', 'Order not found.');
        }

        $normalizedPayment = $this->normalizeOrderPayment($order);
        $order['payment_method'] = $normalizedPayment['method'];
        $order['payment_status'] = $normalizedPayment['status'];

        $data = [
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $this->session->get('user_role'),
            'user_shop_name' => $this->session->get('user_shop_name'),
            'page_title' => 'Order Details - Admin',
            'order' => $order,
            'items' => $order['items'] ?? [],
        ];

        return view('admin/orders/order_details', $data);
    }

    /**
     * Ensure GCash orders are always reflected as gcash + paid in admin views.
     *
     * @param array<string, mixed> $order
     * @return array{method:string,status:string}
     */
    private function normalizeOrderPayment(array $order): array
    {
        $method = strtolower((string) ($order['payment_method'] ?? 'cash'));
        $status = strtolower((string) ($order['payment_status'] ?? 'unpaid'));
        $notes = (string) ($order['notes'] ?? '');

        if (str_contains($notes, 'PAYMENT_METHOD:GCASH') || str_contains($notes, 'GCASH_REF:')) {
            $method = 'gcash';
            $status = 'paid';
        }

        return [
            'method' => $method !== '' ? $method : 'cash',
            'status' => $status !== '' ? $status : 'unpaid',
        ];
    }

    private function generateTrackingNumber(): string
    {
        $datePart = date('Ymd');
        $randomPart = random_int(1000, 9999);
        return 'TRK-' . $datePart . '-' . $randomPart;
    }

    /**
     * Profile page
     */
    public function profile()
    {
        // Check authentication
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $sessionCheck = $this->checkSessionTimeout();
        if ($sessionCheck !== true) {
            return $sessionCheck;
        }

        $userRole = (string) $this->session->get('user_role');

        if ($userRole === 'customer') {
            $customerAccount = $this->userModel->find((int) $this->session->get('user_id'));

            return view('customer/profile', $this->getCustomerPageData('Profile', 'profile', [
                'customer_account' => $customerAccount,
            ]));
        }

        $data = [
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $userRole,
            'user_shop_name' => $this->session->get('user_shop_name'),
            'page_title' => 'Profile'
        ];

        return view('admin/dashboard/profile', $data);
    }

    /**
     * Update customer profile details from profile settings.
     */
    public function updateCustomerProfile()
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $userId = (int) $this->session->get('user_id');
        $customer = $this->userModel->find($userId);

        if (!$customer || (string) ($customer['role'] ?? '') !== 'customer') {
            return redirect()->to('/dashboard/profile')->with('error', 'Customer account not found.');
        }

        $name = trim((string) $this->request->getPost('name'));
        $email = trim((string) $this->request->getPost('email'));
        $phoneNumber = trim((string) $this->request->getPost('phone_number'));
        $addressLine = trim((string) $this->request->getPost('address_line'));
        $city = trim((string) $this->request->getPost('city'));
        $country = trim((string) $this->request->getPost('country'));
        $barangay = trim((string) $this->request->getPost('barangay'));
        $province = trim((string) $this->request->getPost('province'));
        $postalCode = trim((string) $this->request->getPost('postal_code'));
        $newPassword = (string) $this->request->getPost('new_password');
        $confirmPassword = (string) $this->request->getPost('confirm_password');

        $input = [
            'name' => $name,
            'email' => $email,
            'phone_number' => $phoneNumber,
            'address_line' => $addressLine,
            'city' => $city,
            'country' => $country,
            'barangay' => $barangay,
            'province' => $province,
            'postal_code' => $postalCode,
            'new_password' => $newPassword,
            'confirm_password' => $confirmPassword,
        ];

        $rules = [
            'name' => 'required|min_length[3]|max_length[255]|regex_match[/^[\p{L}\p{M}\p{N}\s\-\.\'’]+$/u]',
            'email' => 'required|valid_email|is_unique[users.email,id,' . $userId . ']',
            'phone_number' => 'permit_empty|max_length[30]|regex_match[/^[0-9+\-\s\(\)]+$/]',
            'address_line' => 'permit_empty|max_length[255]|regex_match[/^[a-zA-Z0-9\s\-\.\'#,\/]+$/]',
            'city' => 'permit_empty|max_length[120]|regex_match[/^[a-zA-Z0-9\s\-\.\']+$/]',
            'country' => 'permit_empty|max_length[120]|regex_match[/^[a-zA-Z0-9\s\-\.\']+$/]',
            'barangay' => 'permit_empty|max_length[120]|regex_match[/^[a-zA-Z0-9\s\-\.\']+$/]',
            'province' => 'permit_empty|max_length[120]|regex_match[/^[a-zA-Z0-9\s\-\.\']+$/]',
            'postal_code' => 'permit_empty|max_length[20]|regex_match[/^[a-zA-Z0-9\s\-]+$/]',
            'new_password' => 'permit_empty|min_length[8]',
            'confirm_password' => 'permit_empty|matches[new_password]',
        ];

        $messages = [
            'confirm_password' => [
                'matches' => 'Confirm password does not match the new password.',
            ],
        ];

        if (!$this->validateData($input, $rules, $messages)) {
            return redirect()->to('/dashboard/profile')
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'name' => $name,
            'email' => $email,
            'phone_number' => $phoneNumber,
            'address_line' => $addressLine,
            'city' => $city,
            'country' => $country,
            'barangay' => $barangay,
            'province' => $province,
            'postal_code' => $postalCode,
        ];

        if ($newPassword !== '') {
            $updateData['password'] = $newPassword;
        }

        if (!$this->userModel->update($userId, $updateData)) {
            return redirect()->to('/dashboard/profile')
                ->withInput()
                ->with('error', 'Failed to update profile. Please try again.');
        }

        $this->session->set([
            'user_name' => $name,
            'user_email' => $email,
        ]);

        return redirect()->to('/dashboard/profile')->with('success', 'Profile updated successfully.');
    }

    
    /**
     * Order action handlers for Shopee-like delivery process
     */
    public function customerOrderAction($orderId, $action)
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $order = $this->orderModel->getOrder((int) $orderId);

        if (! $order) {
            log_message('error', 'Order not found for orderId: ' . $orderId);
            return redirect()->to('/customer/orders')->with('error', 'Order not found.');
        }

        if ((int) ($order['created_by'] ?? 0) !== (int) $this->session->get('user_id')) {
            log_message('error', 'Access denied for orderId: ' . $orderId);
            return redirect()->to('/customer/orders')->with('error', 'Order not found.');
        }

        log_message('info', 'Processing action: ' . $action . ' for order: ' . $orderId);

        switch ($action) {
            case 'pay':
                return $this->processOrderPayment($order);
            
            case 'cancel':
                return $this->cancelOrder($order);
            
            case 'confirm':
                return $this->confirmOrderReceived($order);
            
            case 'reorder':
                return $this->reorderItems($order);
            
            case 'review':
                return $this->reviewOrder($order);
            
            case 'view':
                return $this->viewOrderDetailsPrivate($order);
            
            default:
                log_message('error', 'Invalid action: ' . $action);
                return redirect()->to('/customer/orders')->with('error', 'Invalid action.');
        }
    }

    private function processOrderPayment($order)
    {
        if ($order['delivery_status'] !== 'to_pay') {
            return redirect()->to('/customer/orders')->with('error', 'Order cannot be paid.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            if (! $this->productModel->reserveStockForItems(
                (array) ($order['items'] ?? []),
                'order',
                (int) ($order['id'] ?? 0),
                (int) $this->session->get('user_id')
            )) {
                throw new \RuntimeException('Insufficient stock for one of the order items.');
            }

            $customer = $this->userModel->find((int) ($order['created_by'] ?? 0));
            $updated = $this->orderModel->updateOrder(
                (int) $order['id'],
                ['status' => 'completed'],
                [
                    'status' => 'paid',
                    'amount' => round((float) ($order['total_amount'] ?? 0), 2),
                    'amount_received' => round((float) ($order['total_amount'] ?? 0), 2),
                    'change_amount' => 0.00,
                    'paid_at' => date('Y-m-d H:i:s'),
                ],
                $this->buildCustomerShipmentData($customer, [
                    'status' => 'to_ship',
                ])
            );

            if (! $updated) {
                throw new \RuntimeException('Unable to update the order payment status.');
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Payment transaction failed.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->to('/customer/orders')->with('error', $e->getMessage());
        }

        return redirect()->to('/customer/orders')->with('success', 'Payment processed successfully. Order is now ready for shipping.');
    }

    private function cancelOrder($order)
    {
        if (!in_array($order['delivery_status'], ['to_pay', 'to_ship'])) {
            return redirect()->to('/customer/orders')->with('error', 'Order cannot be cancelled.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            if (($order['delivery_status'] ?? 'to_pay') === 'to_ship') {
                if (! $this->productModel->restoreStockForItems(
                    (array) ($order['items'] ?? []),
                    'order',
                    (int) ($order['id'] ?? 0),
                    (int) $this->session->get('user_id')
                )) {
                    throw new \RuntimeException('Failed to restore stock for a cancelled order.');
                }
            }

            $updated = $this->orderModel->updateOrder(
                (int) $order['id'],
                ['status' => 'cancelled'],
                [],
                ['status' => 'cancelled']
            );

            if (! $updated) {
                throw new \RuntimeException('Unable to cancel this order.');
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Cancellation transaction failed.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->to('/customer/orders')->with('error', $e->getMessage());
        }

        return redirect()->to('/customer/orders')->with('success', 'Order cancelled successfully.');
    }

    private function confirmOrderReceived($order)
    {
        if ($order['delivery_status'] !== 'to_receive') {
            return redirect()->to('/customer/orders')->with('error', 'Order cannot be confirmed.');
        }
        $orderId = (int) ($order['id'] ?? 0);
        if ($orderId <= 0) {
            return redirect()->to('/customer/orders')->with('error', 'Invalid order.');
        }

        $paymentMethod = strtolower((string) ($order['payment_method'] ?? 'cash'));
        $orderNotes = (string) ($order['notes'] ?? '');
        $isCodOrder = in_array($paymentMethod, ['cash', 'cod', 'cash_on_delivery'], true)
            || str_contains($orderNotes, 'PAYMENT_METHOD:COD');

        $shipmentData = [
            'status' => 'completed',
            'delivered_at' => date('Y-m-d H:i:s'),
        ];

        // Customer "Order Received" should finalize order.
        // For COD, payment is collected upon delivery, so mark as paid here.
        if ($isCodOrder && ($order['payment_status'] ?? 'unpaid') !== 'paid') {
            $updated = $this->orderModel->updateOrder(
                $orderId,
                ['status' => 'completed'],
                [
                    'status' => 'paid',
                    'amount' => round((float) ($order['total_amount'] ?? 0), 2),
                    'amount_received' => round((float) ($order['total_amount'] ?? 0), 2),
                    'change_amount' => 0.00,
                    'paid_at' => date('Y-m-d H:i:s'),
                ],
                $shipmentData
            );
        } else {
            $updated = $this->orderModel->updateDeliveryStatus($orderId, 'completed', $shipmentData);
        }

        if (! $updated) {
            return redirect()->to('/customer/orders')->with('error', 'Failed to confirm order received.');
        }

        return redirect()->to('/customer/orders?tab=completed')->with('success', 'Order received successfully. Order is now completed.');
    }

    private function reorderItems($order)
    {
        $cartItems = [];

        foreach (($order['items'] ?? []) as $item) {
            $productId = (int) ($item['id'] ?? 0);
            $quantity = (int) ($item['qty'] ?? 0);
            if ($productId > 0 && $quantity > 0) {
                $cartItems[$productId] = $quantity;
            }
        }

        $this->setCustomerCartRawItems($cartItems);

        return redirect()->to('/customer/cart')->with('success', 'Items added to cart. You can now checkout.');
    }

    private function reviewOrder($order)
    {
        // For now, redirect to orders with a message
        // In a real implementation, this would go to a review page
        return redirect()->to('/customer/orders')->with('info', 'Review feature coming soon!');
    }

    /**
     * Public method for viewing order details
     */
    public function viewOrderDetails($orderId)
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $order = $this->orderModel->getOrder((int) $orderId);

        if (! $order) {
            log_message('error', 'Order not found for orderId: ' . $orderId);
            return redirect()->to('/customer/orders')->with('error', 'Order not found.');
        }

        if ((int) ($order['created_by'] ?? 0) !== (int) $this->session->get('user_id')) {
            log_message('error', 'Access denied for orderId: ' . $orderId);
            return redirect()->to('/customer/orders')->with('error', 'Order not found.');
        }

        return $this->viewOrderDetailsPrivate($order);
    }

    private function viewOrderDetailsPrivate($order)
    {
        $orderItems = $order['items'] ?? [];

        $trackingInfo = [];
        if ($order['delivery_status'] === 'to_ship') {
            $trackingInfo = [
                'status' => 'Preparing for shipment',
                'estimated_date' => date('F j, Y', strtotime('+3 days')),
                'message' => 'Your order is being prepared and will be shipped soon.',
            ];
        } elseif ($order['delivery_status'] === 'to_receive') {
            $trackingInfo = [
                'status' => 'Out for delivery',
                'estimated_date' => date('F j, Y', strtotime('+1 day')),
                'message' => 'Your order is out for delivery.',
            ];
        }

        return view('customer/order_details', $this->getCustomerPageData('Order Details', 'orders', [
            'order' => $order,
            'items' => $orderItems,
            'tracking_info' => $trackingInfo,
        ]));
    }

    /**
     * Get notifications based on real data
     */
    private function getNotifications($ordersToday, $revenueToday)
    {
        $notifications = [];

        if ((int) $ordersToday > 0) {
            $notifications[] = ['type' => 'success', 'message' => $ordersToday . ' order(s) recorded today.'];
            $notifications[] = ['type' => 'info', 'message' => 'Today revenue: ' . $revenueToday];
        }

        try {
            $alerts = $this->securityAuditService->getSuspiciousAlerts(24);
            if ($alerts !== []) {
                $notifications[] = [
                    'type' => 'warning',
                    'message' => count($alerts) . ' security alert(s) detected in the last 24 hours.',
                ];
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load security notifications: {message}', ['message' => $e->getMessage()]);
        }

        return $notifications;
    }
}
