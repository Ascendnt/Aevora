<?php

namespace App\Models;

use CodeIgniter\Model;

class WorkScheduleModel extends Model
{
    protected $table         = 'work_schedules';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'company_id', 'name', 'time_in', 'time_out', 'grace_minutes', 'break_minutes', 'schedule_type',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'company_id'    => 'required|is_natural_no_zero',
        'name'          => 'required|min_length[2]|max_length[100]',
        'time_in'       => 'required',
        'time_out'      => 'required',
        'grace_minutes' => 'permit_empty|is_natural',
        'schedule_type' => 'required|in_list[fixed,shifting,executive]',
    ];

    protected $validationMessages = [
        'company_id' => ['required' => 'Please choose a company.', 'is_natural_no_zero' => 'Please choose a company.'],
        'name'       => ['required' => 'Schedule name is required.'],
        'time_in'    => ['required' => 'Time in is required.'],
        'time_out'   => ['required' => 'Time out is required.'],
    ];

    /**
     * Work schedules joined with their company name, optionally scoped to
     * one company (pass null to see all — used for the superadmin view).
     */
    public function withCompany(?int $companyId = null): array
    {
        $builder = $this->db->table('work_schedules ws')
            ->select('ws.*, c.name AS company_name')
            ->join('companies c', 'c.id = ws.company_id')
            ->orderBy('c.name')->orderBy('ws.name');

        if ($companyId !== null) {
            $builder->where('ws.company_id', $companyId);
        }

        return $builder->get()->getResultArray();
    }
}
