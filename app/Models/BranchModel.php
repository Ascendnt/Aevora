<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchModel extends Model
{
    protected $table         = 'branches';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'company_id', 'name', 'code', 'is_hq', 'email', 'phone',
        'address_line', 'city', 'province', 'postal_code', 'country', 'status',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'company_id' => 'required|is_natural_no_zero',
        'name'       => 'required|min_length[2]|max_length[190]',
        'status'     => 'required|in_list[active,inactive]',
    ];

    protected $validationMessages = [
        'company_id' => ['required' => 'Please choose a company.', 'is_natural_no_zero' => 'Please choose a company.'],
        'name'       => ['required' => 'Branch name is required.'],
    ];

    /** Only one HQ per company: clear the flag on siblings. */
    public function makeHq(int $companyId, ?int $exceptBranchId = null): void
    {
        $builder = $this->builder()->where('company_id', $companyId);
        if ($exceptBranchId !== null) {
            $builder->where('id !=', $exceptBranchId);
        }
        $builder->update(['is_hq' => false]);
    }

    public function withCompany(?int $companyId = null): array
    {
        $builder = $this->db->table('branches b')
            ->select('b.*, c.name AS company_name')
            ->join('companies c', 'c.id = b.company_id')
            ->orderBy('c.name')->orderBy('b.is_hq', 'DESC')->orderBy('b.name');

        if ($companyId) {
            $builder->where('b.company_id', $companyId);
        }

        return $builder->get()->getResultArray();
    }
}
