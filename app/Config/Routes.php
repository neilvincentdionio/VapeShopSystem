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
$routes->get('/dashboard/settings', 'Dashboard::settings', ['filter' => 'auth:admin']);

// Records module routes (Task 3)
$routes->group('records', ['filter' => 'auth:admin'], static function ($routes) {
    $routes->get('/', 'Records::index');
    $routes->get('create', 'Records::create');
    $routes->post('store', 'Records::store');
    $routes->get('edit/(:num)', 'Records::edit/$1');
    $routes->post('update/(:num)', 'Records::update/$1');
    $routes->post('delete/(:num)', 'Records::delete/$1', ['filter' => 'auth:admin']);
});
