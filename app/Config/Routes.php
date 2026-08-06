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