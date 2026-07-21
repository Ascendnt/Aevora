<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeBenefitModel extends Model
{
    protected $table         = 'employee_benefits';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'employee_id', 'benefit_type', 'amount', 'is_recurring', 'effective_date', 'end_date', 'notes',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'employee_id'     => 'required|is_natural_no_zero',
        'benefit_type'    => 'required|max_length[100]',
        'amount'          => 'required|numeric',
        'effective_date'  => 'required|valid_date',
    ];

    /** List for display, scoped to a company (or all companies for superadmin). */
    public function withEmployee(?int $companyId = null): array
    {
        $builder = $this->db->table('employee_benefits b')
            ->select('b.*, u.name AS employee_name, e.company_id, c.name AS company_name')
            ->join('employees e', 'e.id = b.employee_id')
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
        $row = $this->db->table('employee_benefits b')
            ->select('b.*, u.name AS employee_name, e.company_id')
            ->join('employees e', 'e.id = b.employee_id')
            ->join('users u', 'u.id = e.user_id')
            ->where('b.id', $id)
            ->get()->getRowArray();

        return $row ?: null;
    }

    /**
     * Benefits considered "active" for a payroll period: recurring ones (they apply every
     * period until ended), or one-off ones whose effective_date falls inside this period.
     * A recurring benefit that has already ended before the period starts is excluded.
     */
    public function activeForPeriod(int $employeeId, string $periodStart, string $periodEnd): array
    {
        return $this->where('employee_id', $employeeId)
            ->groupStart()
                ->where('end_date', null)
                ->orWhere('end_date >=', $periodStart)
            ->groupEnd()
            ->groupStart()
                ->where('is_recurring', true)
                ->orGroupStart()
                    ->where('effective_date >=', $periodStart)
                    ->where('effective_date <=', $periodEnd)
                ->groupEnd()
            ->groupEnd()
            ->findAll();
    }
}
