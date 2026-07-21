<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeLoanModel extends Model
{
    protected $table         = 'employee_loans';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'employee_id', 'loan_type', 'principal_amount', 'monthly_amortization',
        'balance_remaining', 'start_date', 'end_date', 'status', 'notes',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'employee_id'           => 'required|is_natural_no_zero',
        'loan_type'             => 'required|max_length[100]',
        'principal_amount'      => 'required|numeric',
        'monthly_amortization'  => 'required|numeric',
        'balance_remaining'     => 'required|numeric',
        'start_date'            => 'required|valid_date',
        'status'                => 'required|in_list[active,completed,cancelled]',
    ];

    /** List for display, scoped to a company (or all companies for superadmin). */
    public function withEmployee(?int $companyId = null): array
    {
        $builder = $this->db->table('employee_loans l')
            ->select('l.*, u.name AS employee_name, e.company_id, c.name AS company_name')
            ->join('employees e', 'e.id = l.employee_id')
            ->join('users u', 'u.id = e.user_id')
            ->join('companies c', 'c.id = e.company_id')
            ->orderBy('u.name');

        if ($companyId !== null) {
            $builder->where('e.company_id', $companyId);
        }

        return $builder->get()->getResultArray();
    }

    public function findWithEmployee(int $id): ?array
    {
        $row = $this->db->table('employee_loans l')
            ->select('l.*, u.name AS employee_name, e.company_id')
            ->join('employees e', 'e.id = l.employee_id')
            ->join('users u', 'u.id = e.user_id')
            ->where('l.id', $id)
            ->get()->getRowArray();

        return $row ?: null;
    }

    /** Active loans for an employee — the ones the payroll engine deducts from. */
    public function activeForEmployee(int $employeeId): array
    {
        return $this->where('employee_id', $employeeId)->where('status', 'active')->findAll();
    }
}
