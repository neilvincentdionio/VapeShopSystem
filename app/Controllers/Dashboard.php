<?php

namespace App\Controllers;

use App\Models\DashboardModel;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    protected $session;
    protected $dashboardModel;
    protected $userModel;

    public function __construct()
    {
        $this->session = session();
        $this->dashboardModel = new DashboardModel();
        $this->userModel = new UserModel();
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

        $products = [
            ['name' => 'VapeHub X Pod Kit', 'description' => 'Compact pod system, smooth draw', 'price' => 1250],
            ['name' => 'Salt Mint E-Liquid', 'description' => '30ml, cool mint profile', 'price' => 320],
            ['name' => 'Replacement Coil Pack', 'description' => 'Long-life mesh coils (pack of 3)', 'price' => 450],
            ['name' => 'Portable Charger Case', 'description' => 'Fast charging pocket case', 'price' => 680],
        ];

        return view('customer/products', $this->getCustomerPageData('Products', 'products', [
            'products' => $products,
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

        $todayStats = $this->dashboardModel->getAnalytics('today', 'customer', $this->session->get('user_shop_name'));
        $todayOrders = (int) ($todayStats['orders'] ?? 0);

        $orderItems = [
            ['title' => 'Recent Orders', 'message' => 'You have ' . number_format($todayOrders) . ' order(s) today.'],
            ['title' => 'Order Notifications', 'message' => 'Enable profile updates so delivery status alerts reach you quickly.'],
        ];

        return view('customer/orders', $this->getCustomerPageData('Orders', 'orders', [
            'order_items' => $orderItems,
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

        $cartItems = [
            ['name' => 'Starter Pod Kit', 'quantity' => 1, 'amount' => 1250],
            ['name' => 'Salt Mint E-Liquid', 'quantity' => 2, 'amount' => 640],
        ];
        $estimatedTotal = array_sum(array_column($cartItems, 'amount'));

        return view('customer/cart', $this->getCustomerPageData('Cart', 'cart', [
            'cart_items' => $cartItems,
            'estimated_total' => $estimatedTotal,
        ]));
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
            return view('customer/profile', $this->getCustomerPageData('Profile', 'profile'));
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
     * Settings page (admin only)
     */
    public function settings()
    {
        // Check authentication
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }

        // Check if user is admin
        if ($this->session->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard')
                           ->with('error', 'Access denied. Admin privileges required.');
        }

        $data = [
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $this->session->get('user_role'),
            'user_shop_name' => $this->session->get('user_shop_name'),
            'page_title' => 'Settings'
        ];

        return view('admin/dashboard/settings', $data);
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
