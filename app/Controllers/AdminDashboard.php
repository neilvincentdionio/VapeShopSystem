<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\ProductModel;
use App\Models\ReviewModel;

class AdminDashboard extends BaseController
{
    protected $orderModel;
    protected $productModel;
    protected $reviewModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->productModel = new ProductModel();
        $this->reviewModel = new ReviewModel();
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
            'lowStockCount' => $db->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity < 10")->getRow()->total,
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
            SELECT id, name, stock_quantity, price
            FROM products 
            WHERE stock_quantity < 10
            ORDER BY stock_quantity ASC
            LIMIT 10
        ")->getResult();
    }

    private function getRecentReviews()
    {
        $db = \Config\Database::connect();
        
        return $db->query("
            SELECT pr.*, p.name as product_name, u.name as user_name
            FROM product_reviews pr
            JOIN products p ON pr.product_id = p.id
            JOIN users u ON pr.user_id = u.id
            ORDER BY pr.created_at DESC
            LIMIT 5
        ")->getResult();
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
        $db = \Config\Database::connect();
        $db->query("UPDATE product_reviews SET status = 'approved' WHERE id = ?", [$reviewId]);
        
        return $this->response->setJSON(['success' => true]);
    }

    public function rejectReview($reviewId)
    {
        $db = \Config\Database::connect();
        $db->query("UPDATE product_reviews SET status = 'rejected' WHERE id = ?", [$reviewId]);
        
        return $this->response->setJSON(['success' => true]);
    }
}
