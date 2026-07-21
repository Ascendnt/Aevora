<?php

namespace App\Controllers;

use App\Models\EmployeeModel;

class Employees extends BaseController
{
    public function index()
    {
        return view('employees/index', [
            'title'     => 'Employees',
            'active'    => 'employees',
            'employees' => (new EmployeeModel())->withDetails(scoped_company_id()),
        ]);
    }
}
