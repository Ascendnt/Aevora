<?php

namespace App\Models;

use CodeIgniter\Model;

class CutoffScheduleModel extends Model
{
    protected $table         = 'cutoff_schedules';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'company_id', 'scope_type', 'scope_id', 'frequency', 'period_config',
        'pay_date_offset_days', 'reminder_days_before',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'company_id' => 'required|is_natural_no_zero',
        'scope_type' => 'required|in_list[company,department,branch,employee]',
        'frequency'  => 'required|in_list[monthly,semi_monthly,weekly]',
    ];

    protected $validationMessages = [
        'company_id' => ['required' => 'Please choose a company.', 'is_natural_no_zero' => 'Please choose a company.'],
    ];

    public function withCompany(?int $companyId = null): array
    {
        $builder = $this->db->table('cutoff_schedules cs')
            ->select('cs.*, c.name AS company_name')
            ->join('companies c', 'c.id = cs.company_id')
            ->orderBy('c.name')->orderBy('cs.scope_type')->orderBy('cs.id');

        if ($companyId !== null) {
            $builder->where('cs.company_id', $companyId);
        }

        return $builder->get()->getResultArray();
    }
}
