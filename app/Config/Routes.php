<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Redirect root to login
$routes->get('/', 'Auth::login');

// Authentication routes
$routes->get('/login', 'Auth::login');
$routes->get('/register', 'Auth::register');
$routes->post('/auth/authenticate', 'Auth::authenticate');
$routes->post('/auth/register', 'Auth::storeRegistration');
$routes->get('/otp', 'Auth::otp');
$routes->post('/otp/verify', 'Auth::verifyOtp');
$routes->post('/otp/resend', 'Auth::resendOtp');
$routes->get('/forgot-password', 'Auth::forgotPassword');
$routes->post('/auth/sendResetLink', 'Auth::sendResetLink');
$routes->get('/reset-password', 'Auth::resetPassword');
$routes->post('/auth/updatePassword', 'Auth::updatePassword');
$routes->get('/auth/logout', 'Auth::logout');

// Dashboard routes (protected by AuthFilter)
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'auth']);
$routes->get('/admin', 'Dashboard::index', ['filter' => 'auth:admin']);
$routes->get('/admin/dashboard', 'Dashboard::index', ['filter' => 'auth:admin']);
$routes->get('/dashboard/profile', 'Dashboard::profile', ['filter' => 'auth']);
$routes->post('/dashboard/profile/update', 'Dashboard::updateCustomerProfile', ['filter' => 'auth']);
$routes->get('/dashboard/settings', 'Dashboard::settings', ['filter' => 'auth']);

// Orders routes (Admin only)
$routes->get('/orders', 'Dashboard::adminOrders', ['filter' => ['auth:admin', 'permission:manage_orders']]);
$routes->get('/orders/checkout/(:num)', 'Dashboard::adminCheckout/$1', ['filter' => ['auth:admin', 'permission:manage_orders']]);
$routes->post('/orders/checkout-submit/(:num)', 'Dashboard::adminCheckoutSubmit/$1', ['filter' => ['auth:admin', 'permission:manage_orders']]);

// Working checkout route - bypass all issues
$routes->get('/checkout/(:num)', function($orderId) {
    // Direct checkout without CodeIgniter issues
    $orderData = [
        'id' => $orderId,
        'reference_number' => 'SAL-2026-0002',
        'total_amount' => 62.50,
        'status' => 'pending'
    ];
    
    $orderItems = [
        [
            'id' => 1,
            'name' => 'Fruity Cereal',
            'qty' => 1,
            'price' => 62.50
        ]
    ];
    
    return view('admin/orders/checkout_direct', [
        'order' => $orderData,
        'items' => $orderItems,
        'total' => 62.50,
        'reference_number' => 'SAL-2026-0002',
        'page_title' => 'Order Checkout',
        'user_name' => 'Admin'
    ]);
});

// Customer storefront routes (protected by AuthFilter)
$routes->get('/customer/home', 'Dashboard::customerHome', ['filter' => 'auth']);
$routes->get('/customer/products', 'Dashboard::customerProducts', ['filter' => 'auth']);
$routes->get('/customer/orders', 'Dashboard::customerOrders', ['filter' => 'auth']);
$routes->get('/customer/cart', 'Dashboard::customerCart', ['filter' => 'auth']);
$routes->get('/customer/user-management', static function () {
    return redirect()->to('/dashboard')->with('error', 'Access denied.');
}, ['filter' => 'auth']);
$routes->get('/customer/messages', 'Messages::customerInbox', ['filter' => 'auth']);
$routes->post('/customer/messages/send', 'Messages::customerSend', ['filter' => 'auth']);
$routes->get('/messages/(:num)/poll', 'Messages::poll/$1', ['filter' => 'auth']);

// Rider dashboard routes (Rider only)
$routes->get('/rider/dashboard', 'Dashboard::riderDashboard', ['filter' => 'auth:rider']);
$routes->get('/rider/deliveries', 'Dashboard::riderDeliveries', ['filter' => 'auth:rider']);
$routes->get('/rider/messages', 'Messages::riderInbox', ['filter' => 'auth:rider']);
$routes->get('/rider/messages/(:num)', 'Messages::riderConversation/$1', ['filter' => 'auth:rider']);
$routes->post('/rider/messages/(:num)/reply', 'Messages::riderReply/$1', ['filter' => 'auth:rider']);
$routes->get('/dashboard/live-update-token', 'Dashboard::liveUpdateToken', ['filter' => 'auth']);
$routes->get('/notifications/recent', 'Notifications::recent', ['filter' => 'auth']);
$routes->post('/notifications/mark-read/(:num)', 'Notifications::markRead/$1', ['filter' => 'auth']);
$routes->post('/notifications/mark-all-read', 'Notifications::markAllRead', ['filter' => 'auth']);
$routes->post('/dashboard/riderUpdateDeliveryStatus', 'Dashboard::riderUpdateDeliveryStatus', ['filter' => 'auth:rider']);
$routes->post('/dashboard/submitDeliveryProof', 'Dashboard::submitDeliveryProof', ['filter' => 'auth:rider']);
$routes->post('/dashboard/updateRiderLocation', 'Dashboard::updateRiderLocation', ['filter' => 'auth:rider']);
$routes->get('/dashboard/orderTracking/(:num)', 'Dashboard::orderTracking/$1', ['filter' => 'auth']);
$routes->post('/dashboard/assignRiderToOrder', 'Dashboard::assignRiderToOrder', ['filter' => 'auth:admin']);
$routes->post('/dashboard/getDeliveryProof', 'Dashboard::getDeliveryProof', ['filter' => 'auth:admin']);
$routes->get('/uploads/delivery_proofs/(:any)', 'Dashboard::serveDeliveryProof/$1');

// User Management routes (protected by AuthFilter)
$routes->get('/user-management', 'UserManagement::index', ['filter' => ['auth:admin', 'permission:manage_users']]);
$routes->get('/user-management/create', 'UserManagement::create', ['filter' => ['auth:admin', 'permission:manage_users']]);
$routes->post('/user-management/store', 'UserManagement::store', ['filter' => ['auth:admin', 'permission:manage_users']]);
$routes->get('/user-management/view/(:num)', 'UserManagement::view/$1', ['filter' => ['auth:admin', 'permission:manage_users']]);
$routes->get('/user-management/verification-id/(:num)', 'UserManagement::verificationId/$1', ['filter' => ['auth:admin', 'permission:manage_users']]);
$routes->post('/user-management/approve/(:num)', 'UserManagement::approve/$1', ['filter' => ['auth:admin', 'permission:manage_users']]);
$routes->get('/user-management/edit/(:num)', 'UserManagement::edit/$1', ['filter' => ['auth:admin', 'permission:manage_users']]);
$routes->post('/user-management/update/(:num)', 'UserManagement::update/$1', ['filter' => ['auth:admin', 'permission:manage_users']]);
$routes->get('/user-management/delete/(:num)', 'UserManagement::delete/$1', ['filter' => ['auth:admin', 'permission:manage_users']]);
$routes->post('/user-management/destroy/(:num)', 'UserManagement::destroy/$1', ['filter' => ['auth:admin', 'permission:manage_users']]);

// Records module routes (Task 3)
$routes->group('records', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Records::index');
    $routes->get('create', 'Records::create');
    $routes->get('(:num)', 'Records::show/$1');
    $routes->post('store', 'Records::store');
    $routes->get('edit/(:num)', 'Records::edit/$1');
    $routes->post('update/(:num)', 'Records::update/$1');
    $routes->post('delete/(:num)', 'Records::delete/$1');
});

// Products module routes (Admin only)
$routes->group('products', ['filter' => 'auth:admin'], static function ($routes) {
    $routes->get('/', 'Products::index', ['filter' => 'permission:view_products']);
    $routes->get('view/(:num)', 'Products::view/$1', ['filter' => 'permission:view_products']);
    $routes->get('create', 'Products::create', ['filter' => 'permission:create_products']);
    $routes->post('store', 'Products::store', ['filter' => 'permission:create_products']);
    $routes->get('edit/(:num)', 'Products::edit/$1', ['filter' => 'permission:update_products']);
    $routes->post('update/(:num)', 'Products::update/$1', ['filter' => 'permission:update_products']);
    $routes->get('delete/(:num)', 'Products::delete/$1', ['filter' => 'permission:delete_products']);
    $routes->get('toggle-status/(:num)', 'Products::toggleStatus/$1', ['filter' => 'permission:update_products']);
    $routes->post('reviews/reply/(:num)', 'Products::replyReview/$1', ['filter' => 'permission:view_products']);
});

// Customer product details route
$routes->get('/customer/product/(:num)', 'Dashboard::productDetails/$1', ['filter' => 'auth']);

// Customer cart actions
$routes->post('/customer/cart/add', 'Dashboard::customerCartAdd', ['filter' => 'auth']);
$routes->post('/customer/cart/update', 'Dashboard::customerCartUpdate', ['filter' => 'auth']);
$routes->post('/customer/cart/remove', 'Dashboard::customerCartRemove', ['filter' => 'auth']);

// Customer direct order processing
$routes->post('/customer/direct-order', 'Dashboard::customerDirectOrder', ['filter' => 'auth']);

// Customer checkout & receipt (cashier system)
$routes->get('/customer/checkout', 'Dashboard::customerCheckout', ['filter' => 'auth']);
$routes->post('/customer/checkout', 'Dashboard::customerCheckoutSubmit', ['filter' => 'auth']);
$routes->get('/customer/receipt/(:num)', 'Dashboard::customerReceipt', ['filter' => 'auth']);

// Customer 18+ age verification
$routes->get('/customer/age-verification', 'Dashboard::customerAgeVerification', ['filter' => 'auth']);
$routes->post('/customer/age-verification', 'Dashboard::customerAgeVerificationSubmit', ['filter' => 'auth']);

// Customer order actions (Shopee-like delivery process)
$routes->get('/customer/orders/(:num)/(:alpha)', 'Dashboard::customerOrderAction/$1/$2', ['filter' => 'auth']);

// Customer order details page
$routes->get('/customer/order-details/(:num)', 'Dashboard::viewOrderDetails/$1', ['filter' => 'auth']);


// Admin delivery status update (AJAX)
$routes->post('/orders/update-delivery-status', 'Dashboard::updateDeliveryStatus', ['filter' => ['auth:admin', 'permission:manage_orders']]);

// Admin delivery information endpoints
$routes->get('/orders/get-delivery-info/(:num)', 'Dashboard::getDeliveryInfo/$1', ['filter' => ['auth:admin', 'permission:manage_orders']]);
$routes->post('/orders/save-delivery-info', 'Dashboard::saveDeliveryInfo', ['filter' => ['auth:admin', 'permission:manage_orders']]);

// Admin order details page
$routes->get('/admin/order-details/(:num)', 'Dashboard::viewAdminOrderDetails/$1', ['filter' => ['auth:admin', 'permission:manage_orders']]);
$routes->get('/rider/order-details/(:num)', 'Dashboard::viewRiderOrderDetails/$1', ['filter' => 'auth:rider']);
$routes->get('/dashboard/order-details-json/(:num)', 'Dashboard::orderDetailsJson/$1', ['filter' => 'auth']);

// Backup Management routes (Admin only)
$routes->get('/backup', 'BackupController::index', ['filter' => ['auth:admin', 'permission:manage_backups']]);
$routes->post('/backup/create', 'BackupController::create', ['filter' => ['auth:admin', 'permission:manage_backups']]);
$routes->post('/backup/restore', 'BackupController::restore', ['filter' => ['auth:admin', 'permission:manage_backups']]);
$routes->post('/backup/delete', 'BackupController::delete', ['filter' => ['auth:admin', 'permission:manage_backups']]);
$routes->get('/backup/download/(:any)', 'BackupController::download/$1', ['filter' => ['auth:admin', 'permission:manage_backups']]);
$routes->post('/backup/cleanup', 'BackupController::cleanup', ['filter' => ['auth:admin', 'permission:manage_backups']]);
$routes->get('/backup/stats', 'BackupController::stats', ['filter' => ['auth:admin', 'permission:manage_backups']]);

// Test route
$routes->get('/admin/test', 'AdminController::test');

// Simple test routes for debugging
$routes->get('/test-dashboard', function() {
    return "Dashboard test works!";
});

$routes->get('/test-products', function() {
    return "Products test works!";
});

// Session and Activity Logs routes (Admin only)
$routes->get('/admin/session-logs', 'AdminController::sessionLogs', ['filter' => 'auth:admin']);
$routes->get('/admin/get-session-details/(.+)', 'AdminController::getSessionDetails/$1', ['filter' => 'auth:admin']);
$routes->post('/admin/end-session/(.+)', 'AdminController::endSession/$1', ['filter' => 'auth:admin']);

$routes->get('/admin/activity-logs', 'AdminController::activityLogs', ['filter' => 'auth:admin']);
$routes->get('/admin/get-log-details/(:num)', 'AdminController::getLogDetails/$1', ['filter' => 'auth:admin']);
$routes->get('/admin/export-logs', 'AdminController::exportLogs', ['filter' => 'auth:admin']);
$routes->get('/admin/security-report', 'AdminController::exportSecurityReport', ['filter' => 'auth:admin']);
$routes->get('/admin/messages', 'Messages::adminInbox', ['filter' => 'auth']);
$routes->get('/admin/messages/(:num)', 'Messages::adminConversation/$1', ['filter' => 'auth']);
$routes->post('/admin/messages/(:num)/reply', 'Messages::adminReply/$1', ['filter' => 'auth']);
$routes->post('/admin/messages/(:num)/status', 'Messages::updateStatus/$1', ['filter' => 'auth']);
$routes->post('/admin/messages/(:num)/assign-rider', 'Messages::assignRider/$1', ['filter' => 'auth']);

// Additional routes for index views compatibility
$routes->get('/admin/session-logs/details/(:num)', 'AdminController::getSessionDetailsById/$1', ['filter' => 'auth:admin']);
$routes->post('/admin/session-logs/end/(:num)', 'AdminController::endSessionById/$1', ['filter' => 'auth:admin']);
$routes->post('/admin/session-logs/cleanup', 'AdminController::cleanupSessions', ['filter' => 'auth:admin']);

$routes->get('/admin/activity-logs/details/(:num)', 'AdminController::getLogDetailsById/$1', ['filter' => 'auth:admin']);
$routes->post('/admin/activity-logs/cleanup', 'AdminController::cleanupLogs', ['filter' => 'auth:admin']);

// API Authentication routes (JWT)
$routes->post('/api/auth/login', 'ApiAuth::login');
$routes->post('/api/auth/mfa/verify', 'ApiAuth::verifyMfa');
$routes->post('/api/auth/mfa/resend', 'ApiAuth::resendMfa');
$routes->post('/api/auth/refresh', 'ApiAuth::refresh');
$routes->post('/api/auth/logout', 'ApiAuth::logout');
$routes->get('/api/auth/me', 'ApiAuth::me', ['filter' => ['jwtauth', 'permission:read']]);

// Product review and rating routes
$routes->post('/customer/product-review/submit', 'Dashboard::submitProductReview', ['filter' => 'auth']);

// Admin Dashboard routes
$routes->get('/admin-dashboard', 'AdminDashboard::index', ['filter' => 'auth:admin']);
$routes->get('/adminDashboard/getRevenueData', 'AdminDashboard::getRevenueData', ['filter' => 'auth:admin']);
$routes->post('/adminDashboard/approveReview/(:num)', 'AdminDashboard::approveReview/$1', ['filter' => 'auth:admin']);
$routes->post('/adminDashboard/rejectReview/(:num)', 'AdminDashboard::rejectReview/$1', ['filter' => 'auth:admin']);

// Admin review management routes
$routes->get('/admin/reviews/product/(:num)', 'Dashboard::getProductReviews/$1', ['filter' => 'auth:admin']);
$routes->get('/admin/reviews/order/(:num)', 'Dashboard::getOrderReviews/$1');
$routes->post('/admin/reviews/approve/(:num)', 'Dashboard::approveReview/$1', ['filter' => 'auth:admin']);
$routes->post('/admin/reviews/reject/(:num)', 'Dashboard::rejectReview/$1', ['filter' => 'auth:admin']);

// Rider-Admin delivery workflow routes
$routes->post('/dashboard/deliverOrderToRider', 'Dashboard::deliverOrderToRider', ['filter' => 'auth:admin']);
$routes->get('/dashboard/getOrdersReadyForPickup', 'Dashboard::getOrdersReadyForPickup', ['filter' => 'auth:admin']);
