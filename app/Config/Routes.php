<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Redirect root to login
$routes->get('/', 'Auth::login');

// Authentication routes
$routes->get('/login', 'Auth::login');
$routes->post('/auth/authenticate', 'Auth::authenticate');
$routes->get('/forgot-password', 'Auth::forgotPassword');
$routes->post('/auth/sendResetLink', 'Auth::sendResetLink');
$routes->get('/reset-password', 'Auth::resetPassword');
$routes->post('/auth/updatePassword', 'Auth::updatePassword');
$routes->get('/auth/logout', 'Auth::logout');

// Dashboard routes (protected by AuthFilter)
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'auth']);
$routes->get('/dashboard/profile', 'Dashboard::profile', ['filter' => 'auth']);
$routes->get('/dashboard/settings', 'Dashboard::settings', ['filter' => 'auth']);

// User Management routes (protected by AuthFilter)
$routes->get('/user-management', 'UserManagement::index', ['filter' => 'auth:admin']);
$routes->get('/user-management/create', 'UserManagement::create', ['filter' => 'auth:admin']);
$routes->post('/user-management/store', 'UserManagement::store', ['filter' => 'auth:admin']);
$routes->get('/user-management/edit/(:num)', 'UserManagement::edit/$1', ['filter' => 'auth:admin']);
$routes->post('/user-management/update/(:num)', 'UserManagement::update/$1', ['filter' => 'auth:admin']);
$routes->get('/user-management/delete/(:num)', 'UserManagement::delete/$1', ['filter' => 'auth:admin']);
$routes->post('/user-management/destroy/(:num)', 'UserManagement::destroy/$1', ['filter' => 'auth:admin']);

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
