<?php

namespace App\Controllers;

use App\Models\DashboardModel;
use App\Models\UserModel;
use App\Models\ProductModel;
use App\Models\OrderModel;
use App\Models\ReviewModel;
use App\Models\RecordModel;
use App\Models\ShopSettingsModel;
use App\Libraries\SecurityAuditService;
use App\Libraries\NotificationService;
use App\Libraries\ActivityLogger;
use Config\ActivityLogTypes;

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
    protected ActivityLogger $activityLogger;
    protected ShopSettingsModel $shopSettingsModel;

    public function __construct()
    {
        $this->session = session();
        $this->dashboardModel = new DashboardModel();
        $this->userModel = new UserModel();
        $this->shopSettingsModel = new ShopSettingsModel();
        $this->productModel = new ProductModel();
        $this->orderModel = new OrderModel();
        $this->reviewModel = new ReviewModel();
        $this->recordModel = new RecordModel();
        $this->securityAuditService = new SecurityAuditService();
        $this->notificationService = new NotificationService();
        $this->activityLogger = new ActivityLogger();
        helper(['return_refund', 'order']);
    }

    /**
     * @param array<string, mixed> $details
     */
    protected function logUserActivity(
        string $action,
        string $actionType,
        array $details = [],
        string $status = 'success',
        ?int $userId = null
    ): void {
        $uid = $userId ?? (int) $this->session->get('user_id');
        $this->activityLogger->logUserAction(
            $uid > 0 ? $uid : null,
            $action,
            $actionType,
            $details !== [] ? $details : null,
            $status
        );
    }

    /**
     * @param array<string, mixed>|null $order
     * @param array<string, mixed> $extra
     */
    protected function logOrderActivity(
        string $message,
        string $actionType,
        int $orderId,
        ?array $order = null,
        array $extra = [],
        string $status = 'success'
    ): void {
        $reference = is_array($order)
            ? (string) ($order['reference_number'] ?? ('#' . $orderId))
            : null;

        if ($reference === null && $orderId > 0) {
            $fetched = $this->orderModel->getOrder($orderId);
            $reference = is_array($fetched)
                ? (string) ($fetched['reference_number'] ?? ('#' . $orderId))
                : ('#' . $orderId);
        }

        $this->logUserActivity(
            $message,
            $actionType,
            array_merge([
                'order_id' => $orderId,
                'reference_number' => $reference ?? ('#' . $orderId),
            ], $extra),
            $status
        );
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

    /**
     * Allow access for back-office users (admin and custom admin-like roles).
     */
    private function hasAdminPanelAccess(): bool
    {
        $role = strtolower(trim((string) $this->session->get('user_role')));
        return $role !== '' && !in_array($role, ['customer', 'rider'], true);
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
        $adminSummary = $this->dashboardModel->getAdminSummaryStats(10);
        $dailySalesChart = $this->dashboardModel->getDailySalesChartData(14);
        $monthlyProfitChart = $this->dashboardModel->getMonthlyProfitChartData(12);
        $bestSellingChart = $this->dashboardModel->getBestSellingProductsChartData(8);
        $recentOrdersList = $this->dashboardModel->getRecentOrdersList(8);
        $lowStockProducts = $this->dashboardModel->getLowStockProducts(10, 8);

        $data = [
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $userRole,
            'user_shop_name' => $this->session->get('user_shop_name'),
            'page_title' => 'Admin Dashboard',
            'total_products' => $totalProducts,
            'orders_today' => $analytics['orders'],
            'revenue_today' => $analytics['revenue'],
            'total_customers' => $this->dashboardModel->getTotalCustomers(),
            'active_sessions' => $analytics['active_sessions'],
            'new_users' => $analytics['new_users'],
            'recent_registrations' => $recentRegistrations,
            'user_stats' => $userStats,
            'system_metrics' => $systemMetrics,
            'system_performance' => $analytics['orders'] > 0 ? '100%' : '0%',
            'notifications' => $this->getNotifications($analytics['orders'], $analytics['revenue']),
            'growth_rate' => $this->dashboardModel->getGrowthRate($userRole, $shopName),
            'recent_orders' => $analytics['orders'],
            'monthly_revenue' => $monthlyAnalytics['revenue'],
            'admin_summary' => $adminSummary,
            'daily_sales_chart' => $dailySalesChart,
            'monthly_profit_chart' => $monthlyProfitChart,
            'best_selling_chart' => $bestSellingChart,
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
     * Rider return pickup list (separate from regular deliveries).
     */
    public function riderReturnPickups()
    {
        $accessCheck = $this->checkRiderAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        helper('return_refund');

        return view('rider/returns', $this->getRiderPageData('Return Pickups', 'returns', [
            'returns' => $this->getRiderReturnPickups(),
        ]));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getRiderReturnPickups(): array
    {
        $riderId = (int) $this->session->get('user_id');
        $orders = $this->orderModel->getRiderReturnPickups($riderId);

        foreach ($orders as &$order) {
            $order['customer'] = $this->getOrderCustomerInfo(isset($order['created_by']) ? (int) $order['created_by'] : null);
        }
        unset($order);

        return $orders;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getRiderDeliveries(): array
    {
        $riderId = (int) $this->session->get('user_id');
        $orders = $this->orderModel->getAdminOrders();
        $deliveryStatuses = [
            'to_ship', 'to_receive', 'failed_delivery', 'completed', 'delivered',
            'ready_for_pickup', 'accepted_by_rider', 'delivered_to_rider', 'cancelled',
        ];

        $deliveries = array_values(array_filter($orders, static function (array $order) use ($deliveryStatuses, $riderId): bool {
            $status = (string) ($order['delivery_status'] ?? 'to_pay');
            $orderStatus = strtolower(trim((string) ($order['status'] ?? '')));
            $assignedRiderId = (int) ($order['assigned_rider_id'] ?? 0);

            return $assignedRiderId > 0
                && $assignedRiderId === $riderId
                && (in_array($status, $deliveryStatuses, true) || $orderStatus === 'cancelled');
        }));

        foreach ($deliveries as &$delivery) {
            $orderStatus = strtolower(trim((string) ($delivery['status'] ?? '')));
            $shipmentStatus = strtolower(trim((string) ($delivery['delivery_status'] ?? '')));
            if ($orderStatus === 'cancelled' || $shipmentStatus === 'cancelled') {
                $delivery['delivery_status'] = 'cancelled';
            }
            $delivery['customer'] = $this->getOrderCustomerInfo(isset($delivery['created_by']) ? (int) $delivery['created_by'] : null);
            $normalizedContact = $this->normalizeContactNumber((string) ($delivery['contact_number'] ?? ''));
            $delivery['contact_number'] = $normalizedContact !== ''
                ? $normalizedContact
                : (string) (($delivery['customer']['phone'] ?? '') !== '' ? $delivery['customer']['phone'] : 'Not provided');
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
            $normalizedContact = $this->normalizeContactNumber((string) ($delivery['contact_number'] ?? ''));
            $delivery['contact_number'] = $normalizedContact !== ''
                ? $normalizedContact
                : (string) (($delivery['customer']['phone'] ?? '') !== '' ? $delivery['customer']['phone'] : 'Not provided');
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
            'customer_delivery_latitude' => is_array($customer) ? $this->getCustomerDeliveryLatitude($customer) : null,
            'customer_delivery_longitude' => is_array($customer) ? $this->getCustomerDeliveryLongitude($customer) : null,
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
        $orders = $this->attachReturnRefundState($orders);
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
     * @param array<int, array<string, mixed>> $orders
     * @return array<int, array<string, mixed>>
     */
    private function attachReturnRefundState(array $orders): array
    {
        foreach ($orders as &$order) {
            $order['return_meta'] = parse_return_meta(
                (string) ($order['shipment_notes'] ?? ''),
                (string) ($order['delivery_notes'] ?? '')
            );
            $order['shipment_notes_display'] = shipment_notes_for_display((string) ($order['shipment_notes'] ?? ''));
            $order['can_cancel'] = customer_can_cancel_order($order);
            $eligibility = customer_can_request_return($order);
            $order['can_request_return'] = $eligibility['allowed'];
            $order['return_request_message'] = $eligibility['message'];
        }
        unset($order);

        return $orders;
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
        $customer = $this->userModel->find((int) $this->session->get('user_id'));

        return view('customer/cart', $this->getCustomerPageData('Cart', 'cart', [
            'cart_items' => $cartItems,
            'estimated_total' => $estimatedTotal,
            'cart_total' => $estimatedTotal,
            'age_allowed' => $this->canCustomerPurchase(),
            'customer_delivery_address' => is_array($customer) ? $this->buildCustomerAddressString($customer) : '',
            'customer_delivery_latitude' => is_array($customer) ? $this->getCustomerDeliveryLatitude($customer) : null,
            'customer_delivery_longitude' => is_array($customer) ? $this->getCustomerDeliveryLongitude($customer) : null,
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
                $order = null;
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
                    if ($result) {
                        $this->logOrderActivity(
                            'Accepted delivery assignment',
                            ActivityLogTypes::DELIVERY_ACCEPTED,
                            $orderId,
                            $order ?? null
                        );
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
                    if ($result) {
                        $this->logOrderActivity(
                            'Picked up order from store',
                            ActivityLogTypes::DELIVERY_PICKED_UP,
                            $orderId,
                            $order ?? null
                        );
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
                    if ($result) {
                        $this->logOrderActivity(
                            'Started delivery (out for delivery)',
                            ActivityLogTypes::DELIVERY_STARTED,
                            $orderId,
                            $order ?? null
                        );
                    }
                }
                return $this->response->setJSON([
                    'success' => (bool) $result,
                    'message' => $result ? 'Delivery started' : 'Unable to start delivery',
                ]);
            }

            if ($status === 'reschedule_delivery') {
                if ($currentStatus !== 'to_receive') {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Only out-for-delivery orders can be rescheduled.',
                    ]);
                }

                $rescheduleAtRaw = trim((string) $this->request->getPost('reschedule_at'));
                $rescheduleReason = trim((string) $this->request->getPost('reschedule_reason'));

                if ($rescheduleAtRaw === '') {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Please select a new delivery date.',
                    ]);
                }

                $scheduledAt = \DateTime::createFromFormat('Y-m-d', $rescheduleAtRaw)
                    ?: \DateTime::createFromFormat('Y-m-d\TH:i', $rescheduleAtRaw)
                    ?: \DateTime::createFromFormat('Y-m-d H:i', $rescheduleAtRaw);

                if (! $scheduledAt) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Invalid reschedule date format.',
                    ]);
                }

                $scheduledAt->setTime(0, 0, 0);
                $today = new \DateTime('today');
                if ($scheduledAt < $today) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Reschedule date cannot be in the past.',
                    ]);
                }

                $scheduledDate = $scheduledAt->format('Y-m-d');
                $scheduledLabel = $scheduledAt->format('F j, Y');
                $reasonText = $rescheduleReason;
                $existingNotes = trim((string) ($shipment['notes'] ?? ''));
                $rescheduleLine = 'RIDER_RESCHEDULED: ' . $reasonText . ' | Scheduled: ' . $scheduledDate;
                $updatedNotes = $existingNotes !== '' ? ($existingNotes . "\n" . $rescheduleLine) : $rescheduleLine;

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

                $result = $this->orderModel->updateDeliveryStatus($orderId, 'delivered_to_rider', $payload);
                if ($result) {
                    $order = $this->orderModel->getOrder($orderId);
                    if ($order) {
                        $reference = (string) ($order['reference_number'] ?? ('#' . $orderId));
                        $customerMessage = 'Delivery for your order ' . $reference . ' was rescheduled to ' . $scheduledLabel . '.';
                        if ($rescheduleReason !== '') {
                            $customerMessage .= ' Reason: ' . $rescheduleReason;
                        }

                        $this->notificationService->notifyUsers([(int) ($order['created_by'] ?? 0)], [
                            'category' => 'delivery',
                            'type' => 'delivery_rescheduled',
                            'title' => 'Delivery rescheduled',
                            'message' => $customerMessage,
                            'link' => site_url('customer/order-details/' . $orderId),
                            'related_type' => 'order',
                            'related_id' => $orderId,
                        ]);
                        $this->notificationService->notifyAdmins([
                            'category' => 'delivery',
                            'type' => 'delivery_rescheduled',
                            'title' => 'Delivery rescheduled',
                            'message' => 'Rider rescheduled order ' . $reference . ' to ' . $scheduledLabel . '.',
                            'link' => site_url('admin/order-details/' . $orderId),
                            'related_type' => 'order',
                            'related_id' => $orderId,
                        ]);
                    }
                    $this->logOrderActivity(
                        'Rescheduled delivery attempt',
                        ActivityLogTypes::DELIVERY_RESCHEDULED,
                        $orderId,
                        $order ?? null,
                        [
                            'scheduled_at' => $scheduledDate,
                            'reason' => $reasonText,
                        ]
                    );
                }

                return $this->response->setJSON([
                    'success' => (bool) $result,
                    'message' => $result
                        ? 'Delivery rescheduled to ' . $scheduledLabel . '. You can start delivery again when ready.'
                        : 'Unable to reschedule delivery',
                ]);
            }

            if ($status === 'customer_cancelled_at_delivery') {
                if ($currentStatus !== 'to_receive') {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Customer face-to-face cancellation is only allowed while the order is out for delivery.',
                    ]);
                }

                $order = $this->orderModel->getOrder($orderId);
                if (! $order) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Order not found']);
                }

                $cancelReason = trim((string) $this->request->getPost('cancel_reason'));
                $result = $this->performOrderCancellation($order, $riderId, 'rider_at_door', $cancelReason);

                return $this->response->setJSON($result);
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
                    if ($result) {
                        $this->logOrderActivity(
                            'Marked delivery as failed/cancelled',
                            ActivityLogTypes::DELIVERY_FAILED,
                            $orderId,
                            $order ?? null,
                            ['reason' => $cancelReason !== '' ? $cancelReason : 'No reason provided'],
                            'warning'
                        );
                    }
                }
                return $this->response->setJSON([
                    'success' => (bool) $result,
                    'message' => $result ? 'Delivery cancelled successfully' : 'Unable to cancel delivery',
                ]);
            }

            if ($status === 'accept_return_pickup') {
                if ($currentStatus !== 'return_approved') {
                    return $this->response->setJSON(['success' => false, 'message' => 'This return pickup is not ready for acceptance.']);
                }

                $order = $this->orderModel->getOrder($orderId);
                if (! $order) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Order not found']);
                }

                $returnMeta = parse_return_meta(
                    (string) ($order['shipment_notes'] ?? ''),
                    (string) ($order['delivery_notes'] ?? '')
                ) ?? [];

                if (rider_accepted_return_pickup($returnMeta)) {
                    return $this->response->setJSON(['success' => true, 'message' => 'Return pickup already accepted.']);
                }

                $returnMeta['rider_accepted_pickup_at'] = date('Y-m-d H:i:s');
                $returnMeta['rider_accepted_pickup_by'] = $riderId;

                $returnFields = merge_return_meta_shipment_fields(
                    (string) ($order['shipment_notes'] ?? ''),
                    (string) ($order['delivery_notes'] ?? ''),
                    $returnMeta
                );

                $result = $this->orderModel->updateDeliveryStatus($orderId, 'return_approved', $returnFields);

                if ($result) {
                    $reference = (string) ($order['reference_number'] ?? ('#' . $orderId));
                    $this->notificationService->notifyAdmins([
                        'category' => 'delivery',
                        'type' => 'return_pickup_accepted',
                        'title' => 'Rider accepted return pickup',
                        'message' => 'Rider accepted return pickup for order ' . $reference . '.',
                        'link' => site_url('admin/returns?status=return_approved&order=' . $orderId),
                        'related_type' => 'order',
                        'related_id' => $orderId,
                    ]);
                }

                if ($result) {
                    $this->logOrderActivity(
                        'Accepted return pickup assignment',
                        ActivityLogTypes::RETURN_PICKUP_ACCEPTED,
                        $orderId,
                        $order
                    );
                }

                return $this->response->setJSON([
                    'success' => (bool) $result,
                    'message' => $result ? 'Return pickup accepted. You can now scan the customer QR code.' : 'Unable to accept return pickup',
                ]);
            }

            if ($status === 'return_picked_up') {
                if ($currentStatus !== 'return_approved') {
                    return $this->response->setJSON(['success' => false, 'message' => 'Return pickup is not approved yet.']);
                }

                $order = $this->orderModel->getOrder($orderId);
                if (! $order) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Order not found']);
                }

                $returnMeta = parse_return_meta(
                    (string) ($order['shipment_notes'] ?? ''),
                    (string) ($order['delivery_notes'] ?? '')
                ) ?? [];

                if (! rider_accepted_return_pickup($returnMeta)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Accept this return pickup first before scanning the QR code.',
                    ]);
                }

                $scanText = trim((string) $this->request->getPost('return_token'));
                if ($scanText === '') {
                    $scanText = trim((string) $this->request->getPost('return_qr_scan'));
                }

                $parsedScan = parse_return_qr_scan($scanText);
                if ($parsedScan === null) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Invalid return QR code. Scan the customer return QR again.']);
                }

                if (isset($parsedScan['order_id']) && (int) $parsedScan['order_id'] !== $orderId) {
                    return $this->response->setJSON(['success' => false, 'message' => 'This QR code belongs to a different order.']);
                }

                $expectedToken = (string) ($returnMeta['return_token'] ?? '');
                if ($expectedToken === '' || ! hash_equals($expectedToken, (string) $parsedScan['token'])) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Return QR code does not match this order.']);
                }

                $requestType = (string) ($returnMeta['type'] ?? 'return_and_refund');
                if (return_refund_requires_payout($requestType) && trim((string) ($returnMeta['payout_account'] ?? '')) === '') {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Refund payout details are missing. The customer must provide GCash/e-wallet when submitting the return request.',
                    ]);
                }

                $returnMeta['qr_scanned_at'] = date('Y-m-d H:i:s');
                $returnMeta['qr_scanned_by'] = $riderId;
                $returnMeta['status'] = 'return_picked_up';
                $returnMeta = ensure_pending_refund_reference($returnMeta, $orderId);

                $returnFields = merge_return_meta_shipment_fields(
                    (string) ($order['shipment_notes'] ?? ''),
                    (string) ($order['delivery_notes'] ?? ''),
                    $returnMeta
                );

                $result = $this->orderModel->updateDeliveryStatus($orderId, 'return_picked_up', array_merge([
                    'picked_up_at' => date('Y-m-d H:i:s'),
                ], $returnFields));

                if ($result) {
                    $order = $this->orderModel->getOrder($orderId);
                    if ($order) {
                        $reference = (string) ($order['reference_number'] ?? ('#' . $orderId));
                        $this->notificationService->notifyAdmins([
                            'category' => 'orders',
                            'type' => 'return_picked_up',
                            'title' => 'Return item picked up',
                            'message' => 'Rider picked up returned items for order ' . $reference . '. Send refund via GCash/e-wallet.',
                            'link' => site_url('admin/returns?status=return_picked_up&order=' . $orderId),
                            'related_type' => 'order',
                            'related_id' => $orderId,
                        ]);
                        $customerId = (int) ($order['created_by'] ?? 0);
                        if ($customerId > 0) {
                            $this->notificationService->notifyUsers([$customerId], [
                                'category' => 'orders',
                                'type' => 'return_picked_up',
                                'title' => 'Return picked up',
                                'message' => 'Wait for the admin to send your refund via GCash or Maya. Order ' . $reference . '.',
                                'link' => site_url('customer/order-details/' . $orderId),
                                'related_type' => 'order',
                                'related_id' => $orderId,
                            ]);
                        }
                    }
                }

                if ($result) {
                    $this->logOrderActivity(
                        'Completed return pickup (QR scanned)',
                        ActivityLogTypes::RETURN_PICKUP_COMPLETED,
                        $orderId,
                        $order ?? null
                    );
                }

                return $this->response->setJSON([
                    'success' => (bool) $result,
                    'message' => $result ? 'Return pickup recorded successfully' : 'Unable to record return pickup',
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

        if (!$this->hasAdminPanelAccess()) {
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
        if (in_array($currentStatus, ['completed', 'cancelled', 'return_refund'], true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Completed/cancelled/refunded orders cannot be reassigned']);
        }
        if (is_return_refund_status($currentStatus)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Use Return/Refund actions for this order']);
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

        $riderName = (string) ($rider['name'] ?? 'Rider');
        $this->logOrderActivity(
            'Assigned rider ' . $riderName . ' to order',
            ActivityLogTypes::RIDER_ASSIGNED,
            $orderId,
            $order ?? null,
            ['rider_id' => $riderId, 'rider_name' => $riderName]
        );

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

        if (!$this->hasAdminPanelAccess()) {
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
            $this->logOrderActivity(
                'Handed order to rider for delivery',
                ActivityLogTypes::ORDER_HANDED_TO_RIDER,
                (int) $orderId,
                $order ?? null
            );
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

        if (!$this->hasAdminPanelAccess()) {
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

                $this->logOrderActivity(
                    'Submitted delivery proof and completed delivery',
                    ActivityLogTypes::DELIVERY_COMPLETED,
                    $orderId,
                    $order ?? null,
                    ['proof_image' => $filename]
                );
                
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

        if (!$this->hasAdminPanelAccess()) {
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
                    'notes' => delivery_notes_for_display((string) ($proof['delivery_notes'] ?? '')),
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
     * Serve return/refund evidence (photo or video).
     */
    public function serveReturnEvidence(?string $filename = null)
    {
        if ($filename === null || $filename === '') {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'File not found']);
        }

        if (! preg_match('/^return_evidence_\d+_\d+_[a-f0-9]+\.(jpg|jpeg|png|gif|webp|mp4|webm|mov|m4v)$/i', $filename)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid filename']);
        }

        $filePath = WRITEPATH . 'uploads/return_evidence/' . $filename;
        if (! is_file($filePath)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'File not found']);
        }

        $fileInfo = new \CodeIgniter\Files\File($filePath);

        return $this->response
            ->setHeader('Content-Type', $fileInfo->getMimeType())
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
        $hasFlavorChoices = $this->usesFlavorSelection((string) ($product['category'] ?? '')) && $this->hasSelectableVariants($variants);
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

        $productName = (string) ($product['name'] ?? 'product');
        $this->logUserActivity(
            "Added {$productName} to cart (qty {$quantity})",
            ActivityLogTypes::CART_ADD,
            [
                'product_id' => $productId,
                'product_name' => $productName,
                'variant_id' => $selectedVariant ? (int) $selectedVariant['id'] : null,
                'quantity' => $quantity,
                'cart_count' => $cartCount,
            ]
        );

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

        $productName = (string) ($cartProduct['name'] ?? 'product');
        $this->logUserActivity(
            $quantity <= 0
                ? "Removed {$productName} from cart"
                : "Updated {$productName} cart quantity to {$quantity}",
            $quantity <= 0 ? ActivityLogTypes::CART_REMOVE : ActivityLogTypes::CART_UPDATE,
            [
                'product_id' => $productId,
                'product_name' => $productName,
                'variant_id' => $variantId > 0 ? $variantId : null,
                'quantity' => max(0, $quantity),
            ]
        );

        if ($this->request->isAJAX()) {
            $cart = $this->getCustomerCart();
            $lineAmount = 0.0;
            foreach ($cart['items'] as $item) {
                if ((string) ($item['cart_key'] ?? '') === $cartKey) {
                    $lineAmount = (float) ($item['amount'] ?? 0);
                    break;
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'removed' => $quantity <= 0,
                'cart_key' => $cartKey,
                'line_amount' => $lineAmount,
                'cart_total' => $cart['total'],
                'empty' => $cart['items'] === [],
                'csrf_hash' => csrf_hash(),
            ]);
        }

        return redirect()->back()->with('success', 'Cart updated successfully.');
    }

    /**
     * Customer: Update all cart line quantities from the cart page form.
     */
    public function customerCartUpdateAll()
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $quantities = $this->request->getPost('quantities');
        if (! is_array($quantities) || $quantities === []) {
            return redirect()->to('/customer/cart')->with('error', 'No cart items to update.');
        }

        $items = $this->getCustomerCart()['raw_items'];
        $updated = false;

        foreach ($quantities as $cartKey => $quantity) {
            $cartKey = (string) $cartKey;
            if (! array_key_exists($cartKey, $items)) {
                continue;
            }

            $quantity = (int) $quantity;
            $cartProduct = $this->resolveCartProduct($cartKey);
            if (! $cartProduct) {
                unset($items[$cartKey]);
                $updated = true;
                continue;
            }

            if ($quantity <= 0) {
                unset($items[$cartKey]);
                $updated = true;
                continue;
            }

            $maxStock = (int) ($cartProduct['stock'] ?? 0);
            $items[$cartKey] = min($quantity, max(1, $maxStock));
            $updated = true;
        }

        if (! $updated) {
            return redirect()->to('/customer/cart')->with('error', 'No valid cart items to update.');
        }

        $this->setCustomerCartRawItems($items);
        $this->logUserActivity('Updated shopping cart quantities', ActivityLogTypes::CART_UPDATE);

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

        $cartProduct = $this->resolveCartProduct($cartKey);
        $productName = is_array($cartProduct) ? (string) ($cartProduct['name'] ?? 'product') : 'product';
        $this->logUserActivity(
            "Removed {$productName} from cart",
            ActivityLogTypes::CART_REMOVE,
            ['product_id' => $productId, 'product_name' => $productName]
        );

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

        $placedRef = is_array($order)
            ? (string) ($order['reference_number'] ?? $referenceNumber)
            : $referenceNumber;
        $this->logUserActivity(
            'Placed direct order ' . $placedRef,
            ActivityLogTypes::ORDER_PLACED,
            [
                'order_id' => (int) $orderId,
                'reference_number' => $placedRef,
                'total_amount' => round($total, 2),
                'item_count' => count($cartItems),
                'source' => 'web_direct',
            ]
        );

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
            'customer_delivery_latitude' => is_array($customer) ? $this->getCustomerDeliveryLatitude($customer) : null,
            'customer_delivery_longitude' => is_array($customer) ? $this->getCustomerDeliveryLongitude($customer) : null,
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
            return redirect()->back()->with('error', 'Please select a valid payment method.');
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
            return redirect()->back()->with('error', 'Customer account not found.');
        }

        $deliveryData = $this->getCheckoutDeliveryData($customer);
        if ($deliveryData === null) {
            return redirect()->to('/customer/products')->with('error', 'Please enter a delivery address or use your saved address.');
        }
        if (!isset($deliveryData['delivery_latitude'], $deliveryData['delivery_longitude'])) {
            return redirect()->back()->with('error', 'Please confirm your exact delivery location on the map.');
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

            $orderRef = $order ? (string) ($order['reference_number'] ?? ('#' . $orderId)) : $referenceNumber;
            $this->logUserActivity(
                'Placed order ' . $orderRef . ' via ' . ($paymentMethod === 'gcash' ? 'GCash' : 'COD'),
                ActivityLogTypes::ORDER_PLACED,
                [
                    'order_id' => (int) $orderId,
                    'reference_number' => $orderRef,
                    'total_amount' => round($total, 2),
                    'payment_method' => $paymentMethod,
                    'item_count' => count($cartItems),
                    'source' => 'web_checkout',
                ]
            );

            return redirect()->to(site_url('customer/orders?tab=' . $redirectTab))
                ->with('success', $successMessage);
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->with('error', $e->getMessage());
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

        $this->logUserActivity(
            'Completed age verification (18+)',
            ActivityLogTypes::AGE_VERIFIED,
            ['age' => $age]
        );

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
                'unit_price' => (float) ($product['unit_price'] ?? 0),
                'selling_price' => (float) ($product['selling_price'] ?? $product['price'] ?? 0),
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
            $sellingPrice = (float) ($item['selling_price'] ?? $item['price'] ?? 0);
            $costPrice = (float) ($item['unit_price'] ?? $item['cost_price'] ?? 0);
            if ($costPrice <= 0 && $sellingPrice > 0) {
                $costPrice = round(max(0.0, $sellingPrice - 50.0), 2);
            }

            return [
                'id' => (int) ($item['id'] ?? 0),
                'variant_id' => isset($item['variant_id']) ? (int) $item['variant_id'] : null,
                'qty' => (int) ($item['quantity'] ?? $item['qty'] ?? 0),
                'name' => (string) ($item['display_name'] ?? $item['name'] ?? ('Product #' . (string) ($item['id'] ?? ''))),
                'unit_price' => $costPrice,
                'selling_price' => $sellingPrice,
                'price' => $sellingPrice,
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
            'unit_price' => (float) ($product['unit_price'] ?? 0) > 0
                ? (float) $product['unit_price']
                : round(max(0.0, (float) ($product['selling_price'] ?? $product['price'] ?? 0) - 50.0), 2),
            'selling_price' => (float) ($product['selling_price'] ?? $product['price'] ?? 0),
            'price' => (float) ($product['selling_price'] ?? $product['price'] ?? 0),
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
            $puffs = (int) ($variant['puffs'] ?? 0);
            $resolved['variant_id'] = (int) $variant['id'];
            $resolved['flavor'] = $flavor;
            $labelParts = [];
            if ($flavor !== '') {
                $labelParts[] = $flavor;
            }
            if ($puffs > 0) {
                $labelParts[] = number_format($puffs) . ' puffs';
            }
            $resolved['display_name'] = $labelParts !== []
                ? $resolved['name'] . ' - ' . implode(' / ', $labelParts)
                : $resolved['name'];
            $resolved['selling_price'] = (float) ($variant['price'] ?? $resolved['selling_price']);
            $resolved['price'] = $resolved['selling_price'];
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

    private function hasPuffVariants(array $variants): bool
    {
        foreach ($variants as $variant) {
            if ((int) ($variant['puffs'] ?? 0) > 0) {
                return true;
            }
        }

        return false;
    }

    private function hasSelectableVariants(array $variants): bool
    {
        return $this->hasNamedVariants($variants) || $this->hasPuffVariants($variants);
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

    private function getCustomerDeliveryLatitude(array $customer): ?float
    {
        $lat = $customer['delivery_latitude'] ?? null;

        return is_numeric($lat) ? (float) $lat : null;
    }

    private function getCustomerDeliveryLongitude(array $customer): ?float
    {
        $lng = $customer['delivery_longitude'] ?? null;

        return is_numeric($lng) ? (float) $lng : null;
    }

    /**
     * @return array{delivery_latitude: float, delivery_longitude: float}|null
     */
    private function getCustomerSavedDeliveryCoordinates(?array $customer): ?array
    {
        if ($customer === null) {
            return null;
        }

        $lat = $this->getCustomerDeliveryLatitude($customer);
        $lng = $this->getCustomerDeliveryLongitude($customer);
        if ($lat === null || $lng === null) {
            return null;
        }

        return [
            'delivery_latitude' => $lat,
            'delivery_longitude' => $lng,
        ];
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
            'phone' => $this->normalizeContactNumber((string) ($customer['phone_number'] ?? '')),
            'address' => $address !== '' ? $address : (string) ($customer['address'] ?? ''),
        ];
    }

    private function normalizeContactNumber(string $contactNumber): string
    {
        $contactNumber = trim(preg_replace('/\s+/', ' ', $contactNumber) ?? '');
        if ($contactNumber === '') {
            return '';
        }

        return preg_match('/^[0-9+\-\s\(\)]{7,20}$/', $contactNumber) === 1 ? $contactNumber : '';
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

            $phoneNumber = $this->normalizeContactNumber((string) ($customer['phone_number'] ?? ''));
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

            $savedCoordinates = $this->getCustomerSavedDeliveryCoordinates($customer);
            if ($lat === null || $lng === null) {
                $lat = $savedCoordinates['delivery_latitude'] ?? null;
                $lng = $savedCoordinates['delivery_longitude'] ?? null;
            }

            if ($lat === null || $lng === null) {
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

    /**
     * Canonical store pickup location used across delivery/return maps.
     *
     * @return array{lat: float, lng: float, address: string}
     */
    private function getCanonicalStoreLocation(): array
    {
        $store = $this->shopSettingsModel->getStoreLocation();

        return [
            'lat' => $store['lat'],
            'lng' => $store['lng'],
            'address' => $store['address'],
            'name' => $store['name'],
            'phone' => $store['phone'],
        ];
    }

    /**
     * Force store map coordinates/address to canonical pickup location.
     *
     * @param array<string, mixed> $order
     * @return array<string, mixed>
     */
    private function applyCanonicalStoreLocationToOrder(array $order): array
    {
        $store = $this->getCanonicalStoreLocation();
        $order['store_latitude'] = $store['lat'];
        $order['store_longitude'] = $store['lng'];
        $order['store_address'] = $store['address'];

        return $order;
    }

    private function getStoreShipmentData(): array
    {
        $store = $this->getCanonicalStoreLocation();

        return [
            'store_latitude' => $store['lat'],
            'store_longitude' => $store['lng'],
            'store_address' => $store['address'],
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
        $order = $this->applyCanonicalStoreLocationToOrder($order);

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

        if (!$this->hasAdminPanelAccess()) {
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
                'contact_number' => $this->normalizeContactNumber((string) ($order['contact_number'] ?? '')) !== ''
                    ? $this->normalizeContactNumber((string) ($order['contact_number'] ?? ''))
                    : (($customerInfo['phone'] ?? '') !== '' ? (string) $customerInfo['phone'] : 'Not provided'),
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
            'return_status_counts' => $this->orderModel->getReturnRefundStatusCounts(),
        ];

        return view('admin/orders/index', $data);
    }

    /**
     * Admin return/refund management page.
     */
    public function adminReturnRefunds()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $sessionCheck = $this->checkSessionTimeout();
        if ($sessionCheck !== true) {
            return $sessionCheck;
        }

        if (!$this->hasAdminPanelAccess()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Admin privileges required.');
        }

        helper('return_refund');

        $statusFilter = (string) ($this->request->getGet('status') ?? 'all');
        $highlightOrderId = (int) ($this->request->getGet('order') ?? 0);

        $orders = $this->orderModel->getReturnRefundOrders(
            $statusFilter === 'all' ? null : $statusFilter
        );

        $returnOrders = [];
        foreach ($orders as $order) {
            $customerInfo = $this->getOrderCustomerInfo(isset($order['created_by']) ? (int) $order['created_by'] : null);
            $assignedRiderId = (int) ($order['assigned_rider_id'] ?? 0);
            $assignedRider = $assignedRiderId > 0 ? $this->userModel->find($assignedRiderId) : null;
            $deliveryStatus = (string) ($order['delivery_status'] ?? '');
            $orderId = (int) ($order['id'] ?? 0);
            $returnMeta = parse_return_meta(
                (string) ($order['shipment_notes'] ?? ''),
                (string) ($order['delivery_notes'] ?? '')
            );

            if ($deliveryStatus === 'return_picked_up' && is_array($returnMeta)) {
                $previousPendingRef = trim((string) ($returnMeta['pending_refund_reference'] ?? ''));
                $returnMeta = ensure_pending_refund_reference($returnMeta, $orderId);
                if ($previousPendingRef === '' && $orderId > 0) {
                    $returnFields = merge_return_meta_shipment_fields(
                        (string) ($order['shipment_notes'] ?? ''),
                        (string) ($order['delivery_notes'] ?? ''),
                        $returnMeta
                    );
                    $this->orderModel->updateOrder($orderId, [], [], $returnFields);
                }
            }

            $returnOrders[] = [
                'id' => $order['id'],
                'reference_number' => $order['reference_number'],
                'date' => $order['date'],
                'total_amount' => $order['total_amount'],
                'payment_method' => $order['payment_method'] ?? '',
                'payment_status' => $order['payment_status'] ?? '',
                'delivery_status' => $deliveryStatus,
                'shipping_address' => $order['shipping_address'] ?: ($customerInfo['address'] ?? 'Not provided'),
                'contact_number' => $this->normalizeContactNumber((string) ($order['contact_number'] ?? '')) !== ''
                    ? $this->normalizeContactNumber((string) ($order['contact_number'] ?? ''))
                    : (($customerInfo['phone'] ?? '') !== '' ? (string) $customerInfo['phone'] : 'Not provided'),
                'items' => $order['items'] ?? [],
                'customer' => $customerInfo,
                'assigned_rider_id' => $assignedRiderId,
                'assigned_rider_name' => $assignedRider['name'] ?? null,
                'return_meta' => $returnMeta,
            ];
        }

        $riders = array_values(array_filter(
            $this->userModel->findAll(),
            static fn (array $user): bool => (string) ($user['role'] ?? '') === 'rider'
        ));

        return view('admin/returns/index', [
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $this->session->get('user_role'),
            'page_title' => 'Return / Refund',
            'pageHeaderTitle' => 'Return / Refund',
            'pageHeaderSubtitle' => 'Review customer return requests, assign riders for pickup, and complete refunds.',
            'return_orders' => $returnOrders,
            'status_counts' => $this->orderModel->getReturnRefundStatusCounts(),
            'current_status' => $statusFilter,
            'highlight_order_id' => $highlightOrderId,
            'riders' => $riders,
        ]);
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

        if (!$this->hasAdminPanelAccess()) {
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

        if (!$this->hasAdminPanelAccess()) {
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

            $this->logOrderActivity(
                'Processed order payment (' . $paymentMethod . ')',
                ActivityLogTypes::ADMIN_ORDER_PROCESSED,
                (int) $orderId,
                $order,
                [
                    'payment_method' => $paymentMethod,
                    'amount_received' => round($amountReceived, 2),
                    'total_amount' => round((float) $order['total_amount'], 2),
                ]
            );

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

        if (in_array($userRole, ['customer', 'rider'], true)) {
            // Customers and riders manage account details through profile page.
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

        if (!$this->hasAdminPanelAccess()) {
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

        if (!$this->hasAdminPanelAccess()) {
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
                $customerPhone = $this->normalizeContactNumber((string) ($customer['phone_number'] ?? ''));
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

        if (!$this->hasAdminPanelAccess()) {
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

        if (!$this->hasAdminPanelAccess()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Admin privileges required.');
        }

        $order = $this->orderModel->getOrder((int) $orderId);

        if (! $order) {
            return redirect()->to('/orders')->with('error', 'Order not found.');
        }

        $this->repairReturnMetaStorage($order);
        $order = $this->orderModel->getOrder((int) $orderId) ?? $order;
        $order = $this->applyCanonicalStoreLocationToOrder($order);
        if (strtolower(trim((string) ($order['status'] ?? ''))) === 'cancelled') {
            $order['delivery_status'] = 'cancelled';
        }

        $normalizedPayment = $this->normalizeOrderPayment($order);
        $order['payment_method'] = $normalizedPayment['method'];
        $order['payment_status'] = $normalizedPayment['status'];

        $riders = array_values(array_filter(
            $this->userModel->findAll(),
            static fn (array $user): bool => (string) ($user['role'] ?? '') === 'rider'
        ));

        $data = [
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $this->session->get('user_role'),
            'user_shop_name' => $this->session->get('user_shop_name'),
            'page_title' => 'Order Details - Admin',
            'order' => $order,
            'items' => $order['items'] ?? [],
            'riders' => $riders,
            'return_meta' => parse_return_meta(
                (string) ($order['shipment_notes'] ?? ''),
                (string) ($order['delivery_notes'] ?? '')
            ),
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
        $order = $this->applyCanonicalStoreLocationToOrder($order);
        if (strtolower(trim((string) ($order['status'] ?? ''))) === 'cancelled') {
            $order['delivery_status'] = 'cancelled';
        }

        $riderId = (int) $this->session->get('user_id');
        $assignedRiderId = (int) ($order['assigned_rider_id'] ?? 0);
        if ($assignedRiderId !== $riderId) {
            return redirect()->to('/rider/deliveries')->with('error', 'Access denied for this order.');
        }
        $order['customer'] = $this->getOrderCustomerInfo(isset($order['created_by']) ? (int) $order['created_by'] : null);

        $deliveryStatus = (string) ($order['delivery_status'] ?? '');
        $isReturnPickup = function_exists('is_return_refund_status') && is_return_refund_status($deliveryStatus);

        return view('rider/order_details', $this->getRiderPageData('Order Details', $isReturnPickup ? 'returns' : 'deliveries', [
            'order' => $order,
            'items' => $order['items'] ?? [],
            'return_meta' => parse_return_meta(
                (string) ($order['shipment_notes'] ?? ''),
                (string) ($order['delivery_notes'] ?? '')
            ),
            'is_return_pickup' => $isReturnPickup,
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
        $order = $this->applyCanonicalStoreLocationToOrder($order);

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
                'contact_number' => $this->normalizeContactNumber((string) ($order['contact_number'] ?? '')) !== ''
                    ? $this->normalizeContactNumber((string) ($order['contact_number'] ?? ''))
                    : (($customerInfo['phone'] ?? '') !== '' ? (string) $customerInfo['phone'] : 'Not provided'),
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
     * Download return pickup QR as PNG (customer and admin only).
     */
    public function downloadReturnQrPng($orderId)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $id = (int) $orderId;
        if ($id <= 0) {
            return $this->response->setStatusCode(404)->setBody('Order not found');
        }

        $order = $this->orderModel->getOrder($id);
        if (! $order) {
            return $this->response->setStatusCode(404)->setBody('Order not found');
        }

        $role = (string) $this->session->get('user_role');
        $userId = (int) $this->session->get('user_id');

        if ($role === 'rider') {
            return $this->response->setStatusCode(403)->setBody('QR download is available to the customer only');
        }

        if ($role === 'customer') {
            if ((int) ($order['created_by'] ?? 0) !== $userId) {
                return $this->response->setStatusCode(403)->setBody('Access denied');
            }
        } elseif ($role !== 'admin') {
            return $this->response->setStatusCode(403)->setBody('Access denied');
        }

        $returnMeta = parse_return_meta(
            (string) ($order['shipment_notes'] ?? ''),
            (string) ($order['delivery_notes'] ?? '')
        );
        if ($returnMeta === null) {
            return $this->response->setStatusCode(404)->setBody('No return request for this order');
        }

        $orderReference = (string) ($order['reference_number'] ?? ('#' . $id));
        $qrPayload = return_refund_resolve_qr_payload($returnMeta, $id, $orderReference);
        if ($qrPayload === '') {
            return $this->response->setStatusCode(404)->setBody('QR code not available');
        }

        $imageUrl = return_qr_image_url($qrPayload, 512);
        $png = @file_get_contents($imageUrl);
        if ($png === false || $png === '') {
            try {
                $client = \Config\Services::curlrequest(['timeout' => 15]);
                $remote = $client->get($imageUrl);
                $png = (string) $remote->getBody();
            } catch (\Throwable $e) {
                log_message('error', 'Return QR download failed: ' . $e->getMessage());
                return $this->response->setStatusCode(502)->setBody('Unable to generate QR image');
            }
        }

        if ($png === '') {
            return $this->response->setStatusCode(502)->setBody('Unable to generate QR image');
        }

        $filename = return_qr_download_filename($id, $orderReference);

        return $this->response
            ->setHeader('Content-Type', 'image/png')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Content-Length', (string) strlen($png))
            ->setBody($png);
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

        if ($userRole === 'rider') {
            $riderAccount = $this->userModel->find((int) $this->session->get('user_id'));
            return view('rider/profile', $this->getRiderPageData('Profile', 'profile', [
                'rider_account' => $riderAccount,
            ]));
        }

        $adminId = (int) $this->session->get('user_id');
        $adminAccount = $this->userModel->find($adminId);
        $shopSettings = $this->shopSettingsModel->getSettings();

        $data = [
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $userRole,
            'user_shop_name' => $this->session->get('user_shop_name'),
            'page_title' => 'Profile',
            'admin_account' => $adminAccount,
            'shop_settings' => $shopSettings,
        ];

        return view('admin/dashboard/profile', $data);
    }

    /**
     * Update customer/rider profile details from profile settings.
     */
    public function updateCustomerProfile()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $sessionCheck = $this->checkSessionTimeout();
        if ($sessionCheck !== true) {
            return $sessionCheck;
        }

        $userRole = (string) $this->session->get('user_role');
        if ($userRole === 'admin') {
            return $this->updateAdminProfile();
        }

        if (!in_array($userRole, ['customer', 'rider'], true)) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $userId = (int) $this->session->get('user_id');
        $account = $this->userModel->find($userId);

        if (!$account || !in_array((string) ($account['role'] ?? ''), ['customer', 'rider'], true)) {
            return redirect()->to('/dashboard/profile')->with('error', 'Account not found.');
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
            'name' => 'required|min_length[3]|max_length[255]|safe_person_name',
            'email' => 'required|valid_email|is_unique[users.email,id,' . $userId . ']',
            'phone_number' => 'permit_empty|max_length[30]|regex_match[/^[0-9+\-\s\(\)]+$/]',
            'address_line' => 'permit_empty|max_length[255]|safe_address',
            'city' => 'permit_empty|max_length[120]|safe_location',
            'country' => 'permit_empty|max_length[120]|safe_location',
            'barangay' => 'permit_empty|max_length[120]|safe_location',
            'province' => 'permit_empty|max_length[120]|safe_location',
            'postal_code' => 'permit_empty|max_length[20]|safe_postal_code',
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

        $this->activityLogger->logProfileUpdate($userId, $email, [
            'name' => $name,
            'email' => $email,
            'phone_number' => $phoneNumber,
        ]);
        if ($newPassword !== '') {
            $this->activityLogger->logPasswordChange($userId, $email);
        }

        return redirect()->to('/dashboard/profile')->with('success', 'Profile updated successfully.');
    }

    /**
     * Update admin profile and canonical shop pickup location.
     */
    private function updateAdminProfile()
    {
        if (! $this->hasAdminPanelAccess()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $userId = (int) $this->session->get('user_id');
        $account = $this->userModel->find($userId);
        if (! $account || (string) ($account['role'] ?? '') !== 'admin') {
            return redirect()->to('/dashboard/profile')->with('error', 'Account not found.');
        }

        $name = trim((string) $this->request->getPost('name'));
        $email = trim((string) $this->request->getPost('email'));
        $phoneNumber = trim((string) $this->request->getPost('phone_number'));
        $shopName = trim((string) $this->request->getPost('shop_name'));
        $shopAddress = trim((string) $this->request->getPost('shop_address'));
        $shopPhone = trim((string) $this->request->getPost('shop_phone'));
        $shopLatitude = $this->parseCoordinate($this->request->getPost('shop_latitude'));
        $shopLongitude = $this->parseCoordinate($this->request->getPost('shop_longitude'));
        $newPassword = (string) $this->request->getPost('new_password');
        $confirmPassword = (string) $this->request->getPost('confirm_password');

        $input = [
            'name' => $name,
            'email' => $email,
            'phone_number' => $phoneNumber,
            'shop_name' => $shopName,
            'shop_address' => $shopAddress,
            'shop_phone' => $shopPhone,
            'shop_latitude' => $this->request->getPost('shop_latitude'),
            'shop_longitude' => $this->request->getPost('shop_longitude'),
            'new_password' => $newPassword,
            'confirm_password' => $confirmPassword,
        ];

        $rules = [
            'name' => 'required|min_length[3]|max_length[255]|safe_person_name',
            'email' => 'required|valid_email|is_unique[users.email,id,' . $userId . ']',
            'phone_number' => 'permit_empty|max_length[30]|regex_match[/^[0-9+\-\s\(\)]+$/]',
            'shop_name' => 'required|min_length[2]|max_length[150]|safe_text',
            'shop_address' => 'required|min_length[5]|max_length[500]|safe_address',
            'shop_phone' => 'permit_empty|max_length[30]|regex_match[/^[0-9+\-\s\(\)]+$/]',
            'shop_latitude' => 'required|decimal',
            'shop_longitude' => 'required|decimal',
            'new_password' => 'permit_empty|min_length[8]',
            'confirm_password' => 'permit_empty|matches[new_password]',
        ];

        $messages = [
            'shop_latitude' => [
                'required' => 'Please pin your shop location on the map.',
            ],
            'shop_longitude' => [
                'required' => 'Please pin your shop location on the map.',
            ],
            'confirm_password' => [
                'matches' => 'Confirm password does not match the new password.',
            ],
        ];

        if (! $this->validateData($input, $rules, $messages)) {
            return redirect()->to('/dashboard/profile')
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        if ($shopLatitude === null || $shopLongitude === null) {
            return redirect()->to('/dashboard/profile')
                ->withInput()
                ->with('error', 'Please pin your shop location on the map.');
        }

        $userUpdate = [
            'name' => $name,
            'email' => $email,
            'shop_name' => $shopName,
            'phone_number' => $phoneNumber,
            'role' => 'admin',
        ];
        if ($newPassword !== '') {
            $userUpdate['password'] = $newPassword;
        }

        if (! $this->userModel->update($userId, $userUpdate)) {
            return redirect()->to('/dashboard/profile')
                ->withInput()
                ->with('error', 'Failed to update profile. Please try again.');
        }

        if (! $this->shopSettingsModel->saveSettings([
            'shop_name' => $shopName,
            'shop_address' => $shopAddress,
            'shop_latitude' => $shopLatitude,
            'shop_longitude' => $shopLongitude,
            'shop_phone' => $shopPhone !== '' ? $shopPhone : $phoneNumber,
        ], $userId)) {
            return redirect()->to('/dashboard/profile')
                ->withInput()
                ->with('error', 'Profile saved but shop location settings failed to update.');
        }

        $this->session->set([
            'user_name' => $name,
            'user_email' => $email,
            'user_shop_name' => $shopName,
            'user_role' => 'admin',
        ]);
        $this->session->remove('admin_access_repaired');
        (new \App\Libraries\RbacService())->repairAdminAccess();

        $this->activityLogger->logProfileUpdate($userId, $email, [
            'name' => $name,
            'email' => $email,
            'shop_name' => $shopName,
            'shop_address' => $shopAddress,
            'shop_latitude' => $shopLatitude,
            'shop_longitude' => $shopLongitude,
        ]);
        if ($newPassword !== '') {
            $this->activityLogger->logPasswordChange($userId, $email);
        }

        return redirect()->to('/dashboard/profile')->with('success', 'Profile and shop location updated successfully.');
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

        $this->logUserActivity(
            'Paid for order ' . $reference,
            ActivityLogTypes::ORDER_PAID,
            ['order_id' => $orderId, 'reference_number' => $reference, 'amount' => round((float) ($order['total_amount'] ?? 0), 2)]
        );

        return redirect()->to('/customer/orders')->with('success', 'Payment processed successfully. Order is now ready for shipping.');
    }

    /**
     * @return array{success: bool, message: string}
     */
    private function performOrderCancellation(
        array $order,
        int $actorUserId,
        string $source,
        ?string $reason = null
    ): array {
        if ($source === 'customer' && ! customer_can_cancel_order($order)) {
            return ['success' => false, 'message' => customer_cancel_unavailable_message($order)];
        }

        $orderId = (int) ($order['id'] ?? 0);
        if ($orderId <= 0) {
            return ['success' => false, 'message' => 'Invalid order.'];
        }

        $previousStatus = strtolower(trim((string) ($order['delivery_status'] ?? '')));
        if ($previousStatus === 'cancelled') {
            return ['success' => false, 'message' => 'This order is already cancelled.'];
        }

        if ($source === 'rider_at_door' && $previousStatus !== 'to_receive') {
            return [
                'success' => false,
                'message' => 'Face-to-face customer cancellation is only allowed while the order is out for delivery.',
            ];
        }

        if ($source === 'rider_at_door' && (int) ($order['assigned_rider_id'] ?? 0) !== $actorUserId) {
            return ['success' => false, 'message' => 'You are not assigned to this order.'];
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $shouldRestoreStock = $source === 'rider_at_door' || customer_cancel_requires_stock_restore($order);
            if ($shouldRestoreStock) {
                if (! $this->productModel->restoreStockForItems(
                    (array) ($order['items'] ?? []),
                    'order',
                    $orderId,
                    $actorUserId
                )) {
                    throw new \RuntimeException('Failed to restore stock for a cancelled order.');
                }
            }

            $shipmentData = ['status' => 'cancelled'];
            $reasonText = trim((string) $reason);
            if ($source === 'rider_at_door') {
                if ($reasonText === '') {
                    $reasonText = 'Customer cancelled/refused the order at delivery location.';
                }
                $existingNotes = trim((string) ($order['shipment_notes'] ?? ''));
                $noteLine = 'CUSTOMER_CANCELLED_AT_DOOR: ' . $reasonText . ' (' . date('Y-m-d H:i:s') . ')';
                $shipmentData['notes'] = $existingNotes !== '' ? ($existingNotes . "\n" . $noteLine) : $noteLine;
            }

            $updated = $this->orderModel->updateOrder(
                $orderId,
                ['status' => 'cancelled'],
                [],
                $shipmentData
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

            return ['success' => false, 'message' => $e->getMessage()];
        }

        $this->syncOrderToRecord($orderId);
        $reference = (string) ($order['reference_number'] ?? ('#' . $orderId));
        $customerId = (int) ($order['created_by'] ?? 0);

        if ($source === 'rider_at_door') {
            $this->notificationService->notifyAdmins([
                'category' => 'cancellations',
                'type' => 'order_cancelled',
                'title' => 'Customer cancelled at delivery',
                'message' => 'Rider reported that the customer cancelled order ' . $reference . ' face-to-face.',
                'link' => site_url('admin/order-details/' . $orderId),
                'related_type' => 'order',
                'related_id' => $orderId,
            ]);

            if ($customerId > 0) {
                $this->notificationService->notifyUsers([$customerId], [
                    'category' => 'cancellations',
                    'type' => 'order_cancelled',
                    'title' => 'Order cancelled',
                    'message' => 'Your order ' . $reference . ' was cancelled at delivery.',
                    'link' => site_url('customer/orders?tab=cancelled'),
                    'related_type' => 'order',
                    'related_id' => $orderId,
                ]);
            }

            $this->activityLogger->logUserAction(
                $actorUserId,
                'Recorded face-to-face customer cancellation for order ' . $reference,
                ActivityLogTypes::ORDER_CANCELLED_AT_DOOR,
                [
                    'order_id' => $orderId,
                    'reference_number' => $reference,
                    'previous_status' => $previousStatus,
                    'reason' => $reasonText,
                    'customer_id' => $customerId,
                ],
                'warning'
            );

            return [
                'success' => true,
                'message' => 'Order cancelled. Customer refusal at delivery location was recorded.',
            ];
        }

        $this->notificationService->notifyAdmins([
            'category' => 'cancellations',
            'type' => 'order_cancelled',
            'title' => 'Order cancelled',
            'message' => 'Customer cancelled order ' . $reference . '.',
            'link' => site_url('admin/order-details/' . $orderId),
            'related_type' => 'order',
            'related_id' => $orderId,
        ]);

        $riderId = (int) ($order['assigned_rider_id'] ?? 0);
        if ($riderId > 0) {
            $this->notificationService->notifyUsers([$riderId], [
                'category' => 'cancellations',
                'type' => 'order_cancelled',
                'title' => 'Order cancelled by customer',
                'message' => 'Order ' . $reference . ' was cancelled before delivery.',
                'link' => site_url('rider/deliveries'),
                'related_type' => 'order',
                'related_id' => $orderId,
            ]);
        }

        $this->logUserActivity(
            'Cancelled order ' . $reference,
            ActivityLogTypes::ORDER_CANCELLED,
            [
                'order_id' => $orderId,
                'reference_number' => $reference,
                'previous_status' => $previousStatus,
            ]
        );

        return ['success' => true, 'message' => 'Order cancelled successfully.'];
    }

    private function cancelOrder($order)
    {
        $result = $this->performOrderCancellation(
            $order,
            (int) $this->session->get('user_id'),
            'customer',
            null
        );

        if (! $result['success']) {
            return redirect()->to('/customer/orders')->with('error', $result['message']);
        }

        return redirect()->to('/customer/orders?tab=cancelled')->with('success', $result['message']);
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

        $this->logUserActivity(
            'Confirmed receipt of order ' . $reference,
            ActivityLogTypes::ORDER_COMPLETED,
            ['order_id' => $orderId, 'reference_number' => $reference]
        );

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
            if ($deliveryStatus === 'return_refund') {
                $recordStatus = 'return_refund';
            } elseif (in_array($deliveryStatus, ['cancelled', 'failed_delivery'], true)) {
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
            } elseif ($recordStatus === 'return_refund') {
                $paymentStatus = 'unpaid';
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

        $reference = (string) ($order['reference_number'] ?? ('#' . ($order['id'] ?? 0)));
        $this->logUserActivity(
            'Reordered items from order ' . $reference,
            ActivityLogTypes::ORDER_REORDER,
            [
                'order_id' => (int) ($order['id'] ?? 0),
                'reference_number' => $reference,
                'item_count' => count($cartItems),
            ]
        );

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

        $this->logUserActivity(
            'Submitted product review (' . $rating . ' stars)',
            ActivityLogTypes::REVIEW_SUBMITTED,
            [
                'order_id' => $orderId,
                'product_id' => $productId,
                'rating' => $rating,
                'review_id' => $reviewId,
            ]
        );

        return redirect()->to('/customer/orders?tab=to_review')->with('success', 'Your product review has been posted.');
    }

    public function getProductReviews($productId = null)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        if (!$this->hasAdminPanelAccess()) {
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

        if (!$this->hasAdminPanelAccess()) {
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

            $this->logUserActivity(
                ($status === 'approved' ? 'Approved' : 'Rejected') . ' product review #' . $reviewId,
                $status === 'approved' ? ActivityLogTypes::REVIEW_APPROVED : ActivityLogTypes::REVIEW_REJECTED,
                [
                    'review_id' => $reviewId,
                    'product_id' => (int) ($review['product_id'] ?? 0),
                    'order_id' => (int) ($review['order_id'] ?? 0),
                    'customer_id' => (int) ($review['user_id'] ?? 0),
                ],
                $status === 'approved' ? 'success' : 'warning'
            );
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
        $order = $this->applyCanonicalStoreLocationToOrder((array) $order);
        if (strtolower(trim((string) ($order['status'] ?? ''))) === 'cancelled') {
            $order['delivery_status'] = 'cancelled';
        }
        $normalizedPayment = $this->normalizeOrderPayment($order);
        $order['payment_method'] = $normalizedPayment['method'];
        $order['payment_status'] = $normalizedPayment['status'];
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

        $returnEligibility = customer_can_request_return($order);

        return view('customer/order_details', $this->getCustomerPageData('Order Details', 'orders', [
            'order' => $order,
            'items' => $orderItems,
            'tracking_info' => $trackingInfo,
            'return_meta' => parse_return_meta(
                (string) ($order['shipment_notes'] ?? ''),
                (string) ($order['delivery_notes'] ?? '')
            ),
            'can_cancel' => customer_can_cancel_order($order),
            'can_request_return' => $returnEligibility['allowed'],
            'return_request_message' => $returnEligibility['message'],
        ]));
    }

    /**
     * Customer submits a return/refund request for a completed order.
     */
    public function submitCustomerReturnRefundRequest()
    {
        $accessCheck = $this->checkCustomerAccess();
        if ($accessCheck !== true) {
            return $accessCheck;
        }

        $orderId = (int) $this->request->getPost('order_id');
        $requestType = validate_return_refund_request_type((string) $this->request->getPost('request_type'));
        $reason = trim((string) $this->request->getPost('reason'));

        $redirectBack = $this->request->getPost('redirect_to') === 'order-details'
            ? '/customer/order-details/' . $orderId
            : '/customer/orders?tab=return_refund';

        if ($reason === '' || strlen($reason) < 10) {
            return redirect()->to($redirectBack)->with('error', 'Please provide a reason (at least 10 characters).');
        }

        $payoutMethod = (string) $this->request->getPost('payout_method');
        $payoutAccount = trim((string) $this->request->getPost('payout_account'));
        $payoutAccountName = trim((string) $this->request->getPost('payout_account_name'));

        $order = $this->orderModel->getOrder($orderId);
        if (! $order || (int) ($order['created_by'] ?? 0) !== (int) $this->session->get('user_id')) {
            return redirect()->to('/customer/orders')->with('error', 'Order not found.');
        }

        $eligibility = customer_can_request_return($order);
        if (! $eligibility['allowed']) {
            return redirect()->to($redirectBack)->with('error', $eligibility['message']);
        }

        if (return_refund_requires_payout($requestType)) {
            $payoutCheck = validate_return_payout_details($payoutMethod, $payoutAccount, $payoutAccountName);
            if (! $payoutCheck['valid']) {
                return redirect()->to($redirectBack)->with('error', $payoutCheck['message']);
            }
        }

        try {
            $evidenceFiles = $this->processReturnEvidenceUploads($orderId);
        } catch (\InvalidArgumentException $e) {
            return redirect()->to($redirectBack)->with('error', $e->getMessage());
        }

        $reference = (string) ($order['reference_number'] ?? ('#' . $orderId));
        $returnToken = generate_return_qr_token($orderId);

        $meta = [
            'type' => $requestType,
            'reason' => $reason,
            'requested_at' => date('Y-m-d H:i:s'),
            'customer_id' => (int) $this->session->get('user_id'),
            'status' => 'return_requested',
            'return_token' => $returnToken,
            'qr_payload' => build_return_qr_payload($orderId, $returnToken, $reference),
            'evidence_files' => $evidenceFiles,
        ];

        if (return_refund_requires_payout($requestType)) {
            $meta['payout_method'] = $payoutMethod;
            $meta['payout_account'] = normalize_payout_account($payoutMethod, $payoutAccount);
            $meta['payout_account_name'] = $payoutAccountName;
            $meta['payout_collected_by'] = 'customer';
        }

        $returnFields = merge_return_meta_shipment_fields(
            (string) ($order['shipment_notes'] ?? ''),
            (string) ($order['delivery_notes'] ?? ''),
            $meta
        );
        $updated = $this->orderModel->updateDeliveryStatus($orderId, 'return_requested', $returnFields);

        if (! $updated) {
            return redirect()->to($redirectBack)->with('error', 'Unable to submit return/refund request.');
        }

        try {
            $this->notificationService->notifyAdmins([
                'category' => 'orders',
                'type' => 'return_requested',
                'title' => 'Return/Refund requested',
                'message' => 'Customer requested ' . return_refund_type_label($requestType) . ' for order ' . $reference . '.',
                'link' => site_url('admin/returns?status=return_requested&order=' . $orderId),
                'related_type' => 'order',
                'related_id' => $orderId,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Return/refund admin notification failed for order {id}: {message}', [
                'id' => $orderId,
                'message' => $e->getMessage(),
            ]);
        }

        $this->logUserActivity(
            'Requested ' . return_refund_type_label($requestType) . ' for order ' . $reference,
            ActivityLogTypes::RETURN_REFUND_REQUESTED,
            [
                'order_id' => $orderId,
                'reference_number' => $reference,
                'request_type' => $requestType,
                'reason' => mb_substr($reason, 0, 200),
            ]
        );

        return redirect()->to($redirectBack)->with('success', 'Return/refund request submitted. Show your return QR code to the rider during pickup.');
    }

    /**
     * Admin approves, rejects, or completes return/refund processing.
     */
    public function adminReturnRefundAction()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        if (!$this->hasAdminPanelAccess()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        $orderId = (int) ($payload['order_id'] ?? 0);
        $action = (string) ($payload['action'] ?? '');

        if ($orderId <= 0 || $action === '') {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $order = $this->orderModel->getOrder($orderId);
        if (! $order) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Order not found']);
        }

        $currentStatus = (string) ($order['delivery_status'] ?? '');
        $returnMeta = parse_return_meta(
            (string) ($order['shipment_notes'] ?? ''),
            (string) ($order['delivery_notes'] ?? '')
        ) ?? [];
        $reference = (string) ($order['reference_number'] ?? ('#' . $orderId));

        if ($action === 'approve') {
            if ($currentStatus !== 'return_requested') {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Only pending return requests can be approved']);
            }

            $riderId = (int) ($payload['rider_id'] ?? 0);
            if ($riderId <= 0) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Select a rider for return pickup']);
            }

            $rider = $this->userModel->find($riderId);
            if (! $rider || (string) ($rider['role'] ?? '') !== 'rider') {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Invalid rider selected']);
            }

            $returnMeta['status'] = 'return_approved';
            $returnMeta['approved_at'] = date('Y-m-d H:i:s');
            $returnMeta['approved_by'] = (int) $this->session->get('user_id');
            $returnMeta['assigned_rider_id'] = $riderId;
            $adminNote = trim((string) ($payload['admin_note'] ?? ''));
            if ($adminNote !== '') {
                $returnMeta['admin_note'] = $adminNote;
            }

            $returnFields = merge_return_meta_shipment_fields(
                (string) ($order['shipment_notes'] ?? ''),
                (string) ($order['delivery_notes'] ?? ''),
                $returnMeta
            );
            $updated = $this->orderModel->updateDeliveryStatus($orderId, 'return_approved', array_merge([
                'assigned_rider_id' => $riderId,
                'assigned_at' => date('Y-m-d H:i:s'),
            ], $returnFields));

            if ($updated) {
                $this->notificationService->notifyUsers([$riderId], [
                    'category' => 'delivery',
                    'type' => 'return_pickup_assigned',
                    'title' => 'Return pickup assigned',
                    'message' => 'Pick up returned items for order ' . $reference . '.',
                    'link' => site_url('rider/returns?order_id=' . $orderId),
                    'related_type' => 'order',
                    'related_id' => $orderId,
                ]);
                $this->notificationService->notifyUsers([(int) ($order['created_by'] ?? 0)], [
                    'category' => 'orders',
                    'type' => 'return_approved',
                    'title' => 'Return/refund approved',
                    'message' => 'Your return/refund request for order ' . $reference . ' was approved. Rider will pick up the item.',
                    'link' => site_url('customer/orders?tab=return_refund'),
                    'related_type' => 'order',
                    'related_id' => $orderId,
                ]);
            }

            if ($updated) {
                $this->logUserActivity(
                    'Approved return/refund for order ' . $reference,
                    ActivityLogTypes::RETURN_REFUND_APPROVED,
                    ['order_id' => $orderId, 'reference_number' => $reference, 'rider_id' => $riderId],
                    'success',
                    (int) $this->session->get('user_id')
                );
            }

            return $this->response->setJSON([
                'success' => (bool) $updated,
                'message' => $updated ? 'Return/refund request approved and rider assigned' : 'Unable to approve request',
            ]);
        }

        if ($action === 'reject') {
            if ($currentStatus !== 'return_requested') {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Only pending return requests can be rejected']);
            }

            $rejectReason = trim((string) ($payload['reject_reason'] ?? ''));
            if ($rejectReason === '') {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Rejection reason is required']);
            }

            $returnMeta['status'] = 'rejected';
            $returnMeta['rejected_at'] = date('Y-m-d H:i:s');
            $returnMeta['rejected_by'] = (int) $this->session->get('user_id');
            $returnMeta['reject_reason'] = $rejectReason;

            $returnFields = merge_return_meta_shipment_fields(
                (string) ($order['shipment_notes'] ?? ''),
                (string) ($order['delivery_notes'] ?? ''),
                $returnMeta
            );
            $updated = $this->orderModel->updateDeliveryStatus($orderId, 'completed', $returnFields);

            if ($updated) {
                $this->notificationService->notifyUsers([(int) ($order['created_by'] ?? 0)], [
                    'category' => 'orders',
                    'type' => 'return_rejected',
                    'title' => 'Return/refund rejected',
                    'message' => 'Your return/refund request for order ' . $reference . ' was rejected: ' . $rejectReason,
                    'link' => site_url('customer/order-details/' . $orderId),
                    'related_type' => 'order',
                    'related_id' => $orderId,
                ]);
            }

            if ($updated) {
                $this->logUserActivity(
                    'Rejected return/refund for order ' . $reference,
                    ActivityLogTypes::RETURN_REFUND_REJECTED,
                    [
                        'order_id' => $orderId,
                        'reference_number' => $reference,
                        'reject_reason' => $rejectReason,
                    ],
                    'warning',
                    (int) $this->session->get('user_id')
                );
            }

            return $this->response->setJSON([
                'success' => (bool) $updated,
                'message' => $updated ? 'Return/refund request rejected' : 'Unable to reject request',
            ]);
        }

        if ($action === 'complete_refund') {
            if ($currentStatus !== 'return_picked_up') {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Refund can be completed only after rider pickup']);
            }

            if (return_refund_requires_payout((string) ($returnMeta['type'] ?? 'return_and_refund'))) {
                if (trim((string) ($returnMeta['payout_account'] ?? '')) === '') {
                    return $this->response->setStatusCode(422)->setJSON([
                        'success' => false,
                        'message' => 'Customer GCash/e-wallet details are missing. Ask the rider to collect them during pickup.',
                    ]);
                }
            }

            $refundPayoutReference = trim((string) ($payload['refund_payout_reference'] ?? ''));
            if ($refundPayoutReference === '') {
                $refundPayoutReference = trim((string) ($returnMeta['pending_refund_reference'] ?? ''));
            }
            if ($refundPayoutReference !== '') {
                $normalizedRef = format_refund_payout_reference_display($refundPayoutReference);
                if ($normalizedRef !== '') {
                    $refundPayoutReference = $normalizedRef;
                }
            }
            if (return_refund_requires_payout((string) ($returnMeta['type'] ?? 'return_and_refund')) && $refundPayoutReference === '') {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Enter or paste the GCash/Maya reference, or use Send via GCash to auto-fill.',
                ]);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            try {
                $damagedKeys = parse_return_damaged_item_keys($payload['damaged_items'] ?? []);
                $requestType = (string) ($returnMeta['type'] ?? 'return_and_refund');
                if ($damagedKeys === [] && $requestType === 'damaged_item') {
                    foreach ((array) ($order['items'] ?? []) as $line) {
                        if (! is_array($line)) {
                            continue;
                        }
                        $productId = (int) ($line['id'] ?? $line['product_id'] ?? 0);
                        if ($productId <= 0) {
                            continue;
                        }
                        $variantId = isset($line['variant_id']) && (int) $line['variant_id'] > 0
                            ? (int) $line['variant_id']
                            : null;
                        $damagedKeys[] = return_item_stock_key($productId, $variantId);
                    }
                    $damagedKeys = array_values(array_unique($damagedKeys));
                }

                if (! $this->productModel->processReturnedItemsStock(
                    (array) ($order['items'] ?? []),
                    $damagedKeys,
                    'return_refund',
                    $orderId,
                    (int) $this->session->get('user_id')
                )) {
                    throw new \RuntimeException('Failed to update stock for returned items.');
                }

                $returnMeta['status'] = 'return_refund';
                $returnMeta['refunded_at'] = date('Y-m-d H:i:s');
                $returnMeta['refunded_by'] = (int) $this->session->get('user_id');
                $returnMeta['damaged_items'] = $damagedKeys;
                if ($damagedKeys !== []) {
                    $returnMeta['damaged_recorded_at'] = date('Y-m-d H:i:s');
                }
                if ($refundPayoutReference !== '') {
                    $returnMeta['refund_payout_reference'] = $refundPayoutReference;
                    $returnMeta['refund_sent_at'] = date('Y-m-d H:i:s');
                }

                $orderNotes = trim((string) ($order['notes'] ?? ''));
                $refundLine = 'REFUND_PROCESSED:' . date('Y-m-d H:i:s')
                    . '|amount=' . round((float) ($order['total_amount'] ?? 0), 2)
                    . ($refundPayoutReference !== '' ? '|payout_ref=' . $refundPayoutReference : '');
                $orderNotes = $orderNotes !== '' ? ($orderNotes . "\n" . $refundLine) : $refundLine;

                $returnFields = merge_return_meta_shipment_fields(
                    (string) ($order['shipment_notes'] ?? ''),
                    (string) ($order['delivery_notes'] ?? ''),
                    $returnMeta
                );
                $updated = $this->orderModel->updateOrder(
                    $orderId,
                    ['status' => 'return_refund', 'notes' => $orderNotes],
                    [],
                    array_merge([
                        'status' => 'return_refund',
                        'completed_at' => date('Y-m-d H:i:s'),
                    ], $returnFields)
                );

                if (! $updated) {
                    throw new \RuntimeException('Unable to finalize refund status.');
                }

                $db->transComplete();
                if ($db->transStatus() === false) {
                    throw new \RuntimeException('Refund transaction failed.');
                }
            } catch (\Throwable $e) {
                $db->transRollback();

                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
            }

            $this->syncOrderToRecord($orderId);
            $this->notificationService->notifyUsers([(int) ($order['created_by'] ?? 0)], [
                'category' => 'orders',
                'type' => 'return_refund_completed',
                'title' => 'Refund completed',
                'message' => 'Refund for order ' . $reference . ' has been processed.',
                'link' => site_url('customer/orders?tab=return_refund'),
                'related_type' => 'order',
                'related_id' => $orderId,
            ]);

            $this->logUserActivity(
                'Completed return/refund for order ' . $reference,
                ActivityLogTypes::RETURN_REFUND_COMPLETED,
                [
                    'order_id' => $orderId,
                    'reference_number' => $reference,
                    'refund_payout_reference' => $refundPayoutReference,
                    'amount' => round((float) ($order['total_amount'] ?? 0), 2),
                ],
                'success',
                (int) $this->session->get('user_id')
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => $damagedKeys !== []
                    ? 'Refund completed. Resellable stock restored; damaged items recorded.'
                    : 'Refund completed and stock restored',
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid action']);
    }

    /**
     * @return list<array<string, string>>
     */
    private function processReturnEvidenceUploads(int $orderId): array
    {
        $uploadPath = WRITEPATH . 'uploads/return_evidence/';
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $candidates = [];
        $multiple = $this->request->getFileMultiple('return_evidence');
        if (is_array($multiple)) {
            foreach ($multiple as $file) {
                if ($file instanceof \CodeIgniter\HTTP\Files\UploadedFile
                    && $file->isValid()
                    && ! $file->hasMoved()) {
                    $candidates[] = $file;
                }
            }
        }

        $single = $this->request->getFile('return_evidence');
        if ($single instanceof \CodeIgniter\HTTP\Files\UploadedFile
            && $single->isValid()
            && ! $single->hasMoved()) {
            $candidates[] = $single;
        }

        if ($candidates === []) {
            throw new \InvalidArgumentException('Please upload at least one photo or video showing the product issue.');
        }

        $imageMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $videoMimes = ['video/mp4', 'video/webm', 'video/quicktime'];
        $allowedMimes = array_merge($imageMimes, $videoMimes);
        $maxImageBytes = 5 * 1024 * 1024;
        $maxVideoBytes = 25 * 1024 * 1024;
        $saved = [];

        foreach (array_slice($candidates, 0, 3) as $file) {
            $mime = (string) $file->getMimeType();
            if (! in_array($mime, $allowedMimes, true)) {
                throw new \InvalidArgumentException('Only JPG, PNG, GIF, WEBP images and MP4, WEBM, MOV videos are allowed.');
            }

            $isVideo = in_array($mime, $videoMimes, true);
            $maxBytes = $isVideo ? $maxVideoBytes : $maxImageBytes;
            if ($file->getSize() > $maxBytes) {
                throw new \InvalidArgumentException(
                    $isVideo ? 'Each video must be 25MB or smaller.' : 'Each image must be 5MB or smaller.'
                );
            }

            $extension = strtolower((string) $file->getExtension());
            if ($extension === '') {
                $extension = $isVideo ? 'mp4' : 'jpg';
            }

            $filename = 'return_evidence_' . $orderId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
            if (! $file->move($uploadPath, $filename)) {
                continue;
            }

            $saved[] = [
                'filename' => $filename,
                'type' => $isVideo ? 'video' : 'image',
                'original_name' => (string) $file->getClientName(),
                'uploaded_at' => date('Y-m-d H:i:s'),
            ];
        }

        if ($saved === []) {
            throw new \InvalidArgumentException('Unable to save uploaded evidence. Please try again.');
        }

        return $saved;
    }

    /**
     * Move return metadata out of address description (shipment notes) for legacy rows.
     *
     * @param array<string, mixed> $order
     */
    private function repairReturnMetaStorage(array $order): void
    {
        $orderId = (int) ($order['id'] ?? 0);
        if ($orderId <= 0) {
            return;
        }

        $shipmentNotes = (string) ($order['shipment_notes'] ?? '');
        if (! str_contains($shipmentNotes, 'RETURN_META:')) {
            return;
        }

        $meta = parse_return_meta($shipmentNotes, (string) ($order['delivery_notes'] ?? ''));
        if ($meta === null) {
            return;
        }

        $this->orderModel->updateOrder(
            $orderId,
            [],
            [],
            merge_return_meta_shipment_fields(
                $shipmentNotes,
                (string) ($order['delivery_notes'] ?? ''),
                $meta
            )
        );
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
