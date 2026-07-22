<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * An employee's self-service request to change a handful of their own
 * contact fields (phone, address, emergency contact, date of birth) —
 * everything else on the profile stays HR-managed only. requested_changes
 * is stored as JSON: {"field": {"from": "...", "to": "..."}, ...}.
 */
class EmployeeProfileChangeRequestModel extends Model
{
    protected $table         = 'employee_profile_change_requests';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'employee_id', 'requested_changes', 'employee_note', 'status',
        'reviewed_by_user_id', 'review_note', 'reviewed_at',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'employee_id'       => 'required|is_natural_no_zero',
        'requested_changes' => 'required',
        'status'            => 'permit_empty|in_list[pending,approved,rejected]',
    ];

    public function pendingForEmployee(int $employeeId): ?array
    {
        return $this->where('employee_id', $employeeId)->where('status', 'pending')->first();
    }

    public function historyForEmployee(int $employeeId): array
    {
        return $this->where('employee_id', $employeeId)->orderBy('created_at', 'DESC')->findAll();
    }

    /** Pending requests across a company (or all companies for superadmin), newest first. */
    public function pendingWithDetails(?int $companyId = null): array
    {
        $builder = $this->db->table('employee_profile_change_requests r')
            ->select('r.*, u.name AS employee_name, e.company_id, c.name AS company_name')
            ->join('employees e', 'e.id = r.employee_id')
            ->join('users u', 'u.id = e.user_id')
            ->join('companies c', 'c.id = e.company_id')
            ->where('r.status', 'pending')
            ->orderBy('r.created_at', 'ASC');

        if ($companyId !== null) {
            $builder->where('e.company_id', $companyId);
        }

        return $builder->get()->getResultArray();
    }

    public function pendingCount(?int $companyId = null): int
    {
        $builder = $this->db->table('employee_profile_change_requests r')
            ->join('employees e', 'e.id = r.employee_id')
            ->where('r.status', 'pending');

        if ($companyId !== null) {
            $builder->where('e.company_id', $companyId);
        }

        return $builder->countAllResults();
    }
}
