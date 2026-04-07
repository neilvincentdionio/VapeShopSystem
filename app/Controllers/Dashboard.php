<?php

namespace App\Controllers;

use App\Models\DashboardModel;
use App\Models\UserModel;
use App\Models\ProductModel;
use App\Models\RecordModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Database\BaseConnection;

class Dashboard extends BaseController
{
    protected $session;
    protected $dashboardModel;
    protected $userModel;
    protected $productModel;

    public function __construct()
    {
        $this->session = session();
        $this->dashboardModel = new DashboardModel();
        $this->userModel = new UserModel();
        $this->productModel = new ProductModel();
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
        $recordModel = new RecordModel();
        
        // Get active tab from query parameter
        $activeTab = $this->request->getGet('tab') ?? 'all';
        $validTabs = ['all', 'to_pay', 'to_ship', 'to_receive', 'completed', 'cancelled', 'return_refund'];
        
        if (!in_array($activeTab, $validTabs)) {
            $activeTab = 'all';
        }
        
        // Get orders based on active tab
        $orders = $recordModel->getOrdersByDeliveryStatus($userId, $activeTab === 'all' ? null : $activeTab);
        
        // Get status counts for badges
        $statusCounts = $recordModel->getOrderStatusCounts($userId);

        // Parse order items from notes
        $orderDetails = [];
        foreach ($orders as $order) {
            $orderItems = [];
            $notes = $order['notes'] ?? '';
            
            if (!empty($notes)) {
                $decoded = json_decode($notes, true);
                if ($decoded && isset($decoded['items'])) {
                    $orderItems = $decoded['items'];
                }
            }
            
            $orderDetails[] = [
                'id' => $order['id'],
                'reference_number' => $order['reference_number'],
                'date' => $order['created_at'],
                'total_amount' => $order['total_amount'],
                'payment_method' => $order['payment_method'],
                'status' => $order['status'],
                'delivery_status' => $order['delivery_status'] ?? 'to_pay',
                'tracking_number' => $order['tracking_number'],
                'shipping_address' => $order['shipping_address'],
                'contact_number' => $order['contact_number'],
                'items' => $orderItems
            ];
        }

        return view('customer/orders', $this->getCustomerPageData('Orders', 'orders', [
            'orders' => $orderDetails,
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

        $referenceNumber = $this->generateReceiptNumber();

        $receiptItems = array_map(static function (array $item): array {
            return [
                'id' => (int) $item['id'],
                'qty' => (int) $item['quantity'],
                'name' => (string) ($item['name'] ?? ('Product #' . (string) ($item['id'] ?? ''))),
                'unit_price' => (float) $item['price'],
            ];
        }, $cartItems);

        $notesPayload = [
            'items' => $receiptItems,
            'total' => round($total, 2),
            'direct_order' => true,
        ];

        // Ensure RecordModel's notes max_length[1000] validation won't fail.
        $notes = json_encode($notesPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($notes === false) {
            $notes = '';
        }
        if (strlen($notes) > 950) {
            $notesPayload['items'] = array_slice($receiptItems, 0, 10);
            $notes = json_encode($notesPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $recordModel = new RecordModel();
        $db = \Config\Database::connect();

        $db->transStart();

        try {
            $totalQty = (int) array_sum(array_column($cartItems, 'quantity'));
            $unitPrice = $totalQty > 0 ? round($total / $totalQty, 2) : 0.00;

            $insertOk = $recordModel->insert([
                'record_type' => 'sales',
                'date' => date('Y-m-d'),
                'record_date' => date('Y-m-d'),
                'reference_number' => $referenceNumber,
                'title' => 'Direct Order',
                'description' => 'Customer direct order purchase.',
                'quantity' => $totalQty,
                'unit_price' => $unitPrice,
                'total_amount' => round($total, 2),
                'payment_method' => 'cash',
                'payment_status' => 'pending',
                'status' => 'pending',
                'delivery_status' => 'to_pay',
                'notes' => $notes,
                'created_by' => $customerId > 0 ? $customerId : null,
            ]);

            if (! $insertOk) {
                // Get validation errors if any
                $errors = $recordModel->errors();
                $errorMsg = 'Failed to create order.';
                if (!empty($errors)) {
                    $errorMsg .= ' Validation errors: ' . implode(', ', $errors);
                }
                throw new \RuntimeException($errorMsg);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Order processing failed.');
            }

            $this->clearCustomerCart();

            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'message' => 'Order processed successfully!',
                'redirect' => site_url('customer/orders'),
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
     * Customer: Checkout submit (cash only) with change calculation + receipt.
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

        $cashGiven = (float) ($this->request->getPost('cash_given') ?? 0);
        if ($cashGiven <= 0) {
            return redirect()->to('/customer/checkout')->with('error', 'Please enter a valid cash amount.');
        }

        $cart = $this->getCustomerCart();
        $cartItems = $cart['items'];
        $total = (float) $cart['total'];

        if (count($cartItems) === 0 || $total <= 0) {
            return redirect()->to('/customer/products')->with('error', 'Your cart is empty.');
        }

        if ($cashGiven < $total) {
            return redirect()->to('/customer/checkout')->with('error', 'Cash amount is not enough. Please provide sufficient cash.');
        }

        $change = $cashGiven - $total;

        $customer = $this->userModel->find((int) $this->session->get('user_id'));
        $customerId = (int) ($customer['id'] ?? 0);

        $referenceNumber = $this->generateReceiptNumber();

        $receiptItems = array_map(static function (array $item): array {
            return [
                'id' => (int) $item['id'],
                'qty' => (int) $item['quantity'],
                'name' => (string) ($item['name'] ?? ('Product #' . (string) ($item['id'] ?? ''))),
                'unit_price' => (float) $item['price'],
            ];
        }, $cartItems);

        $notesPayload = [
            'items' => $receiptItems,
            'cash_given' => round($cashGiven, 2),
            'change' => round($change, 2),
            'total' => round($total, 2),
        ];

        // Ensure RecordModel's notes max_length[1000] validation won't fail.
        $notes = json_encode($notesPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($notes === false) {
            $notes = '';
        }
        if (strlen($notes) > 950) {
            $notesPayload['items'] = array_slice($receiptItems, 0, 10);
            $notes = json_encode($notesPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $recordModel = new RecordModel();
        $db = \Config\Database::connect();

        $db->transStart();

        try {
            // Re-validate stock at checkout time.
            foreach ($cartItems as $item) {
                $product = $this->productModel->getProductById((int) $item['id'], true);
                if (! $product || (int) $product['stock'] < (int) $item['quantity']) {
                    throw new \RuntimeException('Insufficient stock for one of the items.');
                }

                // updateStock uses: stock = stock + $quantity, so pass negative to decrement.
                $ok = $this->productModel->updateStock((int) $item['id'], -((int) $item['quantity']));
                if (! $ok) {
                    throw new \RuntimeException('Failed to update stock.');
                }
            }

            $totalQty = (int) array_sum(array_column($cartItems, 'quantity'));
            $unitPrice = $totalQty > 0 ? round($total / $totalQty, 2) : 0.00;

            $insertOk = $recordModel->insert([
                'record_type' => 'sales',
                'date' => date('Y-m-d'),
                'record_date' => date('Y-m-d'),
                'reference_number' => $referenceNumber,
                'title' => 'Cash Sale',
                'description' => 'Customer checkout purchase.',
                'quantity' => $totalQty,
                'unit_price' => $unitPrice,
                'total_amount' => round($total, 2),
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'status' => 'completed',
                'delivery_status' => 'to_ship',
                'notes' => $notes,
                'created_by' => $customerId > 0 ? $customerId : null,
            ]);

            if (! $insertOk) {
                throw new \RuntimeException('Failed to create sales record.');
            }

            $recordId = (int) $recordModel->getInsertID();
            
            // Auto-populate customer info for logged-in customers
            if ($customerId > 0) {
                $order = $recordModel->find($recordId);
                $this->autoPopulateCustomerInfo($recordId, $order);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Checkout transaction failed.');
            }

            $this->clearCustomerCart();

            return redirect()->to(site_url('customer/receipt/' . $recordId));
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

        $recordId = (int) $id;
        if ($recordId <= 0) {
            return redirect()->to('/customer/products')->with('error', 'Invalid receipt.');
        }

        $recordModel = new RecordModel();
        $record = $recordModel->find($recordId);

        if (! $record || ($record['record_type'] ?? '') !== 'sales') {
            return redirect()->to('/customer/products')->with('error', 'Receipt not found.');
        }

        return view('customer/receipt', $this->getCustomerPageData('Receipt', 'cart', [
            'receipt' => $record,
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

        $recordModel = new RecordModel();
        $userModel = new UserModel();
        
        // Get all sales orders
        $orders = $recordModel
            ->where('record_type', 'sales')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Parse order items and get customer info
        $orderDetails = [];
        foreach ($orders as $order) {
            $orderItems = [];
            $notes = $order['notes'] ?? '';
            $customerInfo = null;
            
            if (!empty($notes)) {
                $decoded = json_decode($notes, true);
                if ($decoded && isset($decoded['items'])) {
                    $orderItems = $decoded['items'];
                }
            }

            // Get customer information
            if (!empty($order['created_by'])) {
                $customer = $userModel->find($order['created_by']);
                if ($customer) {
                    $customerInfo = [
                        'id' => $customer['id'],
                        'name' => $customer['name'],
                        'email' => $customer['email'],
                        'phone' => $customer['phone'] ?? '',
                        'address' => $customer['address'] ?? ''
                    ];
                }
            }
            
            $orderDetails[] = [
                'id' => $order['id'],
                'reference_number' => $order['reference_number'],
                'date' => $order['created_at'],
                'total_amount' => $order['total_amount'],
                'payment_method' => $order['payment_method'],
                'status' => $order['status'],
                'delivery_status' => $order['delivery_status'] ?? 'to_pay',
                'tracking_number' => $order['tracking_number'],
                'shipping_address' => $order['shipping_address'] ?: ($customerInfo['address'] ?: 'Not provided'),
                'contact_number' => $order['contact_number'] ?: ($customerInfo['phone'] ?: 'Not provided'),
                'items' => $orderItems,
                'customer' => $customerInfo
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
        // Get order directly from database
        $recordModel = new RecordModel();
        $order = $recordModel->find($orderId);
        
        if (!$order || $order['record_type'] !== 'sales' || $order['status'] !== 'pending') {
            return redirect()->to('/orders')->with('error', 'Order not found or already processed.');
        }

        // Create order items from database or use defaults
        $orderItems = [];
        $notes = $order['notes'] ?? '';
        
        if (!empty($notes)) {
            $decoded = json_decode($notes, true);
            if ($decoded && isset($decoded['items'])) {
                $orderItems = $decoded['items'];
            }
        }
        
        // If no items in notes, create from order data
        if (empty($orderItems)) {
            $orderItems = [
                [
                    'id' => 1,
                    'name' => 'Fruity Cereal',
                    'qty' => 1,
                    'price' => 62.50
                ]
            ];
        }

        // Prepare checkout data
        $data = [
            'user_name' => $this->session->get('user_name') ?? 'Admin',
            'user_email' => $this->session->get('user_email') ?? 'admin@vapeshop.com',
            'user_role' => $this->session->get('user_role') ?? 'admin',
            'user_shop_name' => $this->session->get('user_shop_name') ?? 'Vape Shop',
            'page_title' => 'Order Checkout',
            'order' => $order,
            'items' => $orderItems,
            'total' => (float) $order['total_amount'],
            'reference_number' => $order['reference_number']
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

        $recordModel = new RecordModel();
        $order = $recordModel->find($orderId);

        if (!$order || $order['record_type'] !== 'sales') {
            return redirect()->to('/orders')->with('error', 'Order not found.');
        }

        if ($order['status'] !== 'pending') {
            return redirect()->to('/orders')->with('error', 'Order is already processed.');
        }

        // Parse order items from notes
        $orderItems = [];
        $notes = $order['notes'] ?? '';
        
        if (!empty($notes)) {
            $decoded = json_decode($notes, true);
            if ($decoded && isset($decoded['items'])) {
                $orderItems = $decoded['items'];
            }
        }

        if (empty($orderItems)) {
            return redirect()->to('/orders')->with('error', 'Order items not found.');
        }

        // Get cashiering form data
        $ageVerified = $this->request->getPost('age_verified');
        $paymentMethod = $this->request->getPost('payment_method');
        $amountReceived = (float) $this->request->getPost('amount_received');

        // Validate cashiering data
        if (!$ageVerified) {
            return redirect()->to('/orders/checkout/' . $orderId)->with('error', 'Age verification is required.');
        }

        if (!$paymentMethod) {
            return redirect()->to('/orders/checkout/' . $orderId)->with('error', 'Payment method is required.');
        }

        if ($amountReceived < (float) $order['total_amount']) {
            return redirect()->to('/orders/checkout/' . $orderId)->with('error', 'Amount received is insufficient.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update stock for each item
            foreach ($orderItems as $item) {
                $product = $this->productModel->getProductById((int) $item['id'], true);
                if (!$product || (int) $product['stock'] < (int) $item['qty']) {
                    throw new \RuntimeException('Insufficient stock for item: ' . $item['name']);
                }

                // Update stock (decrement)
                $currentStock = (int) $product['stock'];
                $newStock = $currentStock - (int) $item['qty'];
                
                if ($newStock < 0) {
                    throw new \RuntimeException('Insufficient stock for item: ' . $item['name'] . ' (Current: ' . $currentStock . ', Required: ' . $item['qty'] . ')');
                }
                
                $this->productModel->update($item['id'], ['stock' => $newStock]);
            }

            // Update order status
            $updateData = [
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => $paymentMethod,
                'delivery_status' => 'to_ship',
                'description' => $order['description'] . ' - Processed by admin',
                'notes' => json_encode(array_merge(json_decode($order['notes'] ?? '{}', true) ?: [], []))
            ];

            $updateOk = $recordModel->update($orderId, $updateData);

            if (!$updateOk) {
                throw new \RuntimeException('Failed to update order status.');
            }
            
            // Auto-populate customer info when order is set to to_ship
            $this->autoPopulateCustomerInfo($orderId, $order);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Checkout processing failed.');
            }

            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'message' => 'Payment processed successfully! Order completed and stock updated.',
                'order_id' => $orderId
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage()
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
     * Auto-populate customer address and contact number for an order
     */
    private function autoPopulateCustomerInfo($orderId, $order)
    {
        if (!empty($order['created_by'])) {
            $userModel = new UserModel();
            $customer = $userModel->find($order['created_by']);
            
            if ($customer) {
                $updateData = [];
                
                // Build full address from customer profile
                $addressParts = [];
                if (!empty($customer['address_line'])) {
                    $addressParts[] = $customer['address_line'];
                }
                if (!empty($customer['city'])) {
                    $addressParts[] = $customer['city'];
                }
                if (!empty($customer['province'])) {
                    $addressParts[] = $customer['province'];
                }
                if (!empty($customer['postal_code'])) {
                    $addressParts[] = $customer['postal_code'];
                }
                
                // Only update if order doesn't already have this info
                if (empty($order['shipping_address']) && !empty($addressParts)) {
                    $updateData['shipping_address'] = implode(', ', $addressParts);
                }
                
                if (empty($order['contact_number']) && !empty($customer['phone_number'])) {
                    $updateData['contact_number'] = $customer['phone_number'];
                }
                
                // Update the order if there's data to update
                if (!empty($updateData)) {
                    $recordModel = new RecordModel();
                    $recordModel->update($orderId, $updateData);
                }
            }
        }
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

        $recordModel = new RecordModel();
        $order = $recordModel->find($orderId);

        if (!$order || $order['record_type'] !== 'sales') {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Order not found.'
            ]);
        }

        // Debug: Log current status
        $currentStatus = $order['delivery_status'] ?? 'to_pay';
        log_message('debug', 'Current delivery status: ' . $currentStatus);
        log_message('debug', 'Requested new status: ' . $newStatus);

        // Allow any valid status transition for now (simplified logic)
        // This allows the button to work regardless of current status

        // Generate tracking number if marking as to_ship
        $additionalData = [];
        if ($newStatus === 'to_ship' && empty($order['tracking_number'])) {
            $additionalData['tracking_number'] = $this->generateTrackingNumber();
        }

        try {
            // Use direct database query to avoid RecordModel method issues
            $db = \Config\Database::connect();
            
            $updateData = [
                'delivery_status' => $newStatus
            ];
            
            // Add timestamps based on status
            if ($newStatus === 'to_ship') {
                $updateData['shipped_at'] = date('Y-m-d H:i:s');
            } elseif ($newStatus === 'completed') {
                $updateData['delivered_at'] = date('Y-m-d H:i:s');
            }
            
            // Add tracking number if marking as to_ship
            if ($newStatus === 'to_ship' && empty($order['tracking_number'])) {
                $trackingNumber = $this->generateTrackingNumber();
                $updateData['tracking_number'] = $trackingNumber;
            }
            
            // Direct database update using query builder
            $builder = $db->table('records');
            $result = $builder->where('id', $orderId)->update($updateData);
            
            // Auto-populate customer info when order is in process (to_ship)
            if ($result && $newStatus === 'to_ship') {
                $this->autoPopulateCustomerInfo($orderId, $order);
            }
            
            if ($result) {
                return $this->response->setStatusCode(200)->setJSON([
                    'success' => true,
                    'message' => 'Delivery status updated successfully.'
                ]);
            } else {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Failed to update delivery status.'
                ]);
            }
            
        } catch (\Throwable $e) {
            log_message('error', 'Delivery status update error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'An error occurred while updating the delivery status.'
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

        $recordModel = new RecordModel();
        $order = $recordModel->find($orderId);

        if (!$order || $order['record_type'] !== 'sales') {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Order not found.'
            ]);
        }

        // Get customer profile information for defaults
        $customerPhone = '';
        $customerAddress = '';
        if (!empty($order['created_by'])) {
            $customer = $this->userModel->find($order['created_by']);
            if ($customer) {
                $customerPhone = $customer['phone'] ?? '';
                $customerAddress = $customer['address'] ?? '';
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'tracking_number' => $order['tracking_number'] ?? '',
            'shipping_address' => $order['shipping_address'] ?: $customerAddress,
            'contact_number' => $order['contact_number'] ?: $customerPhone
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

        $recordModel = new RecordModel();
        $order = $recordModel->find($orderId);

        if (!$order || $order['record_type'] !== 'sales') {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Order not found.'
            ]);
        }

        try {
            $db = \Config\Database::connect();
            
            $updateData = [];
            
            // Update delivery information if provided
            if (!empty($input->tracking_number)) {
                $updateData['tracking_number'] = $input->tracking_number;
            }
            
            if (!empty($input->shipping_address)) {
                $updateData['shipping_address'] = $input->shipping_address;
            }
            
            if (!empty($input->contact_number)) {
                $updateData['contact_number'] = $input->contact_number;
            }
            
            // If no tracking number exists and order is ready to ship, generate one
            if (empty($order['tracking_number']) && !empty($input->tracking_number)) {
                $updateData['delivery_status'] = 'to_ship';
                $updateData['shipped_at'] = date('Y-m-d H:i:s');
            }
            
            // Update delivery notes in order notes
            if (!empty($input->delivery_notes)) {
                $currentNotes = $order['notes'] ?? '';
                $deliveryNotes = "\n\n--- DELIVERY NOTES ---\n" . $input->delivery_notes . "\nAdded: " . date('Y-m-d H:i:s');
                $updateData['notes'] = $currentNotes . $deliveryNotes;
            }
            
            if (!empty($updateData)) {
                $builder = $db->table('records');
                $result = $builder->where('id', $orderId)->update($updateData);
                
                if ($result) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Delivery information saved successfully.'
                    ]);
                } else {
                    return $this->response->setStatusCode(500)->setJSON([
                        'success' => false,
                        'message' => 'Failed to save delivery information.'
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'No changes to save.'
                ]);
            }
            
        } catch (\Throwable $e) {
            log_message('error', 'Save delivery info error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'An error occurred while saving delivery information.'
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

        $recordModel = new RecordModel();
        $order = $recordModel->find($orderId);

        if (!$order || $order['record_type'] !== 'sales') {
            return redirect()->to('/orders')->with('error', 'Order not found.');
        }

        // Parse order items from notes
        $orderItems = [];
        $notes = $order['notes'] ?? '';
        if (!empty($notes)) {
            $lines = explode("\n", $notes);
            foreach ($lines as $line) {
                if (strpos($line, '|') !== false) {
                    $parts = explode('|', $line);
                    if (count($parts) >= 3) {
                        $orderItems[] = [
                            'name' => trim($parts[0]),
                            'qty' => trim($parts[1]),
                            'unit_price' => trim($parts[2])
                        ];
                    }
                }
            }
        }

        $data = [
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $this->session->get('user_role'),
            'user_shop_name' => $this->session->get('user_shop_name'),
            'page_title' => 'Order Details - Admin',
            'order' => $order,
            'items' => $orderItems
        ];

        return view('admin/orders/order_details', $data);
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
     * Simple test method for order details
     */
    public function testOrderDetails()
    {
        // Redirect to the clean standalone page
        return redirect()->to(base_url('order_details_standalone.html'));
    }

    /**
     * Direct order details view (for testing)
     */
    public function viewOrderDetailsDirect($orderId)
    {
        log_message('info', 'viewOrderDetailsDirect called with orderId: ' . $orderId);
        
        $recordModel = new RecordModel();
        $order = $recordModel->find($orderId);

        if (!$order || $order['record_type'] !== 'sales') {
            log_message('error', 'Order not found for orderId: ' . $orderId);
            return redirect()->to('/customer/orders')->with('error', 'Order not found.');
        }

        // Parse order items
        $notes = $order['notes'] ?? '';
        $orderItems = [];
        
        if (!empty($notes)) {
            $decoded = json_decode($notes, true);
            if ($decoded && isset($decoded['items'])) {
                $orderItems = $decoded['items'];
            }
        }

        return view('customer/order_details', $this->getCustomerPageData('Order Details', 'orders', [
            'order' => $order,
            'items' => $orderItems
        ]));
    }

    /**
     * Order action handlers for Shopee-like delivery process
     */
    public function customerOrderAction($orderId, $action)
    {
        // Debug logging
        log_message('info', 'customerOrderAction called with orderId: ' . $orderId . ', action: ' . $action);
        
        // Check if we're in test mode (no session required)
        $isTestMode = strpos(current_url(), 'test-order-action') !== false;
        
        if (!$isTestMode) {
            $accessCheck = $this->checkCustomerAccess();
            if ($accessCheck !== true) {
                log_message('error', 'Access check failed for customerOrderAction');
                return $accessCheck;
            }
        }

        $recordModel = new RecordModel();
        $order = $recordModel->find($orderId);

        if (!$order || $order['record_type'] !== 'sales') {
            log_message('error', 'Order not found for orderId: ' . $orderId);
            return redirect()->to('/customer/orders')->with('error', 'Order not found.');
        }

        // For test mode, skip ownership check
        if (!$isTestMode && $order['created_by'] != $this->session->get('user_id')) {
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

        // Update payment status and delivery status
        $recordModel = new RecordModel();
        $recordModel->update($order['id'], [
            'payment_status' => 'paid',
            'delivery_status' => 'to_ship'
        ]);
        
        // Auto-populate customer info when order is set to to_ship
        $this->autoPopulateCustomerInfo($order['id'], $order);

        return redirect()->to('/customer/orders')->with('success', 'Payment processed successfully. Order is now ready for shipping.');
    }

    private function cancelOrder($order)
    {
        if (!in_array($order['delivery_status'], ['to_pay', 'to_ship'])) {
            return redirect()->to('/customer/orders')->with('error', 'Order cannot be cancelled.');
        }

        $recordModel = new RecordModel();
        $recordModel->update($order['id'], [
            'delivery_status' => 'cancelled',
            'status' => 'cancelled'
        ]);

        return redirect()->to('/customer/orders')->with('success', 'Order cancelled successfully.');
    }

    private function confirmOrderReceived($order)
    {
        if ($order['delivery_status'] !== 'to_receive') {
            return redirect()->to('/customer/orders')->with('error', 'Order cannot be confirmed.');
        }

        $recordModel = new RecordModel();
        $recordModel->update($order['id'], [
            'delivery_status' => 'completed',
            'status' => 'completed'
        ]);

        return redirect()->to('/customer/orders')->with('success', 'Order confirmed as received. Thank you!');
    }

    private function reorderItems($order)
    {
        // Add items back to cart
        $notes = $order['notes'] ?? '';
        $cartItems = [];
        
        if (!empty($notes)) {
            $decoded = json_decode($notes, true);
            if ($decoded && isset($decoded['items'])) {
                foreach ($decoded['items'] as $item) {
                    $cartItems[$item['id']] = $item['qty'];
                }
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

        $recordModel = new RecordModel();
        $order = $recordModel->find($orderId);

        if (!$order || $order['record_type'] !== 'sales') {
            log_message('error', 'Order not found for orderId: ' . $orderId);
            return redirect()->to('/customer/orders')->with('error', 'Order not found.');
        }

        if ($order['created_by'] != $this->session->get('user_id')) {
            log_message('error', 'Access denied for orderId: ' . $orderId);
            return redirect()->to('/customer/orders')->with('error', 'Order not found.');
        }

        return $this->viewOrderDetailsPrivate($order);
    }

    private function viewOrderDetailsPrivate($order)
    {
        // Parse order items
        $notes = $order['notes'] ?? '';
        $orderItems = [];
        
        if (!empty($notes)) {
            $decoded = json_decode($notes, true);
            if ($decoded && isset($decoded['items'])) {
                $orderItems = $decoded['items'];
            }
        }

        // Add tracking information for to_ship orders
        $trackingInfo = [];
        if ($order['delivery_status'] === 'to_ship') {
            $trackingInfo = [
                'status' => 'Preparing for shipment',
                'estimated_date' => date('F j, Y', strtotime('+3 days')),
                'message' => 'Your order is being prepared and will be shipped soon.'
            ];
        } elseif ($order['delivery_status'] === 'to_receive') {
            $trackingInfo = [
                'status' => 'Out for delivery',
                'estimated_date' => date('F j, Y', strtotime('+1 day')),
                'message' => 'Your order is out for delivery.'
            ];
        }

        return view('customer/order_details', $this->getCustomerPageData('Order Details', 'orders', [
            'order' => $order,
            'items' => $orderItems,
            'tracking_info' => $trackingInfo
        ]));
    }

    /**
     * Get notifications based on real data
     */
    private function getNotifications($ordersToday, $revenueToday)
    {
        if ((int) $ordersToday <= 0) {
            return [];
        }

        return [
            ['type' => 'success', 'message' => $ordersToday . ' order(s) recorded today.'],
            ['type' => 'info', 'message' => 'Today revenue: ' . $revenueToday],
        ];
    }
}
