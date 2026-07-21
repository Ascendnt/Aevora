<?php

namespace App\Controllers;

use App\Models\AccessProfileModel;
use App\Models\BranchModel;
use App\Models\CompanyModel;
use App\Models\EmployeeModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $scoped = scoped_company_id();

        $companyBuilder  = new CompanyModel();
        $branchBuilder   = new BranchModel();
        $employeeBuilder = new EmployeeModel();

        if ($scoped !== null) {
            $companyBuilder->where('id', $scoped);
            $branchBuilder->where('company_id', $scoped);
            $employeeBuilder->where('company_id', $scoped);
        }

        return view('dashboard/index', [
            'title'          => 'Dashboard',
            'active'         => 'dashboard',
            'companyCount'   => $companyBuilder->countAllResults(),
            'branchCount'    => $branchBuilder->countAllResults(),
            // Superadmin accounts have no employees row, so they're naturally excluded from this count.
            'totalEmployees' => $employeeBuilder->countAllResults(),
            // Leave/payroll aren't real modules yet — placeholders until those are built.
            'onLeaveToday'   => 0,
            'payrollRun'     => '—',
            'roleLabel'      => $this->roleLabel(),
            'companyLabel'   => $this->companyLabel(),
        ]);
    }

    private function roleLabel(): string
    {
        if (is_superadmin()) {
            return 'Superadmin';
        }

        $employee = current_employee();
        if (! $employee || ! $employee['access_profile_id']) {
            return 'No access profile assigned';
        }

        $profile = (new AccessProfileModel())->find($employee['access_profile_id']);

        return $profile['name'] ?? 'No access profile assigned';
    }

    private function companyLabel(): string
    {
        if (is_superadmin()) {
            return 'All companies';
        }

        $employee = current_employee();
        if (! $employee) {
            return '—';
        }

        $details = (new EmployeeModel())->findWithDetails((int) $employee['id']);
        $label   = $details['company_name'] ?? '—';

        if (! empty($details['branch_name'])) {
            $label .= ' · ' . $details['branch_name'];
        }

        return $label;
    }
}
