<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table         = 'employees';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'user_id', 'company_id', 'branch_id', 'department_id', 'position_id',
        'access_profile_id', 'employee_number', 'status', 'hire_date',
        // Onboarding/profile enhancements: org structure, pay basis, approval routing.
        'date_of_birth', 'supervisor_id', 'job_level_id', 'employee_rank_id',
        'approval_level', 'basic_pay', 'pay_frequency', 'is_minimum_wage_earner',
        'work_schedule_id', 'phone', 'address', 'emergency_contact_name', 'emergency_contact_phone',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'user_id'    => 'required|is_natural_no_zero',
        'company_id' => 'required|is_natural_no_zero',
        'status'     => 'required|in_list[active,inactive]',
    ];

    /**
     * Employee list joined with the display fields views need, optionally
     * scoped to a single company (pass null for "all companies").
     */
    public function withDetails(?int $companyId = null): array
    {
        $builder = $this->db->table('employees e')
            ->select("e.*, u.name AS user_name, u.email AS user_email,
                      c.name AS company_name, b.name AS branch_name,
                      d.name AS department_name, p.title AS position_title,
                      ap.name AS access_profile_name")
            ->join('users u', 'u.id = e.user_id')
            ->join('companies c', 'c.id = e.company_id')
            ->join('branches b', 'b.id = e.branch_id', 'left')
            ->join('departments d', 'd.id = e.department_id', 'left')
            ->join('positions p', 'p.id = e.position_id', 'left')
            ->join('access_profiles ap', 'ap.id = e.access_profile_id', 'left')
            ->orderBy('u.name');

        if ($companyId !== null) {
            $builder->where('e.company_id', $companyId);
        }

        return $builder->get()->getResultArray();
    }

    public function findWithDetails(int $id): ?array
    {
        $row = $this->db->table('employees e')
            ->select("e.*, u.name AS user_name, u.email AS user_email, c.name AS company_name, b.name AS branch_name,
                      d.name AS department_name, p.title AS position_title, jl.name AS job_level_name,
                      er.name AS employee_rank_name, ws.name AS work_schedule_name, ap.name AS access_profile_name,
                      su.name AS supervisor_name")
            ->join('users u', 'u.id = e.user_id')
            ->join('companies c', 'c.id = e.company_id')
            ->join('branches b', 'b.id = e.branch_id', 'left')
            ->join('departments d', 'd.id = e.department_id', 'left')
            ->join('positions p', 'p.id = e.position_id', 'left')
            ->join('job_levels jl', 'jl.id = e.job_level_id', 'left')
            ->join('employee_ranks er', 'er.id = e.employee_rank_id', 'left')
            ->join('work_schedules ws', 'ws.id = e.work_schedule_id', 'left')
            ->join('access_profiles ap', 'ap.id = e.access_profile_id', 'left')
            ->join('employees se', 'se.id = e.supervisor_id', 'left')
            ->join('users su', 'su.id = se.user_id', 'left')
            ->where('e.id', $id)
            ->get()->getRowArray();

        return $row ?: null;
    }

    /** Employees currently assigned a given access profile (for the profile's own admin page). */
    public function byAccessProfile(int $accessProfileId): array
    {
        return $this->db->table('employees e')
            ->select('e.id, e.company_id, u.name AS user_name, c.name AS company_name')
            ->join('users u', 'u.id = e.user_id')
            ->join('companies c', 'c.id = e.company_id')
            ->where('e.access_profile_id', $accessProfileId)
            ->orderBy('u.name')
            ->get()->getResultArray();
    }

    public function findByUserId(int $userId): ?array
    {
        return $this->where('user_id', $userId)->first();
    }

    /** Module keys individually granted to this employee, beyond their profile. */
    public function individualModules(int $employeeId): array
    {
        return array_column(
            $this->db->table('employee_module_access')->select('module_key')->where('employee_id', $employeeId)->get()->getResultArray(),
            'module_key',
        );
    }

    public function setIndividualModules(int $employeeId, array $moduleKeys): void
    {
        $this->db->table('employee_module_access')->where('employee_id', $employeeId)->delete();

        $rows = array_map(static fn ($key) => ['employee_id' => $employeeId, 'module_key' => $key], $moduleKeys);

        if ($rows !== []) {
            $this->db->table('employee_module_access')->insertBatch($rows);
        }
    }
}
