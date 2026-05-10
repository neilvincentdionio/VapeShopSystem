<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\ProductModel;
use App\Models\ReviewModel;
use App\Libraries\NotificationService;

class AdminDashboard extends BaseController
{
    protected $orderModel;
    protected $productModel;
    protected $reviewModel;
    protected NotificationService $notificationService;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->productModel = new ProductModel();
        $this->reviewModel = new ReviewModel();
        $this->notificationService = new NotificationService();
    }

    public function index()
    {
        // Get dashboard stats
        $stats = $this->getDashboardStats();
        $salesData = $this->getSalesData();
        $topProducts = $this->getTopProducts();
        $lowStock = $this->getLowStockAlerts();
        $recentReviews = $this->getRecentReviews();

        return view('admin/admin_dashboard', [
            'stats' => $stats,
            'salesData' => $salesData,
            'topProducts' => $topProducts,
            'lowStock' => $lowStock,
            'recentReviews' => $recentReviews,
            'page_title' => 'Admin Dashboard'
        ]);
    }

    private function getDashboardStats()
    {
        $db = \Config\Database::connect();
        
        return [
            'totalRevenue' => $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE payment_status = 'paid'")->getRow()->total,
            'totalOrders' => $db->query("SELECT COUNT(*) as total FROM orders")->getRow()->total,
            'totalProducts' => $db->query("SELECT COUNT(*) as total FROM products")->getRow()->total,
            'pendingReviews' => $db->query("SELECT COUNT(*) as total FROM product_reviews WHERE status = 'pending'")->getRow()->total,
            'lowStockCount' => $db->query("SELECT COUNT(*) as total FROM products WHERE stock_qty < 10")->getRow()->total,
            'todayRevenue' => $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE DATE(created_at) = CURDATE() AND payment_status = 'paid'")->getRow()->total
        ];
    }

    private function getSalesData()
    {
        $db = \Config\Database::connect();
        
        // Get last 30 days sales
        $sales = $db->query("
            SELECT DATE(created_at) as date, SUM(total_amount) as total, COUNT(*) as orders
            FROM orders 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
            AND payment_status = 'paid'
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ")->getResult();

        return [
            'labels' => array_map(function($item) { return date('M j', strtotime($item->date)); }, $sales),
            'revenue' => array_map(function($item) { return (float)$item->total; }, $sales),
            'orders' => array_map(function($item) { return (int)$item->orders; }, $sales)
        ];
    }

    private function getTopProducts()
    {
        $db = \Config\Database::connect();
        
        return $db->query("
            SELECT p.name, SUM(oi.quantity) as sold, SUM(oi.quantity * oi.price) as revenue
            FROM products p
            JOIN order_items oi ON p.id = oi.product_id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.payment_status = 'paid'
            AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY p.id, p.name
            ORDER BY sold DESC
            LIMIT 10
        ")->getResult();
    }

    private function getLowStockAlerts()
    {
        $db = \Config\Database::connect();
        
        return $db->query("
            SELECT id, name, stock_qty AS stock_quantity, price
            FROM products 
            WHERE stock_qty < 10
            ORDER BY stock_qty ASC
            LIMIT 10
        ")->getResult();
    }

    private function getRecentReviews()
    {
        return $this->reviewModel->getRecentProductReviews(5);
    }

    public function getRevenueData()
    {
        $period = $this->request->getGet('period', 'month');
        $db = \Config\Database::connect();
        
        $sql = "";
        switch($period) {
            case 'week':
                $sql = "SELECT DATE(created_at) as date, SUM(total_amount) as total FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND payment_status = 'paid' GROUP BY DATE(created_at)";
                break;
            case 'month':
                $sql = "SELECT DATE(created_at) as date, SUM(total_amount) as total FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND payment_status = 'paid' GROUP BY DATE(created_at)";
                break;
            case 'year':
                $sql = "SELECT MONTH(created_at) as date, SUM(total_amount) as total FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND payment_status = 'paid' GROUP BY MONTH(created_at)";
                break;
        }
        
        $data = $db->query($sql)->getResult();
        
        return $this->response->setJSON([
            'labels' => array_map(function($item) use ($period) { 
                return $period == 'year' ? date('M', mktime(0, 0, 0, $item->date, 1)) : date('M j', strtotime($item->date)); 
            }, $data),
            'data' => array_map(function($item) { return (float)$item->total; }, $data)
        ]);
    }

    public function approveReview($reviewId)
    {
        $review = $this->reviewModel->find((int) $reviewId);
        $db = \Config\Database::connect();
        $db->query("UPDATE product_reviews SET status = 'approved' WHERE id = ?", [$reviewId]);
        if ($review) {
            $this->notificationService->notifyUsers([(int) ($review['user_id'] ?? 0)], [
                'category' => 'approvals',
                'type' => 'review_approved',
                'title' => 'Review approved',
                'message' => 'Your product review was approved.',
                'link' => site_url('customer/orders?tab=completed'),
                'related_type' => 'review',
                'related_id' => (int) $reviewId,
            ]);
        }
        
        return $this->response->setJSON(['success' => true]);
    }

    public function rejectReview($reviewId)
    {
        $review = $this->reviewModel->find((int) $reviewId);
        $db = \Config\Database::connect();
        $db->query("UPDATE product_reviews SET status = 'rejected' WHERE id = ?", [$reviewId]);
        if ($review) {
            $this->notificationService->notifyUsers([(int) ($review['user_id'] ?? 0)], [
                'category' => 'approvals',
                'type' => 'review_rejected',
                'title' => 'Review rejected',
                'message' => 'Your product review was rejected.',
                'link' => site_url('customer/orders?tab=completed'),
                'related_type' => 'review',
                'related_id' => (int) $reviewId,
            ]);
        }
        
        return $this->response->setJSON(['success' => true]);
    }
}
