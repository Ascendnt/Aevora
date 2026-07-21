<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollRunModel extends Model
{
    protected $table         = 'payroll_runs';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'company_id', 'cutoff_schedule_id', 'period_start', 'period_end', 'pay_date', 'status',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'company_id'   => 'required|is_natural_no_zero',
        'period_start' => 'required|valid_date',
        'period_end'   => 'required|valid_date',
    ];

    /** Runs list for a company (or all companies for superadmin), most recent period first. */
    public function withCompany(?int $companyId = null): array
    {
        $builder = $this->db->table('payroll_runs pr')
            ->select('pr.*, c.name AS company_name')
            ->join('companies c', 'c.id = pr.company_id')
            ->orderBy('pr.period_start', 'DESC');

        if ($companyId !== null) {
            $builder->where('pr.company_id', $companyId);
        }

        return $builder->get()->getResultArray();
    }

    public function findWithCompany(int $id): ?array
    {
        $row = $this->db->table('payroll_runs pr')
            ->select('pr.*, c.name AS company_name, c.country_code')
            ->join('companies c', 'c.id = pr.company_id')
            ->where('pr.id', $id)
            ->get()->getRowArray();

        return $row ?: null;
    }

    /** The most recent draft run for a company, if one exists — used by the dashboard. */
    public function latestDraft(?int $companyId): ?array
    {
        $builder = $this->where('status', 'draft')->orderBy('period_start', 'DESC');

        if ($companyId !== null) {
            $builder->where('company_id', $companyId);
        }

        return $builder->first();
    }

    public function draftCount(?int $companyId): int
    {
        $builder = $this->where('status', 'draft');

        if ($companyId !== null) {
            $builder->where('company_id', $companyId);
        }

        return $builder->countAllResults();
    }
}
