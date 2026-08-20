<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
//authentication Routes
$routes->get('/', 'Auth::index');
$routes->get('/login', 'Auth::index');
$routes->post('/login/attempt', 'Auth::attemptLogin');
$routes->get('/logout', 'Auth::logout');

//Dashboard Protected Route (Menggunakan Filter Auth Guard)
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'auth']);

$routes->group('aplikasi', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Application::index');
});

$routes->group('projects', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Projects::index');
    $routes->get('user/(:num)', 'Projects::user/$1');
    $routes->get('create', 'Projects::create');
    $routes->get('detail/(:segment)', 'Projects::detail/$1');
    $routes->get('edit/(:segment)', 'Projects::edit/$1');
    $routes->post('store', 'Projects::store');
    $routes->post('update/(:segment)', 'Projects::update/$1');
    $routes->post('update-progress/(:segment)', 'Projects::updateProgress/$1');
    $routes->post('delete/(:segment)', 'Projects::delete/$1');
    $routes->get('files/(:segment)/download', 'Projects::downloadFile/$1');
    $routes->post('files/delete/(:segment)', 'Projects::deleteFile/$1');
});

$routes->group('users', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Users::index');
});
