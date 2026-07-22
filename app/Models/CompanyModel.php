<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    protected $table         = 'companies';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'name', 'legal_name', 'tin', 'industry', 'email', 'phone', 'website',
        'address_line', 'city', 'province', 'postal_code', 'country',
        'logo_path', 'date_established', 'company_size', 'sec_dti_number',
        'sss_number', 'philhealth_number', 'pagibig_number',
        'business_permit_number', 'rdo_code', 'is_hq', 'country_code', 'max_approval_levels',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'name'  => 'required|min_length[2]|max_length[190]',
        'email' => 'permit_empty|valid_email',
    ];

    protected $validationMessages = [
        'name' => ['required' => 'Company name is required.'],
    ];

    /**
     * Companies with their branch counts and HQ city, for the list view.
     * Pass a company id to scope to just that one company (non-superadmin view).
     */
    public function withBranchCounts(?int $companyId = null): array
    {
        $builder = $this->db->table('companies c')
            ->select("c.*, COALESCE(b.cnt, 0) AS branch_count, hq.city AS hq_city")
            ->join('(SELECT company_id, COUNT(*) AS cnt FROM branches GROUP BY company_id) b', 'b.company_id = c.id', 'left')
            ->join('branches hq', 'hq.company_id = c.id AND hq.is_hq', 'left')
            ->orderBy('c.name');

        if ($companyId !== null) {
            $builder->where('c.id', $companyId);
        }

        return $builder->get()->getResultArray();
    }

    /** Only one HQ company system-wide: clear the flag on every other company. */
    public function makeHq(int $companyId): void
    {
        $this->builder()->where('id !=', $companyId)->update(['is_hq' => false]);
    }

    public function hqCompany(): ?array
    {
        return $this->where('is_hq', true)->first();
    }
}
