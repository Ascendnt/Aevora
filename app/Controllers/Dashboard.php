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
            'title'            => 'Dashboard',
            'active'           => 'dashboard',
            'companyCount'     => $companyBuilder->countAllResults(),
            'branchCount'      => $branchBuilder->countAllResults(),
            // Superadmin accounts have no employees row, so they're naturally excluded from this count.
            'totalEmployees'   => $employeeBuilder->countAllResults(),
            'birthdaysThisMonth' => $this->birthdaysThisMonth($scoped),
            'attendanceToday'  => $this->attendanceToday($scoped),
            'turnoverYtd'      => $this->turnoverYtd($scoped),
            'roleLabel'        => $this->roleLabel(),
            'companyLabel'     => $this->companyLabel(),
        ]);
    }

    /** Active employees whose date_of_birth falls in the current calendar month, soonest first. */
    private function birthdaysThisMonth(?int $companyId): array
    {
        $builder = db_connect()->table('employees e')
            ->select("e.id, u.name AS user_name, e.date_of_birth")
            ->join('users u', 'u.id = e.user_id')
            ->where('e.status', 'active')
            ->where('e.date_of_birth IS NOT NULL', null, false)
            ->where('EXTRACT(MONTH FROM e.date_of_birth) = ' . (int) date('n'), null, false);

        if ($companyId !== null) {
            $builder->where('e.company_id', $companyId);
        }

        $rows = $builder->get()->getResultArray();

        usort($rows, static fn ($a, $b) => ((int) date('j', strtotime($a['date_of_birth']))) <=> ((int) date('j', strtotime($b['date_of_birth']))));

        return $rows;
    }

    /** Today's clock-in picture: how many active employees have/haven't logged in yet, and how many are late. */
    private function attendanceToday(?int $companyId): array
    {
        $employeeModel = new EmployeeModel();
        if ($companyId !== null) {
            $employeeModel->where('company_id', $companyId);
        }
        $active = $employeeModel->where('status', 'active')->countAllResults();

        if ($active === 0) {
            return ['active' => 0, 'clockedIn' => 0, 'notYet' => 0, 'late' => 0];
        }

        $today = date('Y-m-d');
        $logBuilder = db_connect()->table('attendance_logs al')
            ->select('al.employee_id, al.time_in')
            ->join('employees e', 'e.id = al.employee_id')
            ->where('al.log_date', $today)
            ->where('e.status', 'active');

        if ($companyId !== null) {
            $logBuilder->where('e.company_id', $companyId);
        }

        $logs      = $logBuilder->get()->getResultArray();
        $clockedIn = count(array_filter($logs, static fn ($l) => ! empty($l['time_in'])));

        // "Late" here is a lightweight same-day signal (clocked in after 9am server-relative),
        // not the same authoritative late-minutes figure PayrollEngine computes per schedule.
        $late = count(array_filter($logs, static fn ($l) => ! empty($l['time_in']) && $l['time_in'] > '09:00:00'));

        return ['active' => $active, 'clockedIn' => $clockedIn, 'notYet' => max(0, $active - $clockedIn), 'late' => $late];
    }

    /**
     * Approximate year-to-date turnover: employees deactivated this year, as a
     * share of (currently active + those who left this year). This app has no
     * dedicated "terminated on" date — status flips to inactive via a simple
     * toggle — so this uses updated_at as a proxy, which is an approximation,
     * not an exact HR turnover figure.
     */
    private function turnoverYtd(?int $companyId): array
    {
        $yearStart = date('Y') . '-01-01';

        $inactiveBuilder = db_connect()->table('employees')
            ->where('status', 'inactive')
            ->where('updated_at >=', $yearStart);

        if ($companyId !== null) {
            $inactiveBuilder->where('company_id', $companyId);
        }

        $leftThisYear = $inactiveBuilder->countAllResults();

        $activeBuilder = db_connect()->table('employees')->where('status', 'active');
        if ($companyId !== null) {
            $activeBuilder->where('company_id', $companyId);
        }
        $activeNow = $activeBuilder->countAllResults();

        $population = $activeNow + $leftThisYear;
        $rate       = $population > 0 ? round(($leftThisYear / $population) * 100, 1) : 0.0;

        return ['left' => $leftThisYear, 'rate' => $rate];
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
