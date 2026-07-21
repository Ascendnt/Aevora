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
        $routes->get('import', 'EmployeeManagement::importForm');
        $routes->post('import', 'EmployeeManagement::import');
        $routes->get('import/template', 'EmployeeManagement::importTemplate');
        $routes->get('(:num)/edit', 'EmployeeManagement::edit/$1');
        $routes->post('(:num)', 'EmployeeManagement::update/$1');
        $routes->post('(:num)/reset-password', 'EmployeeManagement::resetPassword/$1');
        $routes->post('(:num)/toggle-status', 'EmployeeManagement::toggleStatus/$1');
        $routes->get('(:num)/documents', 'EmployeeDocuments::index/$1');
        $routes->post('(:num)/documents/generate', 'EmployeeDocuments::generate/$1');
        $routes->post('(:num)/documents/upload', 'EmployeeDocuments::upload/$1');
    });

    $routes->group('job-levels', ['filter' => 'module:employee_management'], static function (RouteCollection $routes) {
        $routes->get('/', 'JobLevels::index');
        $routes->get('new', 'JobLevels::new');
        $routes->post('/', 'JobLevels::create');
        $routes->get('(:num)/edit', 'JobLevels::edit/$1');
        $routes->post('(:num)', 'JobLevels::update/$1');
        $routes->post('(:num)/delete', 'JobLevels::delete/$1');
    });

    $routes->group('employee-ranks', ['filter' => 'module:employee_management'], static function (RouteCollection $routes) {
        $routes->get('/', 'EmployeeRanks::index');
        $routes->get('new', 'EmployeeRanks::new');
        $routes->post('/', 'EmployeeRanks::create');
        $routes->get('(:num)/edit', 'EmployeeRanks::edit/$1');
        $routes->post('(:num)', 'EmployeeRanks::update/$1');
        $routes->post('(:num)/delete', 'EmployeeRanks::delete/$1');
    });

    // Documents (module: documents)
    $routes->group('document-templates', ['filter' => 'module:documents'], static function (RouteCollection $routes) {
        $routes->get('/', 'DocumentTemplates::index');
        $routes->get('new', 'DocumentTemplates::new');
        $routes->post('/', 'DocumentTemplates::create');
        $routes->get('(:num)/edit', 'DocumentTemplates::edit/$1');
        $routes->post('(:num)', 'DocumentTemplates::update/$1');
        $routes->post('(:num)/delete', 'DocumentTemplates::delete/$1');
    });

    $routes->group('documents', ['filter' => 'module:documents'], static function (RouteCollection $routes) {
        $routes->get('(:num)/view', 'EmployeeDocuments::view/$1');
        $routes->post('(:num)/status', 'EmployeeDocuments::updateStatus/$1');
        $routes->post('(:num)/delete', 'EmployeeDocuments::delete/$1');
    });

    // Filings (module: filings) — replaces the old "leave" placeholder
    $routes->group('filings', ['filter' => 'module:filings'], static function (RouteCollection $routes) {
        $routes->get('/', 'Filings::index');
        $routes->get('new', 'Filings::new');
        $routes->post('/', 'Filings::create');
        $routes->get('my-approvals', 'Filings::myApprovals');
        $routes->post('(:num)/decide', 'Filings::decide/$1');
    });

    $routes->group('leave-types', ['filter' => 'module:filings'], static function (RouteCollection $routes) {
        $routes->get('/', 'LeaveTypes::index');
        $routes->get('new', 'LeaveTypes::new');
        $routes->post('/', 'LeaveTypes::create');
        $routes->get('import', 'LeaveTypes::importForm');
        $routes->post('import', 'LeaveTypes::import');
        $routes->get('(:num)/edit', 'LeaveTypes::edit/$1');
        $routes->post('(:num)', 'LeaveTypes::update/$1');
        $routes->post('(:num)/delete', 'LeaveTypes::delete/$1');
    });

    // Time & attendance (module: time_attendance) — replaces the old placeholder
    $routes->group('attendance', ['filter' => 'module:time_attendance'], static function (RouteCollection $routes) {
        $routes->get('/', 'TimeAttendance::index');
        $routes->post('clock-in', 'TimeAttendance::clockIn');
        $routes->post('clock-out', 'TimeAttendance::clockOut');
    });

    $routes->group('work-schedules', ['filter' => 'module:time_attendance'], static function (RouteCollection $routes) {
        $routes->get('/', 'WorkSchedules::index');
        $routes->get('new', 'WorkSchedules::new');
        $routes->post('/', 'WorkSchedules::create');
        $routes->get('(:num)/edit', 'WorkSchedules::edit/$1');
        $routes->post('(:num)', 'WorkSchedules::update/$1');
        $routes->post('(:num)/delete', 'WorkSchedules::delete/$1');
    });

    $routes->group('holidays', ['filter' => 'module:time_attendance'], static function (RouteCollection $routes) {
        $routes->get('/', 'Holidays::index');
        $routes->get('new', 'Holidays::new');
        $routes->post('/', 'Holidays::create');
        $routes->post('sync', 'Holidays::syncFromApi');
        $routes->get('(:num)/edit', 'Holidays::edit/$1');
        $routes->post('(:num)', 'Holidays::update/$1');
        $routes->post('(:num)/delete', 'Holidays::delete/$1');
    });

    $routes->group('cutoff-schedules', ['filter' => 'module:time_attendance'], static function (RouteCollection $routes) {
        $routes->get('/', 'CutoffSchedules::index');
        $routes->get('new', 'CutoffSchedules::new');
        $routes->post('/', 'CutoffSchedules::create');
        $routes->get('(:num)/edit', 'CutoffSchedules::edit/$1');
        $routes->post('(:num)', 'CutoffSchedules::update/$1');
        $routes->post('(:num)/delete', 'CutoffSchedules::delete/$1');
    });

    // Payroll (module: payroll) — replaces the old placeholder
    $routes->group('payroll', ['filter' => 'module:payroll'], static function (RouteCollection $routes) {
        $routes->get('/', 'Payroll::dashboard');
        $routes->get('runs', 'Payroll::runs');
        $routes->get('runs/new', 'Payroll::newRun');
        $routes->post('runs', 'Payroll::createRun');
        $routes->get('runs/(:num)', 'Payroll::viewRun/$1');
        $routes->post('runs/(:num)/finalize', 'Payroll::finalizeRun/$1');
        $routes->get('runs/(:num)/export', 'Payroll::export/$1');
        $routes->get('benefits', 'Payroll::benefits');
        $routes->get('benefits/new', 'Payroll::newBenefit');
        $routes->post('benefits', 'Payroll::createBenefit');
        $routes->get('benefits/(:num)/edit', 'Payroll::editBenefit/$1');
        $routes->post('benefits/(:num)', 'Payroll::updateBenefit/$1');
        $routes->post('benefits/(:num)/delete', 'Payroll::deleteBenefit/$1');
        $routes->get('loans', 'Payroll::loansIndex');
        $routes->get('loans/new', 'Payroll::newLoan');
        $routes->post('loans', 'Payroll::createLoan');
        $routes->get('loans/(:num)/edit', 'Payroll::editLoan/$1');
        $routes->post('loans/(:num)', 'Payroll::updateLoan/$1');
        $routes->post('loans/(:num)/delete', 'Payroll::deleteLoan/$1');
        $routes->get('import', 'Payroll::importForm');
        $routes->post('import', 'Payroll::import');
    });

    // Notifications & AI assistant placeholder — no module filter, available to every logged-in user
    $routes->get('notifications', 'Notifications::index');
    $routes->get('notifications/unread-count', 'Notifications::unreadCount');
    $routes->post('notifications/(:num)/mark-read', 'Notifications::markRead/$1');
    $routes->post('assistant/ask', 'Assistant::ask');

    // Access profiles — superadmin-only, checked inside the controller.
    $routes->group('access-profiles', static function (RouteCollection $routes) {
        $routes->get('/', 'AccessProfiles::index');
        $routes->get('new', 'AccessProfiles::new');
        $routes->post('/', 'AccessProfiles::create');
        $routes->get('(:num)/edit', 'AccessProfiles::edit/$1');
        $routes->post('(:num)', 'AccessProfiles::update/$1');
        $routes->post('(:num)/delete', 'AccessProfiles::delete/$1');
    });
});
