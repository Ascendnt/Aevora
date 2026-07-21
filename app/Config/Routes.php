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

    // Company settings (module: company_settings)
    $routes->group('', ['filter' => 'module:company_settings'], static function (RouteCollection $routes) {
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
    });

    // Employees directory (module: employees)
    $routes->get('employees', 'Employees::index', ['filter' => 'module:employees']);

    // Employee management (module: employee_management)
    $routes->group('employee-management', ['filter' => 'module:employee_management'], static function (RouteCollection $routes) {
        $routes->get('/', 'EmployeeManagement::index');
        $routes->get('new', 'EmployeeManagement::new');
        $routes->post('/', 'EmployeeManagement::create');
        $routes->get('(:num)/edit', 'EmployeeManagement::edit/$1');
        $routes->post('(:num)', 'EmployeeManagement::update/$1');
        $routes->post('(:num)/reset-password', 'EmployeeManagement::resetPassword/$1');
        $routes->post('(:num)/toggle-status', 'EmployeeManagement::toggleStatus/$1');
    });

    // Access profiles — superadmin-only, checked inside the controller.
    $routes->group('access-profiles', static function (RouteCollection $routes) {
        $routes->get('/', 'AccessProfiles::index');
        $routes->get('new', 'AccessProfiles::new');
        $routes->post('/', 'AccessProfiles::create');
        $routes->get('(:num)/edit', 'AccessProfiles::edit/$1');
        $routes->post('(:num)', 'AccessProfiles::update/$1');
        $routes->post('(:num)/delete', 'AccessProfiles::delete/$1');
    });

    // Placeholder modules (not built yet)
    $routes->get('attendance', 'Pages::attendance', ['filter' => 'module:time_attendance']);
    $routes->get('leave', 'Pages::leave', ['filter' => 'module:leave']);
    $routes->get('payroll', 'Pages::payroll', ['filter' => 'module:payroll']);
});
