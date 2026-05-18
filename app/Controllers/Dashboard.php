<?php

namespace App\Controllers;

use App\Models\DashboardModel;
use App\Models\UserModel;
use App\Models\ProductModel;
use App\Models\OrderModel;
use App\Models\ReviewModel;
use App\Models\RecordModel;
use App\Libraries\SecurityAuditService;
use App\Libraries\NotificationService;

class Dashboard extends BaseController
{
    protected $session;
    protected $dashboardModel;
    protected $userModel;
    protected $productModel;
    protected $orderModel;
    protected $recordModel;
    protected $reviewModel;
    protected $securityAuditService;
    protected NotificationService $notificationService;

    public function __construct()
    {
        $this->session = session();
        $this->dashboardModel = new DashboardModel();
        $this->userModel = new UserModel();
        $this->productModel = new ProductModel();
        $this->orderModel = new OrderModel();
        $this->reviewModel = new ReviewModel();
        $this->recordModel = new RecordModel();
        $this->securityAuditService = new SecurityAuditService();
        $this->notificationService = new NotificationService();
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
     * Allow access only for authenticated rider users.
     */
    private function checkRiderAccess()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $sessionCheck = $this->checkSessionTimeout();
        if ($sessionCheck !== true) {
            return $sessionCheck;
        }

        if ((string) $this->session->get('user_role') !== 'rider') {
            return redirect()->to('/dashboard')
                ->with('error', 'Access denied. Rider area only.');
        }

        return true;
    }

    public function liveUpdateToken()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $userRole = (string) $this->session->get('user_role');
        $userId = (int) $this->session->get('user_id');
        $db = \Config\Database::connect();

        try {
            $ordersTs = 0;
            $shipmentsTs = 0;
            $paymentsTs = 0;

            if ($userRole === 'admin') {
                $ordersTs = (int) (($db->query("SELECT COALESCE(UNIX_TIMESTAMP(MAX(updated_at)), 0) AS ts FROM orders")->getRowArray()['ts'] ?? 0));
                $shipmentsTs = (int) (($db->query("SELECT COALESCE(UNIX_TIMESTAMP(MAX(updated_at)), 0) AS ts FROM order_shipments")->getRowArray()['ts'] ?? 0));
                $paymentsTs = (int) (($db->query("SELECT COALESCE(UNIX_TIMESTAMP(MAX(updated_at)), 0) AS ts FROM order_payments")->getRowArray()['ts'] ?? 0));
            } elseif ($userRole === 'rider') {
                $shipmentsTs = (int) (($db->query(
                    "SELECT COALESCE(UNIX_TIMESTAMP(MAX(updated_at)), 0) AS ts FROM order_shipments WHERE assigned_rider_id = ?",
                    [$userId]
                )->getRowArray()['ts'] ?? 0));
            } else {
                // Customer and other authenticated users: track own orders and related shipments/payments.
                $ordersTs = (int) (($db->query(
                    "SELECT COALESCE(UNIX_TIMESTAMP(MAX(updated_at)), 0) AS ts FROM orders WHERE customer_id = ?",
                    [$userId]
                )->getRowArray()['ts'] ?? 0));
                $shipmentsTs = (int) (($db->query(
                    "SELECT COALESCE(UNIX_TIMESTAMP(MAX(s.updated_at)), 0) AS ts
                     FROM order_shipments s
                     JOIN orders o ON o.id = s.order_id
                     WHERE o.customer_id = ?",
                    [$userId]
                )->getRowArray()['ts'] ?? 0));
                $paymentsTs = (int) (($db->query(
                    "SELECT COALESCE(UNIX_TIMESTAMP(MAX(p.updated_at)), 0) AS ts
                     FROM order_payments p
                     JOIN orders o ON o.id = p.order_id
                     WHERE o.customer_id = ?",
                    [$userId]
                )->getRowArray()['ts'] ?? 0));
            }

            return $this->response->setJSON([
                'success' => true,
                'token' => implode(':', [$ordersTs, $shipmentsTs, $paymentsTs]),
                'role' => $userRole,
                'checked_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unable to check updates',
            ]);
        }
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
            'revenue_today' => $analyticsToday['revenue'] ?? '₱0.00',
            'recent_orders' => $analyticsToday['orders'] ?? 0,
            'growth_rate' => $this->dashboardModel->getGrowthRate($userRole, $shopName),
        ], $extra);
    }

    /**
     * Shared data for rider pages.
     *
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    private function getRiderPageData(string $pageTitle, string $activePage, array $extra = []): array
    {
        return array_merge([
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => (string) $this->session->get('user_role'),
            'page_title' => $pageTitle,
            'active_page' => $activePage,
        ], $extra);
    }

    /**
     * Get human-readable status label
     */
    public function getStatusLabel(string $status): string
    {
        $labels = [
            'pending' => 'Pending',
            'to_ship' => 'For Pickup',
            'for_pickup' => 'For Pickup',
            'in_progress' => 'In Progress',
            'completed' => 'Delivered',
            'failed' => 'Failed Delivery',
        ];

        return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
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

        if ($userRole === 'rider') {
            return redirect()->to('/rider/dashboard');
        }

        // Get dashboard analytics based on user role
        $shopName = $this->session->get('user_shop_name');
        $analytics = $this->dashboardModel->getAnalytics('today', $userRole, $shopName);
        $userStats = $this->dashboardModel->getUserActivityStats();
        $systemMetrics = $this->dashboardModel->getSystemMetrics();
        $monthlyAnalytics = $this->dashboardModel->getAnalytics('month', $userRole, $shopName);
        
        $totalProducts = $this->dashboardModel->getTotalProducts($userRole, $shopName);
        $recentRegistrations = $this->dashboardModel->getRecentRegistrations();
        $revenueChart = $this->dashboardModel->getRevenueChartData(7);
        $recentOrdersList = $this->dashboardModel->getRecentOrdersList(8);
        $lowStockProducts = $this->dashboardModel->getLowStockProducts(10, 8);

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
            'total_customers' => $this->dashboardModel->getTotalCustomers(),
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
            'monthly_revenue' => $monthlyAnalytics['revenue'],
            'revenue_chart' => $revenueChart,
            'recent_orders_list' => $recentOrdersList,
            'low_stock_products' => $lowStockProducts,
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
     * Rider dashboard page.
     */
    public function riderDashboard()
    {
        $accessCheck = $this->checkRiderAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $deliveries = $this->getRiderDashboardDeliveries();
        $today = date('Y-m-d');

        $stats = [
            'active' => 0,
            'to_ship' => 0,
            'to_receive' => 0,
            'completed_today' => 0,
        ];

        foreach ($deliveries as $delivery) {
            $status = (string) ($delivery['delivery_status'] ?? 'to_pay');
            if (in_array($status, ['to_ship', 'to_receive', 'failed_delivery'], true)) {
                $stats['active']++;
            }
            if ($status === 'to_ship') {
                $stats['to_ship']++;
            }
            if ($status === 'to_receive') {
                $stats['to_receive']++;
            }
            if ($status === 'completed' && str_starts_with((string) ($delivery['updated_at'] ?? ''), $today)) {
                $stats['completed_today']++;
            }
        }

        return view('rider/dashboard', $this->getRiderPageData('Rider Dashboard', 'dashboard', [
            'stats' => $stats,
            'deliveries' => array_slice($deliveries, 0, 5),
        ]));
    }

    /**
     * Rider delivery list page.
     */
    public function riderDeliveries()
    {
        $accessCheck = $this->checkRiderAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        return view('rider/deliveries', $this->getRiderPageData('My Deliveries', 'deliveries', [
            'deliveries' => $this->getRiderDeliveries(),
        ]));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getRiderDeliveries(): array
    {
        $riderId = (int) $this->session->get('user_id');
        $orders = $this->orderModel->getAdminOrders();
        $deliveryStatuses = ['to_ship', 'to_receive', 'failed_delivery', 'completed', 'delivered', 'ready_for_pickup', 'accepted_by_rider', 'delivered_to_rider'];

        $deliveries = array_values(array_filter($orders, static function (array $order) use ($deliveryStatuses, $riderId): bool {
            $status = (string) ($order['delivery_status'] ?? 'to_pay');
            $assignedRiderId = (int) ($order['assigned_rider_id'] ?? 0);

            return in_array($status, $deliveryStatuses, true)
                && $assignedRiderId > 0
                && $assignedRiderId === $riderId;
        }));

        foreach ($deliveries as &$delivery) {
            $delivery['customer'] = $this->getOrderCustomerInfo(isset($delivery['created_by']) ? (int) $delivery['created_by'] : null);
        }
        unset($delivery);

        return $deliveries;
    }

    /**
     * Dashboard feed for riders.
     * Shows recent delivery-stage orders even if not assigned to the current rider.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getRiderDashboardDeliveries(): array
    {
        $orders = $this->orderModel->getAdminOrders();
        $deliveryStatuses = ['to_ship', 'to_receive', 'failed_delivery', 'completed', 'delivered', 'ready_for_pickup', 'accepted_by_rider', 'delivered_to_rider'];

        $deliveries = array_values(array_filter($orders, static function (array $order) use ($deliveryStatuses): bool {
            $status = (string) ($order['delivery_status'] ?? 'to_pay');
            return in_array($status, $deliveryStatuses, true);
        }));

        foreach ($deliveries as &$delivery) {
            $delivery['customer'] = $this->getOrderCustomerInfo(isset($delivery['created_by']) ? (int) $delivery['created_by'] : null);
        }
        unset($delivery);

        return $deliveries;
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

        // Get one customer-facing card per product. Flavor variants are attached
        // to each product for selection before adding to cart.
        $products = $this->productModel->getCustomerProducts((string) $search, (string) $category);
        $products = $this->attachProductReviewSummaries($products);

        $categories = $allowedCategories;

        $ageAllowed = $this->canCustomerPurchase();
        $cart = $this->getCustomerCart();
        $customer = $this->userModel->find((int) $this->session->get('user_id'));

        return view('customer/products', $this->getCustomerPageData('Products', 'products', [
            'products' => $products,
            'categories' => $categories,
            'search' => $search,
            'selectedCategory' => $category,
            'age_allowed' => $ageAllowed,
            'cart_items' => $cart['items'],
            'cart_total' => $cart['total'],
            'customer_delivery_address' => is_array($customer) ? $this->buildCustomerAddressString($customer) : '',
        ]));
    }

    private function attachProductReviewSummaries(array $products): array
    {
        if ($products === []) {
            return $products;
        }

        $productIds = array_values(array_unique(array_map(static fn (array $product): int => (int) ($product['id'] ?? 0), $products)));
        $reviewData = $this->reviewModel->getProductReviewDataForProducts($productIds);

        foreach ($products as &$product) {
            $productId = (int) ($product['id'] ?? 0);
            $data = $reviewData[$productId] ?? [
                'summary' => ['total_reviews' => 0, 'average_rating' => 0.0],
                'reviews' => [],
            ];
            $product['review_summary'] = $data['summary'];
            $product['approved_reviews'] = $data['reviews'];
        }
        unset($product);

        return $products;
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
        $validTabs = ['all', 'to_pay', 'to_ship', 'to_receive', 'completed', 'to_review', 'cancelled', 'return_refund', 'failed_delivery'];

        if (!in_array($activeTab, $validTabs, true)) {
            $activeTab = 'all';
        }

        $orders = $this->orderModel->getCustomerOrders($userId, $activeTab === 'to_review' ? 'completed' : ($activeTab === 'all' ? null : $activeTab));
        $orders = $this->attachProductReviewState($orders, $userId);
        if ($activeTab === 'to_review') {
            $orders = array_values(array_filter($orders, static fn (array $order): bool => (int) ($order['reviewable_count'] ?? 0) > 0));
        }

        $statusCounts = $this->orderModel->getCustomerStatusCounts($userId);
        $statusCounts['to_review'] = $this->countOrdersToReview($userId);

        return view('customer/orders', $this->getCustomerPageData('Orders', 'orders', [
            'orders' => $orders,
            'activeTab' => $activeTab,
            'statusCounts' => $statusCounts,
        ]));
    }

    private function attachProductReviewState(array $orders, int $customerId): array
    {
        if ($orders === []) {
            return $orders;
        }

        $orderIds = array_values(array_unique(array_map(static fn (array $order): int => (int) ($order['id'] ?? 0), $orders)));
        $reviewsByKey = $this->reviewModel->getCustomerReviewsForOrders($orderIds, $customerId);

        foreach ($orders as &$order) {
            $reviewableCount = 0;
            $items = (array) ($order['items'] ?? []);
            foreach ($items as &$item) {
                $productId = (int) ($item['id'] ?? 0);
                $review = $productId > 0 ? ($reviewsByKey[((int) $order['id']) . ':' . $productId] ?? null) : null;
                $item['product_review'] = $review;
                $item['can_review_product'] = $productId > 0
                    && ($order['delivery_status'] ?? '') === 'completed'
                    && $review === null;

                if ($item['can_review_product']) {
                    $reviewableCount++;
                }
            }
            unset($item);

            $order['items'] = $items;
            $order['reviewable_count'] = $reviewableCount;
            $order['has_product_reviews'] = $this->orderHasProductReviews($order);
        }
        unset($order);

        return $orders;
    }

    private function countOrdersToReview(int $customerId): int
    {
        $orders = $this->attachProductReviewState($this->orderModel->getCustomerOrders($customerId, 'completed'), $customerId);
        return count(array_filter($orders, static fn (array $order): bool => (int) ($order['reviewable_count'] ?? 0) > 0));
    }

    private function orderHasProductReviews(array $order): bool
    {
        foreach ((array) ($order['items'] ?? []) as $item) {
            if (! empty($item['product_review'])) {
                return true;
            }
        }

        return false;
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
     * Customer: Update delivery status
     */
    public function riderUpdateDeliveryStatus()
    {
        $accessCheck = $this->checkRiderAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $orderId = (int) $this->request->getPost('order_id');
        $status = (string) $this->request->getPost('status');

        if (!$orderId || !$status) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            $riderId = (int) $this->session->get('user_id');
            $shipment = $this->orderModel->getShipmentByOrderId($orderId);
            if (! $shipment) {
                return $this->response->setJSON(['success' => false, 'message' => 'Shipment not found']);
            }

            if ((int) ($shipment['assigned_rider_id'] ?? 0) !== $riderId) {
                return $this->response->setJSON(['success' => false, 'message' => 'This order is not assigned to you']);
            }

            $currentStatus = (string) ($shipment['status'] ?? 'to_pay');

            if ($status === 'accepted_by_rider') {
                if ($currentStatus !== 'ready_for_pickup') {
                    return $this->response->setJSON(['success' => false, 'message' => 'Order is not ready for acceptance']);
                }

                $result = $this->orderModel->updateDeliveryStatus($orderId, 'accepted_by_rider', []);
                if ($result) {
                    $order = $this->orderModel->getOrder($orderId);
                    if ($order) {
                        $reference = (string) ($order['reference_number'] ?? ('#' . $orderId));
                        $this->notificationService->notifyUsers([(int) ($order['created_by'] ?? 0)], [
                            'category' => 'delivery',
                            'type' => 'delivery_accepted',
                            'title' => 'Delivery accepted',
                            'message' => 'Rider accepted order ' . $reference . '.',
                            'link' => site_url('customer/order-details/' . $orderId),
                            'related_type' => 'order',
                            'related_id' => $orderId,
                        ]);
                        $this->notificationService->notifyAdmins([
                            'category' => 'delivery',
                            'type' => 'delivery_accepted',
                            'title' => 'Delivery accepted',
                            'message' => 'Rider accepted order ' . $reference . '.',
                            'link' => site_url('admin/order-details/' . $orderId),
                            'related_type' => 'order',
                            'related_id' => $orderId,
                        ]);
                    }
                }
                return $this->response->setJSON([
                    'success' => (bool) $result,
                    'message' => $result ? 'Delivery accepted successfully' : 'Unable to accept delivery',
                ]);
            }

            if ($status === 'delivered_to_rider') {
                if ($currentStatus !== 'accepted_by_rider') {
                    return $this->response->setJSON(['success' => false, 'message' => 'Please accept the delivery before pickup']);
                }

                $result = $this->orderModel->updateDeliveryStatus($orderId, 'delivered_to_rider', [
                    'picked_up_at' => date('Y-m-d H:i:s'),
                ]);
                if ($result) {
                    $order = $this->orderModel->getOrder($orderId);
                    if ($order) {
                        $reference = (string) ($order['reference_number'] ?? ('#' . $orderId));
                        $this->notificationService->notifyUsers([(int) ($order['created_by'] ?? 0)], [
                            'category' => 'delivery',
                            'type' => 'order_picked_up',
                            'title' => 'Order picked up',
                            'message' => 'Your order ' . $reference . ' was picked up by the rider.',
                            'link' => site_url('customer/order-details/' . $orderId),
                            'related_type' => 'order',
                            'related_id' => $orderId,
                        ]);
                    }
                }

                return $this->response->setJSON([
                    'success' => (bool) $result,
                    'message' => $result ? 'Order picked up successfully' : 'Unable to update pickup status',
                ]);
            }

            if ($status === 'to_receive') {
                if (! in_array($currentStatus, ['delivered_to_rider', 'failed_delivery'], true)) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Order cannot start delivery from current state']);
                }

                $activeDelivery = \Config\Database::connect()
                    ->table('order_shipments')
                    ->select('order_id, tracking_number')
                    ->where('assigned_rider_id', $riderId)
                    ->where('status', 'to_receive')
                    ->where('order_id !=', $orderId)
                    ->get()
                    ->getRowArray();

                if (! empty($activeDelivery)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'You already have an active delivery in progress. Finish the current delivery first before starting another one.',
                    ]);
                }

                $lat = $this->parseCoordinate($this->request->getPost('rider_latitude'));
                $lng = $this->parseCoordinate($this->request->getPost('rider_longitude'));
                $locationData = [];
                if ($lat !== null && $lng !== null) {
                    $locationData = [
                        'rider_latitude' => $lat,
                        'rider_longitude' => $lng,
                        'last_location_updated_at' => date('Y-m-d H:i:s'),
                    ];
                }

                $result = $this->orderModel->updateDeliveryStatus($orderId, 'to_receive', $locationData);
                if ($result) {
                    $order = $this->orderModel->getOrder($orderId);
                    if ($order) {
                        $reference = (string) ($order['reference_number'] ?? ('#' . $orderId));
                        $this->notificationService->notifyUsers([(int) ($order['created_by'] ?? 0)], [
                            'category' => 'delivery',
                            'type' => 'out_for_delivery',
                            'title' => 'Out for delivery',
                            'message' => 'Your order ' . $reference . ' is out for delivery.',
                            'link' => site_url('dashboard/orderTracking/' . $orderId),
                            'related_type' => 'order',
                            'related_id' => $orderId,
                        ]);
                    }
                }
                return $this->response->setJSON([
                    'success' => (bool) $result,
                    'message' => $result ? 'Delivery started' : 'Unable to start delivery',
                ]);
            }

            if ($status === 'failed_delivery') {
                if (! in_array($currentStatus, ['accepted_by_rider', 'delivered_to_rider', 'to_receive'], true)) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Delivery cannot be cancelled from current state']);
                }

                $cancelReason = trim((string) $this->request->getPost('cancel_reason'));
                $notesPrefix = 'RIDER_CANCELLED';
                $existingNotes = trim((string) ($shipment['notes'] ?? ''));
                $reasonText = $cancelReason !== '' ? $cancelReason : 'No reason provided';
                $cancelNoteLine = $notesPrefix . ': ' . $reasonText . ' (' . date('Y-m-d H:i:s') . ')';
                $updatedNotes = $existingNotes !== '' ? ($existingNotes . "\n" . $cancelNoteLine) : $cancelNoteLine;

                $lat = $this->parseCoordinate($this->request->getPost('rider_latitude'));
                $lng = $this->parseCoordinate($this->request->getPost('rider_longitude'));
                $payload = [
                    'notes' => $updatedNotes,
                    'last_location_updated_at' => date('Y-m-d H:i:s'),
                ];
                if ($lat !== null && $lng !== null) {
                    $payload['rider_latitude'] = $lat;
                    $payload['rider_longitude'] = $lng;
                }

                $result = $this->orderModel->updateDeliveryStatus($orderId, 'failed_delivery', $payload);
                if ($result) {
                    $order = $this->orderModel->getOrder($orderId);
                    if ($order) {
                        $reference = (string) ($order['reference_number'] ?? ('#' . $orderId));
                        $this->notificationService->notifyOrderAudience($order, [
                            'category' => 'delivery',
                            'type' => 'delivery_cancelled',
                            'title' => 'Delivery cancelled',
                            'message' => 'Delivery for order ' . $reference . ' was cancelled by the rider.',
                            'link' => site_url('admin/order-details/' . $orderId),
                            'related_type' => 'order',
                            'related_id' => $orderId,
                        ], false, false, true);
                        $this->notificationService->notifyUsers([(int) ($order['created_by'] ?? 0)], [
                            'category' => 'delivery',
                            'type' => 'delivery_cancelled',
                            'title' => 'Delivery cancelled',
                            'message' => 'Delivery for your order ' . $reference . ' was cancelled.',
                            'link' => site_url('customer/order-details/' . $orderId),
                            'related_type' => 'order',
                            'related_id' => $orderId,
                        ]);
                    }
                }
                return $this->response->setJSON([
                    'success' => (bool) $result,
                    'message' => $result ? 'Delivery cancelled successfully' : 'Unable to cancel delivery',
                ]);
            }

            return $this->response->setJSON(['success' => false, 'message' => 'Invalid status transition']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function assignRiderToOrder()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        if ((string) $this->session->get('user_role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $payload = $this->request->getJSON(true) ?? [];
        $orderId = (int) ($payload['order_id'] ?? $this->request->getPost('order_id'));
        $riderId = (int) ($payload['rider_id'] ?? $this->request->getPost('rider_id'));

        if ($orderId <= 0 || $riderId <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Order and rider are required']);
        }

        $rider = $this->userModel->find($riderId);
        if (! $rider || (string) ($rider['role'] ?? '') !== 'rider') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid rider selected']);
        }

        $shipment = $this->orderModel->getShipmentByOrderId($orderId);
        if (! $shipment) {
            return $this->response->setJSON(['success' => false, 'message' => 'Shipment not found']);
        }

        $currentStatus = (string) ($shipment['status'] ?? 'to_pay');
        if (in_array($currentStatus, ['completed', 'cancelled'], true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Completed/cancelled orders cannot be reassigned']);
        }
        if (in_array($currentStatus, ['to_receive', 'delivered_to_rider'], true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Rider cannot be reassigned after pickup/start delivery']);
        }

        $updated = $this->orderModel->assignRiderToOrder($orderId, $riderId);
        if (! $updated) {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to assign rider']);
        }
        $order = $this->orderModel->getOrder($orderId);
        if ($order) {
            $reference = (string) ($order['reference_number'] ?? ('#' . $orderId));
            $this->notificationService->notifyUsers([$riderId], [
                'category' => 'delivery',
                'type' => 'rider_assigned',
                'title' => 'New delivery assigned',
                'message' => 'Order ' . $reference . ' is ready for your pickup.',
                'link' => site_url('rider/deliveries?order_id=' . $orderId . '#delivery-' . $orderId),
                'related_type' => 'order',
                'related_id' => $orderId,
            ]);
            $this->notificationService->notifyUsers([(int) ($order['created_by'] ?? 0)], [
                'category' => 'delivery',
                'type' => 'rider_assigned',
                'title' => 'Rider assigned',
                'message' => 'A rider was assigned to your order ' . $reference . '.',
                'link' => site_url('customer/order-details/' . $orderId),
                'related_type' => 'order',
                'related_id' => $orderId,
            ]);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Rider assigned successfully']);
    }

    /**
     * Admin: Deliver order to rider
     */
    public function deliverOrderToRider()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $sessionCheck = $this->checkSessionTimeout();
        if ($sessionCheck !== true) {
            return $sessionCheck;
        }

        if ((string) $this->session->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Admin only.');
        }

        // Try to get order_id from POST JSON first, then from regular POST
        $jsonInput = $this->request->getJSON();
        $orderId = null;
        
        if ($jsonInput && isset($jsonInput->order_id)) {
            $orderId = $jsonInput->order_id;
        } else {
            $orderId = $this->request->getPost('order_id');
        }

        if (!$orderId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Order ID is required']);
        }

        try {
            $this->orderModel->markOrderDeliveredToRider($orderId);
            $order = $this->orderModel->getOrder((int) $orderId);
            if ($order) {
                $reference = (string) ($order['reference_number'] ?? ('#' . $orderId));
                $this->notificationService->notifyOrderAudience($order, [
                    'category' => 'delivery',
                    'type' => 'order_handed_to_rider',
                    'title' => 'Order handed to rider',
                    'message' => 'Order ' . $reference . ' was handed to the rider.',
                    'link' => site_url('rider/order-details/' . (int) $orderId),
                    'related_type' => 'order',
                    'related_id' => (int) $orderId,
                ], false, true, false);
            }
            return $this->response->setJSON(['success' => true, 'message' => 'Order delivered to rider']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Get orders ready for pickup (for admin side)
     */
    public function getOrdersReadyForPickup()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        if ((string) $this->session->get('user_role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        try {
            $orders = $this->orderModel->getOrdersReadyForPickup();
            return $this->response->setJSON(['success' => true, 'orders' => $orders]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Submit delivery proof with image
     */
    public function submitDeliveryProof()
    {
        $accessCheck = $this->checkRiderAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $orderId = (int) $this->request->getPost('order_id');
        $deliveryNotes = $this->request->getPost('delivery_notes');
        $finalLat = $this->parseCoordinate($this->request->getPost('final_rider_latitude'));
        $finalLng = $this->parseCoordinate($this->request->getPost('final_rider_longitude'));
        $proofImage = $this->request->getFile('delivery_proof');

        if (!$orderId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Order ID is required']);
        }

        if (!$proofImage || !$proofImage->isValid()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Please select a valid image file']);
        }

        try {
            $shipment = $this->orderModel->getShipmentByOrderId($orderId);
            $riderId = (int) $this->session->get('user_id');
            if (! $shipment) {
                return $this->response->setJSON(['success' => false, 'message' => 'Shipment not found']);
            }
            if ((int) ($shipment['assigned_rider_id'] ?? 0) !== $riderId) {
                return $this->response->setJSON(['success' => false, 'message' => 'You are not assigned to this order']);
            }
            if ((string) ($shipment['status'] ?? '') !== 'to_receive') {
                return $this->response->setJSON(['success' => false, 'message' => 'Order must be out for delivery before completion']);
            }

            // Validate image
            if (!$proofImage->isValid() || $proofImage->getSize() <= 0) {
                return $this->response->setJSON(['success' => false, 'message' => 'Invalid image file']);
            }

            // Check file size (max 5MB)
            if ($proofImage->getSize() > 5 * 1024 * 1024) {
                return $this->response->setJSON(['success' => false, 'message' => 'Image size must be less than 5MB']);
            }

            // Check file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!in_array($proofImage->getMimeType(), $allowedTypes)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Only JPEG, PNG, and GIF images are allowed']);
            }

            $effectiveLat = $finalLat ?? (isset($shipment['rider_latitude']) ? (float) $shipment['rider_latitude'] : null);
            $effectiveLng = $finalLng ?? (isset($shipment['rider_longitude']) ? (float) $shipment['rider_longitude'] : null);
            $deliveryLat = isset($shipment['delivery_latitude']) ? (float) $shipment['delivery_latitude'] : null;
            $deliveryLng = isset($shipment['delivery_longitude']) ? (float) $shipment['delivery_longitude'] : null;
            if ($effectiveLat !== null && $effectiveLng !== null && $deliveryLat !== null && $deliveryLng !== null) {
                $meters = $this->calculateDistanceMeters($effectiveLat, $effectiveLng, $deliveryLat, $deliveryLng);
                $maxMeters = (float) (getenv('DELIVERY_COMPLETION_MAX_DISTANCE_METERS') ?: 500);
                if ($meters > $maxMeters) {
                    return $this->response->setJSON(['success' => false, 'message' => 'You are too far from customer location to complete this delivery']);
                }
            }

            // Generate unique filename
            $filename = 'delivery_proof_' . $orderId . '_' . time() . '.' . $proofImage->getExtension();
            
            // Move file to uploads directory
            $uploadPath = WRITEPATH . 'uploads/delivery_proofs/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            if ($proofImage->move($uploadPath, $filename)) {
                $shipmentUpdate = [
                    'status' => 'delivered',
                    'delivery_proof_image' => $filename,
                    'delivery_notes' => $deliveryNotes,
                    'delivery_proof_submitted_at' => date('Y-m-d H:i:s'),
                    'delivered_at' => date('Y-m-d H:i:s'),
                ];

                if ($effectiveLat !== null && $effectiveLng !== null) {
                    $shipmentUpdate += [
                        'final_rider_latitude' => $effectiveLat,
                        'final_rider_longitude' => $effectiveLng,
                        'delivered_latitude' => $effectiveLat,
                        'delivered_longitude' => $effectiveLng,
                        'last_location_updated_at' => date('Y-m-d H:i:s'),
                    ];
                }

                $updated = $this->orderModel->updateOrder(
                    $orderId,
                    [],
                    [],
                    $shipmentUpdate
                );

                if (! $updated) {
                    @unlink($uploadPath . $filename);
                    return $this->response->setJSON(['success' => false, 'message' => 'Failed to complete delivery']);
                }

                $this->syncOrderToRecord($orderId);
                $order = $this->orderModel->getOrder($orderId);
                if ($order) {
                    $reference = (string) ($order['reference_number'] ?? ('#' . $orderId));
                    $this->notificationService->notifyAdmins([
                        'category' => 'delivery',
                        'type' => 'delivery_proof',
                        'title' => 'Delivery proof submitted',
                        'message' => 'Rider submitted proof for order ' . $reference . '.',
                        'link' => site_url('admin/order-details/' . $orderId . '#delivery-proof'),
                        'related_type' => 'order',
                        'related_id' => $orderId,
                    ]);
                }
                
                return $this->response->setJSON(['success' => true, 'message' => 'Delivery proof submitted successfully']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to upload image']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Get delivery proof for admin viewing
     */
    public function getDeliveryProof()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        if ((string) $this->session->get('user_role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        // Try to get order_id from POST JSON first, then from regular POST
        $jsonInput = $this->request->getJSON();
        $orderId = null;
        
        if ($jsonInput && isset($jsonInput->order_id)) {
            $orderId = $jsonInput->order_id;
        } else {
            $orderId = $this->request->getPost('order_id');
        }
        
        if (!$orderId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Order ID is required']);
        }

        try {
            $db = \Config\Database::connect();
            $proof = $db->table('order_shipments')
                ->select('delivery_proof_image, delivery_notes, delivery_proof_submitted_at')
                ->where('order_id', $orderId)
                ->where('delivery_proof_image IS NOT NULL')
                ->get()
                ->getRowArray();

            if (!$proof) {
                return $this->response->setJSON(['success' => false, 'message' => 'No delivery proof found']);
            }

            return $this->response->setJSON([
                'success' => true,
                'proof' => [
                    'image' => $proof['delivery_proof_image'],
                    'notes' => $proof['delivery_notes'],
                    'submitted_at' => $proof['delivery_proof_submitted_at']
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Serve delivery proof images
     */
    public function serveDeliveryProof($filename = null)
    {
        if (!$filename) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'File not found']);
        }

        // Security: Validate filename format
        if (!preg_match('/^delivery_proof_\d+_\d+\.(jpg|jpeg|png|gif)$/i', $filename)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid filename']);
        }

        $filePath = WRITEPATH . 'uploads/delivery_proofs/' . $filename;
        
        if (!file_exists($filePath)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'File not found']);
        }

        // Get file info
        $fileInfo = new \CodeIgniter\Files\File($filePath);
        $mimeType = $fileInfo->getMimeType();
        
        // Serve the file
        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Length', (string) $fileInfo->getSize())
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setBody(file_get_contents($filePath));
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
        $variantId = (int) ($this->request->getPost('variant_id') ?? 0);
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

        $product = $this->productModel->getProductBaseById($productId);
        if (! $product || (int) ($product['is_active'] ?? 0) !== 1) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Product not found.',
            ]);
        }

        $variants = $this->productModel->getProductVariants($productId);
        $hasFlavorChoices = $this->usesFlavorSelection((string) ($product['category'] ?? '')) && $this->hasNamedVariants($variants);
        $selectedVariant = null;
        $availableStock = (int) ($product['stock_qty'] ?? 0);

        if ($hasFlavorChoices) {
            if ($variantId <= 0) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Please select a flavor.',
                ]);
            }

            $selectedVariant = $this->productModel->getProductVariant($productId, $variantId);
            if (
                ! $selectedVariant
                || (int) ($selectedVariant['is_active'] ?? 0) !== 1
                || trim((string) ($selectedVariant['flavor'] ?? '')) === ''
            ) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Invalid flavor selected.',
                ]);
            }

            $availableStock = (int) ($selectedVariant['stock_qty'] ?? 0);
        }

        if ($availableStock <= 0) {
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => 'This product is out of stock.',
            ]);
        }

        $cart = $this->getCustomerCart();
        $items = $cart['raw_items'];
        $cartKey = $this->makeCartKey($productId, $selectedVariant ? (int) $selectedVariant['id'] : null);
        $currentQty = (int) ($items[$cartKey] ?? 0);
        $requestedQty = $currentQty + $quantity;

        if ($requestedQty > $availableStock) {
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => 'Insufficient stock.',
                'available' => $availableStock,
            ]);
        }

        $items[$cartKey] = $requestedQty;
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

        $cartKey = (string) ($this->request->getPost('cart_key') ?? '');
        $productId = (int) $this->request->getPost('product_id');
        $quantity = (int) ($this->request->getPost('quantity') ?? 0);

        if ($cartKey === '') {
            $cartKey = (string) $productId;
        }

        [$productId, $variantId] = $this->parseCartKey($cartKey);
        if ($productId <= 0) {
            return redirect()->to('/customer/cart')->with('error', 'Invalid product.');
        }

        $cartProduct = $this->resolveCartProduct($cartKey);
        if (! $cartProduct) {
            return redirect()->to('/customer/cart')->with('error', 'Product not found.');
        }

        $items = $this->getCustomerCart()['raw_items'];
        if ($quantity <= 0) {
            unset($items[$cartKey]);
        } else {
            $quantity = min($quantity, (int) $cartProduct['stock']);
            $items[$cartKey] = $quantity;
        }

        $this->setCustomerCartRawItems($items);

        return redirect()->back()->with('success', 'Cart updated successfully.');
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

        $cartKey = (string) ($this->request->getPost('cart_key') ?? '');
        $productId = (int) $this->request->getPost('product_id');
        if ($cartKey === '') {
            $cartKey = (string) $productId;
        }

        [$productId] = $this->parseCartKey($cartKey);
        if ($productId <= 0) {
            return redirect()->to('/customer/cart')->with('error', 'Invalid product.');
        }

        $items = $this->getCustomerCart()['raw_items'];
        unset($items[$cartKey]);
        $this->setCustomerCartRawItems($items);

        return redirect()->back()->with('success', 'Item removed from cart.');
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
            $cartProduct = $this->resolveCartProduct((string) ($item['cart_key'] ?? $item['id'] ?? ''));
            if (! $cartProduct || (int) $cartProduct['stock'] < (int) $item['quantity']) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => 'Insufficient stock for one of the items.',
                ]);
            }
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
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
                    'status' => 'to_ship',
                ])
            );

            if (! $orderId) {
                throw new \RuntimeException('Failed to create order.');
            }

            if (! $this->productModel->reserveStockForItems(
                $orderItems,
                'order',
                (int) $orderId,
                (int) $this->session->get('user_id')
            )) {
                throw new \RuntimeException('Insufficient stock for one of the selected items.');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Order transaction failed.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        $this->clearCustomerCart();
        $order = $this->orderModel->getOrder((int) $orderId);
        if ($order) {
            $reference = (string) ($order['reference_number'] ?? ('#' . $orderId));
            $this->notificationService->notifyUsers([$customerId], [
                'category' => 'orders',
                'type' => 'order_created',
                'title' => 'Order placed',
                'message' => 'Your order ' . $reference . ' was placed successfully.',
                'link' => site_url('customer/order-details/' . (int) $orderId),
                'related_type' => 'order',
                'related_id' => (int) $orderId,
            ]);
            $this->notificationService->notifyAdmins([
                'category' => 'orders',
                'type' => 'new_order',
                'title' => 'New customer order',
                'message' => 'New direct order ' . $reference . ' needs processing.',
                'link' => site_url('orders?order_id=' . (int) $orderId . '#order-' . (int) $orderId),
                'related_type' => 'order',
                'related_id' => (int) $orderId,
            ]);
        }

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
        $customer = $this->userModel->find((int) $this->session->get('user_id'));

        return view('customer/checkout', $this->getCustomerPageData('Checkout', 'cart', [
            'cart_items' => $cart['items'],
            'estimated_total' => $cart['total'],
            'customer_delivery_address' => is_array($customer) ? $this->buildCustomerAddressString($customer) : '',
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

        $deliveryData = $this->getCheckoutDeliveryData($customer);
        if ($deliveryData === null) {
            return redirect()->to('/customer/products')->with('error', 'Please enter a delivery address or use your saved address.');
        }
        if (!isset($deliveryData['delivery_latitude'], $deliveryData['delivery_longitude'])) {
            return redirect()->to('/customer/checkout')->with('error', 'Please confirm your exact delivery location on the map.');
        }
        $deliveryDescription = trim(strip_tags((string) ($this->request->getPost('delivery_description') ?? '')));

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
                'status' => 'to_ship',
                'shipping_address' => (string) $deliveryData['shipping_address'],
                'delivery_address' => (string) $deliveryData['shipping_address'],
                'delivery_latitude' => $deliveryData['delivery_latitude'],
                'delivery_longitude' => $deliveryData['delivery_longitude'],
                'notes' => $deliveryDescription,
            ]);
            $shipmentData = array_merge($shipmentData, $this->getStoreShipmentData());
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
                'shipping_address' => (string) $deliveryData['shipping_address'],
                'delivery_address' => (string) $deliveryData['shipping_address'],
                'delivery_latitude' => $deliveryData['delivery_latitude'],
                'delivery_longitude' => $deliveryData['delivery_longitude'],
                'notes' => $deliveryDescription,
            ]);
            $shipmentData = array_merge($shipmentData, $this->getStoreShipmentData());
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

            // Orders move straight to "to_ship", so reserve stock immediately
            // for both COD and GCash. Cancellation restores it.
            if (
                ! $this->productModel->reserveStockForItems(
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

            $redirectTab = 'to_ship';
            $successMessage = $paymentMethod === 'cash_on_delivery'
                ? 'Order placed successfully. COD payment is pending.'
                : 'GCash transaction successful. Your order is marked as paid.';
            $order = $this->orderModel->getOrder((int) $orderId);
            if ($order) {
                $reference = (string) ($order['reference_number'] ?? ('#' . $orderId));
                $this->notificationService->notifyUsers([$customerId], [
                    'category' => 'orders',
                    'type' => 'order_created',
                    'title' => 'Order placed',
                    'message' => 'Your order ' . $reference . ' was placed successfully.',
                    'link' => site_url('customer/order-details/' . (int) $orderId),
                    'related_type' => 'order',
                    'related_id' => (int) $orderId,
                ]);
                $this->notificationService->notifyAdmins([
                    'category' => 'orders',
                    'type' => $paymentMethod === 'gcash' ? 'payment_received' : 'new_order',
                    'title' => $paymentMethod === 'gcash' ? 'GCash order paid' : 'New COD order',
                    'message' => 'Order ' . $reference . ' is ready for processing.',
                    'link' => $paymentMethod === 'gcash'
                        ? site_url('admin/order-details/' . (int) $orderId)
                        : site_url('orders?order_id=' . (int) $orderId . '#order-' . (int) $orderId),
                    'related_type' => 'order',
                    'related_id' => (int) $orderId,
                ]);
            }
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

        // Normalize: productId or productId:variantId => positive int quantity
        $normalized = [];
        foreach ($rawItems as $key => $qty) {
            $key = (string) $key;
            $qty = (int) $qty;
            [$productId] = $this->parseCartKey($key);
            if ($productId > 0 && $qty > 0) {
                $normalized[$key] = $qty;
            }
        }

        $items = [];
        $total = 0.0;

        foreach ($normalized as $cartKey => $qty) {
            $product = $this->resolveCartProduct((string) $cartKey);
            if (! $product) {
                continue;
            }

            $lineTotal = (float) $product['price'] * (int) $qty;
            $total += $lineTotal;

            $items[] = [
                'id' => (int) $product['id'],
                'name' => (string) $product['name'],
                'display_name' => (string) ($product['display_name'] ?? $product['name']),
                'cart_key' => (string) $cartKey,
                'variant_id' => $product['variant_id'] ?? null,
                'flavor' => (string) ($product['flavor'] ?? ''),
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
     * Persist raw cart items (productId or productId:variantId => qty) in session.
     */
    private function setCustomerCartRawItems(array $items): void
    {
        $normalized = [];
        foreach ($items as $key => $qty) {
            $key = (string) $key;
            $qty = (int) $qty;
            [$productId] = $this->parseCartKey($key);
            if ($productId > 0 && $qty > 0) {
                $normalized[$key] = $qty;
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
                'variant_id' => isset($item['variant_id']) ? (int) $item['variant_id'] : null,
                'qty' => (int) ($item['quantity'] ?? $item['qty'] ?? 0),
                'name' => (string) ($item['display_name'] ?? $item['name'] ?? ('Product #' . (string) ($item['id'] ?? ''))),
                'unit_price' => (float) ($item['price'] ?? $item['unit_price'] ?? 0),
            ];
        }, $cartItems);
    }

    private function makeCartKey(int $productId, ?int $variantId = null): string
    {
        return $variantId && $variantId > 0
            ? $productId . ':' . $variantId
            : (string) $productId;
    }

    /**
     * @return array{0:int,1:int|null}
     */
    private function parseCartKey(string $cartKey): array
    {
        $parts = explode(':', $cartKey, 2);
        $productId = (int) ($parts[0] ?? 0);
        $variantId = isset($parts[1]) && $parts[1] !== '' ? (int) $parts[1] : null;

        return [$productId, $variantId && $variantId > 0 ? $variantId : null];
    }

    private function resolveCartProduct(string $cartKey): ?array
    {
        [$productId, $variantId] = $this->parseCartKey($cartKey);
        if ($productId <= 0) {
            return null;
        }

        $product = $this->productModel->getProductBaseById($productId);
        if (! $product || (int) ($product['is_active'] ?? 0) !== 1) {
            return null;
        }

        $resolved = [
            'id' => (int) $product['id'],
            'name' => (string) $product['name'],
            'display_name' => (string) $product['name'],
            'category' => (string) ($product['category'] ?? ''),
            'price' => (float) ($product['price'] ?? 0),
            'image' => (string) ($product['image'] ?? $product['image_url'] ?? ''),
            'stock' => (int) ($product['stock_qty'] ?? 0),
            'variant_id' => null,
            'flavor' => '',
        ];

        if ($variantId) {
            $variant = $this->productModel->getProductVariant((int) $product['id'], $variantId);
            if (! $variant || (int) ($variant['is_active'] ?? 0) !== 1) {
                return null;
            }

            $flavor = trim((string) ($variant['flavor'] ?? ''));
            $resolved['variant_id'] = (int) $variant['id'];
            $resolved['flavor'] = $flavor;
            $resolved['display_name'] = $flavor !== ''
                ? $resolved['name'] . ' - ' . $flavor
                : $resolved['name'];
            $resolved['price'] = (float) ($variant['price'] ?? $resolved['price']);
            $resolved['stock'] = (int) ($variant['stock_qty'] ?? 0);
        }

        return $resolved;
    }

    private function usesFlavorSelection(string $category): bool
    {
        return in_array(strtolower($category), ['pods', 'disposable', 'e-liquid'], true);
    }

    private function hasNamedVariants(array $variants): bool
    {
        foreach ($variants as $variant) {
            if (trim((string) ($variant['flavor'] ?? '')) !== '') {
                return true;
            }
        }

        return false;
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

    private function getCheckoutDeliveryData(?array $customer = null): ?array
    {
        $mode = (string) ($this->request->getPost('delivery_address_mode') ?? 'manual');
        $lat = $this->parseCoordinate($this->request->getPost('delivery_latitude'));
        $lng = $this->parseCoordinate($this->request->getPost('delivery_longitude'));

        if ($mode === 'saved_address') {
            if ($customer === null) {
                return null;
            }

            $savedAddress = $this->buildCustomerAddressString($customer);
            if ($savedAddress === '') {
                return null;
            }

            return [
                'shipping_address' => $savedAddress,
                'delivery_latitude' => $lat,
                'delivery_longitude' => $lng,
            ];
        }

        $fields = [
            'delivery_address_line',
            'delivery_barangay',
            'delivery_city',
            'delivery_province',
            'delivery_postal_code',
            'delivery_country',
        ];

        $parts = [];
        foreach ($fields as $field) {
            $value = trim((string) ($this->request->getPost($field) ?? ''));
            if ($value !== '') {
                $parts[] = strip_tags($value);
            }
        }

        if (count($parts) < 6) {
            return null;
        }

        return [
            'shipping_address' => implode(', ', $parts),
            'delivery_latitude' => $lat,
            'delivery_longitude' => $lng,
        ];
    }

    private function parseCoordinate($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function calculateDistanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    private function getStoreShipmentData(): array
    {
        $defaultLat = 6.1352000;
        $defaultLng = 125.2179000;
        $defaultAddress = 'Bula, General Santos City, South Cotabato, Philippines';

        $lat = $this->parseCoordinate(getenv('STORE_LATITUDE') ?: null) ?? $defaultLat;
        $lng = $this->parseCoordinate(getenv('STORE_LONGITUDE') ?: null) ?? $defaultLng;
        $address = trim((string) (getenv('STORE_ADDRESS') ?: ''));

        return [
            'store_latitude' => $lat,
            'store_longitude' => $lng,
            'store_address' => $address !== '' ? $address : $defaultAddress,
        ];
    }

    public function updateRiderLocation()
    {
        $accessCheck = $this->checkRiderAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $orderId = (int) $this->request->getPost('order_id');
        $lat = $this->parseCoordinate($this->request->getPost('rider_latitude'));
        $lng = $this->parseCoordinate($this->request->getPost('rider_longitude'));
        if ($orderId <= 0 || $lat === null || $lng === null) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid location payload']);
        }

        $shipment = $this->orderModel->getShipmentByOrderId($orderId);
        $riderId = (int) $this->session->get('user_id');
        if (! $shipment || (int) ($shipment['assigned_rider_id'] ?? 0) !== $riderId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not authorized for this order']);
        }

        $status = (string) ($shipment['status'] ?? '');
        if (!in_array($status, ['to_receive', 'delivered_to_rider', 'accepted_by_rider'], true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Location updates are only allowed during active delivery']);
        }

        $ok = $this->orderModel->updateOrder($orderId, [], [], [
            'rider_latitude' => $lat,
            'rider_longitude' => $lng,
            'last_location_updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['success' => (bool) $ok]);
    }

    public function orderTracking($orderId)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $order = $this->orderModel->getOrder((int) $orderId);
        if (! $order) {
            return $this->response->setJSON(['success' => false, 'message' => 'Order not found']);
        }

        $role = (string) $this->session->get('user_role');
        $userId = (int) $this->session->get('user_id');

        if ($role === 'customer' && (int) ($order['created_by'] ?? 0) !== $userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }
        if ($role === 'rider' && (int) ($order['assigned_rider_id'] ?? 0) !== $userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }
        if (!in_array($role, ['admin', 'customer', 'rider'], true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $rider = null;
        if (!empty($order['assigned_rider_id'])) {
            $r = $this->userModel->find((int) $order['assigned_rider_id']);
            if ($r) {
                $rider = [
                    'name' => (string) ($r['name'] ?? 'Rider'),
                    'contact' => (string) ($r['phone_number'] ?? $r['phone'] ?? ''),
                ];
            }
        }

        $status = (string) ($order['delivery_status'] ?? 'to_pay');
        $allowCustomerLiveRider = in_array($status, ['delivered_to_rider', 'to_receive', 'delivered', 'completed'], true);
        $riderLat = isset($order['rider_latitude']) ? (float) $order['rider_latitude'] : null;
        $riderLng = isset($order['rider_longitude']) ? (float) $order['rider_longitude'] : null;
        if ($role === 'customer' && ! $allowCustomerLiveRider) {
            $riderLat = null;
            $riderLng = null;
        }

        return $this->response->setJSON([
            'success' => true,
            'tracking' => [
                'order_id' => (int) ($order['id'] ?? 0),
                'status' => $status,
                'phase' => in_array($status, ['ready_for_pickup', 'accepted_by_rider'], true) ? 'pickup' : (in_array($status, ['delivered_to_rider', 'to_receive', 'delivered'], true) ? 'delivery' : 'none'),
                'delivery_address' => (string) ($order['delivery_address'] ?? $order['shipping_address'] ?? ''),
                'delivery_latitude' => isset($order['delivery_latitude']) ? (float) $order['delivery_latitude'] : null,
                'delivery_longitude' => isset($order['delivery_longitude']) ? (float) $order['delivery_longitude'] : null,
                'store_address' => (string) ($order['store_address'] ?? ''),
                'store_latitude' => isset($order['store_latitude']) ? (float) $order['store_latitude'] : null,
                'store_longitude' => isset($order['store_longitude']) ? (float) $order['store_longitude'] : null,
                'rider_latitude' => $riderLat,
                'rider_longitude' => $riderLng,
                'last_location_updated_at' => $order['last_location_updated_at'] ?? null,
                'rider' => $rider,
                'proof_image' => $order['delivery_proof_image'] ?? null,
                'proof_notes' => $order['delivery_notes'] ?? null,
                'proof_submitted_at' => $order['delivery_proof_submitted_at'] ?? null,
                'final_rider_latitude' => isset($order['final_rider_latitude']) ? (float) $order['final_rider_latitude'] : null,
                'final_rider_longitude' => isset($order['final_rider_longitude']) ? (float) $order['final_rider_longitude'] : null,
            ],
        ]);
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

        $product = $this->productModel->getProductBaseById($id);

        if (!$product || (int) ($product['is_active'] ?? 0) !== 1) {
            return redirect()->to('/customer/products')->with('error', 'Product not found.');
        }

        $product['status'] = (int) ($product['is_active'] ?? 0) === 1 ? 'active' : 'inactive';
        $product['stock'] = (int) ($product['stock_qty'] ?? 0);
        $product['variants'] = array_values(array_filter(array_map(static function (array $variant): array {
            return [
                'id' => (int) ($variant['id'] ?? 0),
                'flavor' => (string) ($variant['flavor'] ?? ''),
                'stock' => (int) ($variant['stock_qty'] ?? 0),
                'price' => (float) ($variant['price'] ?? 0),
            ];
        }, $this->productModel->getProductVariants((int) $id)), static fn (array $variant): bool => trim($variant['flavor']) !== ''));

        if ($product['variants'] !== []) {
            $product['stock'] = array_sum(array_column($product['variants'], 'stock'));
        }

        $highlightReviewId = (int) ($this->request->getGet('review_id') ?? 0);
        $productReviews = $this->reviewModel->getReviewsForProduct((int) $id, 'approved');
        if ($highlightReviewId > 0) {
            $highlightReview = $this->reviewModel->find($highlightReviewId);
            $alreadyIncluded = array_filter($productReviews, static fn (array $review): bool => (int) ($review['id'] ?? 0) === $highlightReviewId);
            if (
                $highlightReview
                && $alreadyIncluded === []
                && (int) ($highlightReview['product_id'] ?? 0) === (int) $id
                && (int) ($highlightReview['user_id'] ?? 0) === (int) $this->session->get('user_id')
            ) {
                $productReviews = array_merge([$highlightReview], $productReviews);
            }
        }

        return view('customer/product_details', $this->getCustomerPageData('Product Details', 'products', [
            'product' => $product,
            'age_allowed' => $this->canCustomerPurchase(),
            'productReviews' => $productReviews,
            'highlightReviewId' => $highlightReviewId,
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
            $assignedRiderId = (int) ($order['assigned_rider_id'] ?? 0);
            $assignedRider = $assignedRiderId > 0 ? $this->userModel->find($assignedRiderId) : null;

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
                'shipment_notes' => $order['shipment_notes'] ?? '',
                'contact_number' => $order['contact_number'] ?: ($customerInfo['phone'] ?? 'Not provided'),
                'items' => $order['items'] ?? [],
                'customer' => $customerInfo,
                'assigned_rider_id' => $assignedRiderId,
                'assigned_rider_name' => $assignedRider['name'] ?? null,
                'delivery_proof_image' => $order['delivery_proof_image'] ?? null,
                'delivery_proof_submitted_at' => $order['delivery_proof_submitted_at'] ?? null,
            ];
        }

        $riders = array_values(array_filter(
            $this->userModel->findAll(),
            static fn (array $user): bool => (string) ($user['role'] ?? '') === 'rider'
        ));

        $data = [
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $this->session->get('user_role'),
            'user_shop_name' => $this->session->get('user_shop_name'),
            'page_title' => 'Orders Management',
            'orders' => $orderDetails,
            'riders' => $riders,
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

            $this->syncOrderToRecord((int) $orderId);

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

        $validStatuses = ['to_pay', 'to_ship', 'ready_for_pickup', 'accepted_by_rider', 'delivered_to_rider', 'to_receive', 'completed', 'failed_delivery'];
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

        $currentDeliveryStatus = (string) ($order['delivery_status'] ?? 'to_pay');
        if (
            $newStatus === 'delivered_to_rider'
            && $currentDeliveryStatus !== 'accepted_by_rider'
        ) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Pickup can only be marked after rider accepts the delivery.',
            ]);
        }
        if ($newStatus === 'delivered_to_rider') {
            $shipmentData['picked_up_at'] = date('Y-m-d H:i:s');
        }

        if ($newStatus === 'to_ship' && empty($order['tracking_number'])) {
            $shipmentData['tracking_number'] = $this->generateTrackingNumber();
        }

        if ($newStatus === 'completed') {
            if (($order['delivery_status'] ?? '') !== 'delivered') {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Admin can confirm received only after rider marks order as delivered.',
                ]);
            }
            if (empty($order['delivery_proof_image'])) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Delivery proof is required before admin confirmation.',
                ]);
            }
            $shipmentData['completed_at'] = date('Y-m-d H:i:s');
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
                $this->syncOrderToRecord($orderId);
                $updatedOrder = $this->orderModel->getOrder($orderId) ?? $order;
                $reference = (string) ($updatedOrder['reference_number'] ?? ('#' . $orderId));
                $customerLink = site_url('customer/order-details/' . $orderId);
                $riderLink = site_url('rider/order-details/' . $orderId);
                $adminLink = site_url('admin/order-details/' . $orderId);

                $this->notificationService->notifyUsers([(int) ($updatedOrder['created_by'] ?? 0)], [
                    'category' => 'orders',
                    'type' => 'order_status',
                    'title' => 'Order status updated',
                    'message' => 'Order ' . $reference . ' is now ' . $this->getStatusLabel((string) $newStatus) . '.',
                    'link' => $customerLink,
                    'related_type' => 'order',
                    'related_id' => $orderId,
                ]);
                if (! empty($updatedOrder['assigned_rider_id'])) {
                    $this->notificationService->notifyUsers([(int) $updatedOrder['assigned_rider_id']], [
                        'category' => 'delivery',
                        'type' => 'delivery_status',
                        'title' => 'Delivery status updated',
                        'message' => 'Order ' . $reference . ' is now ' . $this->getStatusLabel((string) $newStatus) . '.',
                        'link' => $riderLink,
                        'related_type' => 'order',
                        'related_id' => $orderId,
                    ]);
                }
                if (in_array($newStatus, ['completed', 'failed_delivery'], true)) {
                    $this->notificationService->notifyAdmins([
                        'category' => 'orders',
                        'type' => $newStatus === 'completed' ? 'approval' : 'cancellation',
                        'title' => $newStatus === 'completed' ? 'Order completed' : 'Delivery failed',
                        'message' => 'Order ' . $reference . ' is now ' . $this->getStatusLabel((string) $newStatus) . '.',
                        'link' => $adminLink,
                        'related_type' => 'order',
                        'related_id' => $orderId,
                    ]);
                }
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
                $reference = (string) ($order['reference_number'] ?? ('#' . $orderId));
                $this->notificationService->notifyUsers([(int) ($order['created_by'] ?? 0)], [
                    'category' => 'delivery',
                    'type' => 'delivery_info',
                    'title' => 'Delivery information updated',
                    'message' => 'Delivery details were updated for order ' . $reference . '.',
                    'link' => site_url('customer/order-details/' . $orderId),
                    'related_type' => 'order',
                    'related_id' => $orderId,
                ]);
                if (! empty($order['assigned_rider_id'])) {
                    $this->notificationService->notifyUsers([(int) $order['assigned_rider_id']], [
                        'category' => 'delivery',
                        'type' => 'delivery_info',
                        'title' => 'Delivery information updated',
                        'message' => 'Delivery details changed for order ' . $reference . '.',
                        'link' => site_url('rider/order-details/' . $orderId),
                        'related_type' => 'order',
                        'related_id' => $orderId,
                    ]);
                }
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

    public function viewRiderOrderDetails($orderId)
    {
        $accessCheck = $this->checkRiderAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $order = $this->orderModel->getOrder((int) $orderId);
        if (! $order) {
            return redirect()->to('/rider/deliveries')->with('error', 'Order not found.');
        }

        $riderId = (int) $this->session->get('user_id');
        $assignedRiderId = (int) ($order['assigned_rider_id'] ?? 0);
        if ($assignedRiderId !== $riderId) {
            return redirect()->to('/rider/deliveries')->with('error', 'Access denied for this order.');
        }
        $order['customer'] = $this->getOrderCustomerInfo(isset($order['created_by']) ? (int) $order['created_by'] : null);

        return view('rider/order_details', $this->getRiderPageData('Order Details', 'deliveries', [
            'order' => $order,
            'items' => $order['items'] ?? [],
        ]));
    }

    public function orderDetailsJson($orderId)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $id = (int) $orderId;
        if ($id <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid order']);
        }

        $order = $this->orderModel->getOrder($id);
        if (! $order) {
            return $this->response->setJSON(['success' => false, 'message' => 'Order not found']);
        }

        $role = (string) $this->session->get('user_role');
        $userId = (int) $this->session->get('user_id');

        if ($role === 'rider') {
            if ((int) ($order['assigned_rider_id'] ?? 0) !== $userId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
            }
        } elseif ($role === 'customer') {
            if ((int) ($order['created_by'] ?? 0) !== $userId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
            }
        } elseif ($role !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $customerInfo = $this->getOrderCustomerInfo(isset($order['created_by']) ? (int) $order['created_by'] : null);

        return $this->response->setJSON([
            'success' => true,
            'order' => [
                'id' => (int) ($order['id'] ?? 0),
                'reference_number' => (string) ($order['reference_number'] ?? ''),
                'delivery_status' => (string) ($order['delivery_status'] ?? 'to_pay'),
                'date' => (string) ($order['date'] ?? ''),
                'total_amount' => (float) ($order['total_amount'] ?? 0),
                'shipping_address' => (string) ($order['shipping_address'] ?? ($customerInfo['address'] ?? 'Not provided')),
                'contact_number' => (string) ($order['contact_number'] ?? ($customerInfo['phone'] ?? 'Not provided')),
                'shipment_notes' => (string) ($order['shipment_notes'] ?? ''),
                'delivery_latitude' => isset($order['delivery_latitude']) ? (float) $order['delivery_latitude'] : null,
                'delivery_longitude' => isset($order['delivery_longitude']) ? (float) $order['delivery_longitude'] : null,
                'rider_latitude' => isset($order['rider_latitude']) ? (float) $order['rider_latitude'] : null,
                'rider_longitude' => isset($order['rider_longitude']) ? (float) $order['rider_longitude'] : null,
                'last_location_updated_at' => $order['last_location_updated_at'] ?? null,
                'store_address' => (string) ($order['store_address'] ?? ''),
                'store_latitude' => isset($order['store_latitude']) ? (float) $order['store_latitude'] : null,
                'store_longitude' => isset($order['store_longitude']) ? (float) $order['store_longitude'] : null,
                'customer_name' => (string) ($customerInfo['name'] ?? 'Customer'),
                'customer_email' => (string) ($customerInfo['email'] ?? ''),
                'items' => array_values(array_map(static function ($item) {
                    return [
                        'name' => (string) ($item['name'] ?? 'Product'),
                        'qty' => (int) ($item['qty'] ?? 0),
                        'unit_price' => (float) ($item['unit_price'] ?? 0),
                    ];
                }, (array) ($order['items'] ?? []))),
            ],
        ]);
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

        $this->syncOrderToRecord((int) ($order['id'] ?? 0));
        $orderId = (int) ($order['id'] ?? 0);
        $reference = (string) ($order['reference_number'] ?? ('#' . $orderId));
        $this->notificationService->notifyUsers([(int) ($order['created_by'] ?? 0)], [
            'category' => 'payments',
            'type' => 'payment_received',
            'title' => 'Payment processed',
            'message' => 'Payment for order ' . $reference . ' was processed.',
            'link' => site_url('customer/order-details/' . $orderId),
            'related_type' => 'order',
            'related_id' => $orderId,
        ]);
        $this->notificationService->notifyAdmins([
            'category' => 'payments',
            'type' => 'payment_received',
            'title' => 'Payment processed',
            'message' => 'Customer paid order ' . $reference . '.',
            'link' => site_url('admin/order-details/' . $orderId),
            'related_type' => 'order',
            'related_id' => $orderId,
        ]);

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

        $this->syncOrderToRecord((int) ($order['id'] ?? 0));
        $orderId = (int) ($order['id'] ?? 0);
        $reference = (string) ($order['reference_number'] ?? ('#' . $orderId));
        $this->notificationService->notifyAdmins([
            'category' => 'cancellations',
            'type' => 'order_cancelled',
            'title' => 'Order cancelled',
            'message' => 'Customer cancelled order ' . $reference . '.',
            'link' => site_url('admin/order-details/' . $orderId),
            'related_type' => 'order',
            'related_id' => $orderId,
        ]);

        return redirect()->to('/customer/orders')->with('success', 'Order cancelled successfully.');
    }

    private function confirmOrderReceived($order)
    {
        if (($order['delivery_status'] ?? '') !== 'delivered') {
            return redirect()->to('/customer/orders')->with('error', 'Order cannot be confirmed yet.');
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

        $this->syncOrderToRecord($orderId);
        $reference = (string) ($order['reference_number'] ?? ('#' . $orderId));
        $this->notificationService->notifyAdmins([
            'category' => 'approvals',
            'type' => 'order_received',
            'title' => 'Order received confirmed',
            'message' => 'Customer confirmed receipt of order ' . $reference . '.',
            'link' => site_url('admin/order-details/' . $orderId),
            'related_type' => 'order',
            'related_id' => $orderId,
        ]);
        if (! empty($order['assigned_rider_id'])) {
            $this->notificationService->notifyUsers([(int) $order['assigned_rider_id']], [
                'category' => 'delivery',
                'type' => 'order_received',
                'title' => 'Order received confirmed',
                'message' => 'Customer confirmed receipt of order ' . $reference . '.',
                'link' => site_url('rider/order-details/' . $orderId),
                'related_type' => 'order',
                'related_id' => $orderId,
            ]);
        }

        return redirect()->to('/customer/orders?tab=completed')->with('success', 'Order received successfully. Order is now completed.');
    }

    private function syncOrderToRecord(int $orderId): void
    {
        if ($orderId <= 0) {
            return;
        }

        try {
            if (!\Config\Database::connect()->tableExists('records')) {
                return;
            }

            $order = $this->orderModel->getOrder($orderId);
            if (!$order) {
                return;
            }

            $referenceNumber = trim((string) ($order['reference_number'] ?? ''));
            if ($referenceNumber === '') {
                return;
            }

            $recordDate = (string) ($order['record_date'] ?? $order['date'] ?? date('Y-m-d'));
            $recordDateTs = strtotime($recordDate);
            $normalizedDate = $recordDateTs !== false ? date('Y-m-d', $recordDateTs) : date('Y-m-d');

            $deliveryStatus = strtolower((string) ($order['delivery_status'] ?? 'to_pay'));
            $recordStatus = 'pending';
            if (in_array($deliveryStatus, ['cancelled', 'failed_delivery'], true)) {
                $recordStatus = 'cancelled';
            } elseif ($deliveryStatus === 'completed') {
                $recordStatus = 'completed';
            }

            $paymentMethod = strtolower((string) ($order['payment_method'] ?? 'cash'));
            if (!in_array($paymentMethod, ['cash', 'card', 'gcash', 'bank_transfer'], true)) {
                $paymentMethod = 'cash';
            }

            $paymentStatus = strtolower((string) ($order['payment_status'] ?? 'unpaid'));
            if (!in_array($paymentStatus, ['paid', 'partial', 'unpaid'], true)) {
                $paymentStatus = 'unpaid';
            }
            if ($recordStatus === 'completed') {
                $paymentStatus = 'paid';
            }

            $payload = [
                'record_type' => 'sales',
                'record_date' => $normalizedDate,
                'reference_number' => $referenceNumber,
                'title' => trim((string) ($order['title'] ?? 'Sales Order')),
                'description' => trim((string) ($order['description'] ?? 'Auto-synced from Orders module')),
                'quantity' => max(0, (int) ($order['quantity'] ?? 0)),
                'unit_price' => max(0, (float) ($order['unit_price'] ?? 0)),
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'status' => $recordStatus,
                'notes' => trim((string) ($order['notes'] ?? '')),
                'created_by' => (int) ($this->session->get('user_id') ?? $order['created_by'] ?? 0) ?: null,
            ];

            $existing = $this->recordModel
                ->where('record_type', 'sales')
                ->where('reference_number', $referenceNumber)
                ->first();

            if ($existing && isset($existing['id'])) {
                $this->recordModel->update((int) $existing['id'], $payload);
            } else {
                $this->recordModel->insert($payload);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed syncing order {id} to records: {message}', [
                'id' => $orderId,
                'message' => $e->getMessage(),
            ]);
        }
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

    public function submitProductReview()
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $orderId = (int) $this->request->getPost('order_id');
        $productId = (int) $this->request->getPost('product_id');
        $rating = (int) $this->request->getPost('rating');
        $reviewText = trim((string) $this->request->getPost('review_text'));
        $customerId = (int) $this->session->get('user_id');

        if ($orderId <= 0 || $productId <= 0) {
            return redirect()->to('/customer/orders?tab=to_review')->with('error', 'Invalid product review.');
        }

        if ($rating < 1 || $rating > 5) {
            return redirect()->back()->withInput()->with('error', 'Rating must be between 1 and 5 stars.');
        }

        $order = $this->orderModel->getOrder($orderId);
        if (! $order || (int) ($order['created_by'] ?? 0) !== $customerId) {
            return redirect()->to('/customer/orders?tab=to_review')->with('error', 'Order not found.');
        }

        if (($order['delivery_status'] ?? '') !== 'completed') {
            return redirect()->to('/customer/orders')->with('error', 'Only completed orders can be reviewed.');
        }

        $hasProduct = false;
        foreach ((array) ($order['items'] ?? []) as $item) {
            if ((int) ($item['id'] ?? 0) === $productId) {
                $hasProduct = true;
                break;
            }
        }

        if (! $hasProduct) {
            return redirect()->to('/customer/orders?tab=to_review')->with('error', 'This product is not part of that order.');
        }

        $payload = [
            'order_id' => $orderId,
            'product_id' => $productId,
            'user_id' => $customerId,
            'rating' => $rating,
            'review_text' => $reviewText !== '' ? mb_substr($reviewText, 0, 1000) : null,
            'status' => 'approved',
        ];

        $existing = $this->reviewModel->getReviewForOrderProduct($orderId, $productId, $customerId);
        $saved = $existing && isset($existing['id'])
            ? $this->reviewModel->update((int) $existing['id'], $payload)
            : (bool) $this->reviewModel->insert($payload);

        if (! $saved) {
            return redirect()->back()->withInput()->with('error', 'Failed to save product review. Please try again.');
        }
        $reviewId = (int) ($existing['id'] ?? $this->reviewModel->getInsertID() ?? 0);
        $this->notificationService->notifyAdmins([
            'category' => 'approvals',
            'type' => 'review_submitted',
            'title' => 'Product review submitted',
            'message' => 'A customer submitted a product review for order #' . $orderId . '.',
            'link' => site_url('products/view/' . $productId . '?review_id=' . $reviewId . '#review-' . $reviewId),
            'related_type' => 'review',
            'related_id' => $reviewId,
        ]);

        return redirect()->to('/customer/orders?tab=to_review')->with('success', 'Your product review has been posted.');
    }

    public function getProductReviews($productId = null)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        if ((string) $this->session->get('user_role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $id = (int) $productId;
        if ($id <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid product']);
        }

        return $this->response->setJSON([
            'success' => true,
            'summary' => $this->reviewModel->getProductReviewSummary($id),
            'reviews' => $this->reviewModel->getReviewsForProduct($id),
        ]);
    }

    public function approveReview($reviewId = null)
    {
        return $this->updateProductReviewStatus((int) $reviewId, 'approved');
    }

    public function rejectReview($reviewId = null)
    {
        return $this->updateProductReviewStatus((int) $reviewId, 'rejected');
    }

    private function updateProductReviewStatus(int $reviewId, string $status)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        if ((string) $this->session->get('user_role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        if ($reviewId <= 0 || ! in_array($status, ['approved', 'rejected'], true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid review']);
        }

        $review = $this->reviewModel->find($reviewId);
        $success = (bool) $this->reviewModel->update($reviewId, ['status' => $status]);
        if ($success && $review) {
            $this->notificationService->notifyUsers([(int) ($review['user_id'] ?? 0)], [
                'category' => 'approvals',
                'type' => 'review_' . $status,
                'title' => 'Review ' . ucfirst($status),
                'message' => 'Your product review was ' . $status . '.',
                'link' => site_url('customer/orders?tab=completed'),
                'related_type' => 'review',
                'related_id' => $reviewId,
            ]);
        }

        return $this->response->setJSON([
            'success' => $success,
        ]);
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
