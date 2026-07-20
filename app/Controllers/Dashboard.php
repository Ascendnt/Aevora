<?php

namespace App\Controllers;

use App\Models\BranchModel;
use App\Models\CompanyModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $companies = new CompanyModel();
        $branches  = new BranchModel();

        return view('dashboard/index', [
            'title'         => 'Dashboard',
            'active'        => 'dashboard',
            'companyCount'  => $companies->countAllResults(),
            'branchCount'   => $branches->countAllResults(),
            // Hardcoded for now — will come from real modules later.
            'totalEmployees' => 128,
            'onLeaveToday'   => 5,
            'payrollRun'     => 'Jul 31',
        ]);
    }
}
