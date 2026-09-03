<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
//authentication Routes
$routes->get('/', 'Auth::index');
$routes->get('/login', 'Auth::index');
$routes->post('/login/attempt', 'Auth::attemptLogin');
$routes->get('/logout', 'Auth::logout');
$routes->post('/session/activity', 'Auth::activity', ['filter' => 'auth']);

//Dashboard Protected Route (Menggunakan Filter Auth Guard)
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'auth']);
$routes->get('/kinerja-tim', 'TeamPerformance::index', ['filter' => 'auth']);

$routes->group('profile', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Profile::index');
    $routes->get('edit', 'Profile::edit');
    $routes->post('update', 'Profile::update');
    $routes->post('change-password', 'Profile::changePassword');
});

$routes->group('aplikasi', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Application::index');
    $routes->get('export/excel', 'Application::exportExcel');
    $routes->get('export/pdf', 'Application::exportPdf');
    $routes->get('create', 'Application::create');
    $routes->post('store', 'Application::store');
    $routes->get('detail/(:num)', 'Application::detail/$1');
    $routes->get('edit/(:num)', 'Application::edit/$1');
    $routes->post('update/(:num)', 'Application::update/$1');
    $routes->post('delete/(:num)', 'Application::delete/$1');
});

$routes->group('projects', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Projects::index');
    $routes->get('export/excel', 'Projects::exportExcel');
    $routes->get('export/pdf', 'Projects::exportPdf');
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
    $routes->get('create', 'Users::create');
    $routes->post('store', 'Users::store');
    $routes->get('detail/(:num)', 'Users::detail/$1');
    $routes->get('edit/(:num)', 'Users::edit/$1');
    $routes->post('update/(:num)', 'Users::update/$1');
    $routes->post('reset-password/(:num)', 'Users::resetPassword/$1');
    $routes->post('activate/(:num)', 'Users::activate/$1');
    $routes->post('deactivate/(:num)', 'Users::deactivate/$1');
});
