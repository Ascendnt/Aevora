<?php

namespace App\Models;

use CodeIgniter\Model;

class LeaveTypeModel extends Model
{
    protected $table         = 'leave_types';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['company_id', 'name', 'is_paid', 'filing_rule', 'min_days_notice'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'company_id'      => 'required|is_natural_no_zero',
        'name'            => 'required|min_length[2]|max_length[100]',
        'filing_rule'     => 'required|in_list[before,after]',
        'min_days_notice' => 'permit_empty|is_natural',
    ];

    protected $validationMessages = [
        'company_id'  => ['required' => 'Please choose a company.', 'is_natural_no_zero' => 'Please choose a company.'],
        'name'        => ['required' => 'Leave type name is required.'],
        'filing_rule' => ['in_list' => 'Filing rule must be either "before" or "after".'],
    ];

    /** Leave types with their company name, optionally scoped to a single company. */
    public function withCompany(?int $companyId = null): array
    {
        $builder = $this->db->table('leave_types lt')
            ->select('lt.*, c.name AS company_name')
            ->join('companies c', 'c.id = lt.company_id')
            ->orderBy('c.name')->orderBy('lt.name');

        if ($companyId !== null) {
            $builder->where('lt.company_id', $companyId);
        }

        return $builder->get()->getResultArray();
    }

    /** Used by the CSV import's create-or-update-by-name matching. */
    public function findByName(int $companyId, string $name): ?array
    {
        return $this->where('company_id', $companyId)->where('name', $name)->first();
    }
}
