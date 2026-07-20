<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Landing::index');

// Auth
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attempt');
$routes->get('logout', 'Auth::logout');

// Protected app routes
$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->get('dashboard', 'Dashboard::index');

    // Company settings
    $routes->get('companies', 'Companies::index');
    $routes->get('companies/new', 'Companies::new');
    $routes->post('companies', 'Companies::create');
    $routes->get('companies/(:num)/edit', 'Companies::edit/$1');
    $routes->post('companies/(:num)', 'Companies::update/$1');
    $routes->post('companies/(:num)/delete', 'Companies::delete/$1');
    $routes->post('companies/(:num)/logo/delete', 'Companies::deleteLogo/$1');
    $routes->get('companies/(:num)/organization', 'Companies::organization/$1');
    $routes->post('companies/(:num)/departments', 'Companies::addDepartment/$1');
    $routes->post('departments/(:num)/delete', 'Companies::deleteDepartment/$1');
    $routes->post('companies/(:num)/positions', 'Companies::addPosition/$1');
    $routes->post('positions/(:num)/delete', 'Companies::deletePosition/$1');

    // Branches
    $routes->get('branches', 'Branches::index');
    $routes->get('branches/new', 'Branches::new');
    $routes->post('branches', 'Branches::create');
    $routes->get('branches/(:num)/edit', 'Branches::edit/$1');
    $routes->post('branches/(:num)', 'Branches::update/$1');
    $routes->post('branches/(:num)/delete', 'Branches::delete/$1');

    // Hardcoded modules (placeholders for now)
    $routes->get('employees', 'Pages::employees');
    $routes->get('attendance', 'Pages::attendance');
    $routes->get('leave', 'Pages::leave');
    $routes->get('payroll', 'Pages::payroll');
});