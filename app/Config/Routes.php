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

// Records routes (protected by AuthFilter)
$routes->get('/records', 'Records::index', ['filter' => 'auth']);
$routes->get('/records/create', 'Records::create', ['filter' => 'auth']);
$routes->post('/records/store', 'Records::store', ['filter' => 'auth']);
$routes->get('/records/edit/(:num)', 'Records::edit/$1', ['filter' => 'auth']);
$routes->post('/records/update/(:num)', 'Records::update/$1', ['filter' => 'auth']);
$routes->post('/records/delete/(:num)', 'Records::delete/$1', ['filter' => 'auth']);
$routes->get('/records/search', 'Records::search', ['filter' => 'auth']);
