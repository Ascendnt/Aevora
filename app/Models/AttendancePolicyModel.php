<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendancePolicyModel extends Model
{
    protected $table         = 'attendance_policies';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'company_id', 'name', 'absent_before_holiday_forfeits_pay', 'absent_after_holiday_forfeits_pay',
        'consecutive_absence_alert_days', 'notes',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'company_id' => 'required|is_natural_no_zero',
        'name'       => 'required|min_length[2]|max_length[100]',
    ];

    public function withCompany(?int $companyId = null): array
    {
        $builder = $this->db->table('attendance_policies p')
            ->select('p.*, c.name AS company_name')
            ->join('companies c', 'c.id = p.company_id')
            ->orderBy('c.name')->orderBy('p.name');

        if ($companyId !== null) {
            $builder->where('p.company_id', $companyId);
        }

        return $builder->get()->getResultArray();
    }

    /** The effective policy for a company — first one defined, or null if HR hasn't set one up. */
    public function forCompany(int $companyId): ?array
    {
        return $this->where('company_id', $companyId)->orderBy('id')->first();
    }
}
