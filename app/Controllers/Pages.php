<?php

namespace App\Controllers;

class Pages extends BaseController
{
    public function employees()
    {
        return view('pages/employees', ['title' => 'Employees', 'active' => 'employees']);
    }

    public function attendance()
    {
        return view('pages/attendance', ['title' => 'Time & attendance', 'active' => 'attendance']);
    }

    public function leave()
    {
        return view('pages/leave', ['title' => 'Leave', 'active' => 'leave']);
    }

    public function payroll()
    {
        return view('pages/payroll', ['title' => 'Payroll', 'active' => 'payroll']);
    }
}
