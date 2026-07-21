<?php

namespace App\Models;

use CodeIgniter\Model;

class PayslipModel extends Model
{
    protected $table         = 'payslips';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'payroll_run_id', 'employee_id', 'basic_pay', 'days_worked', 'late_minutes',
        'undertime_minutes', 'absence_days', 'late_deduction', 'undertime_deduction',
        'absence_deduction', 'gross_pay', 'taxable_income', 'tax_withheld',
        'statutory_deductions', 'benefits_total', 'loan_deductions_total',
        'total_deductions', 'net_pay', 'computed_at',
    ];
    protected $useTimestamps = true;

    /** Locked payslips for a finalized/paid run, with employee display info, for viewing/exporting. */
    public function byRun(int $runId): array
    {
        return $this->db->table('payslips p')
            ->select("p.*, u.name AS employee_name, e.employee_number")
            ->join('employees e', 'e.id = p.employee_id')
            ->join('users u', 'u.id = e.user_id')
            ->where('p.payroll_run_id', $runId)
            ->orderBy('u.name')
            ->get()->getResultArray();
    }

    public function forEmployeeInRun(int $runId, int $employeeId): ?array
    {
        return $this->where('payroll_run_id', $runId)->where('employee_id', $employeeId)->first();
    }
}
