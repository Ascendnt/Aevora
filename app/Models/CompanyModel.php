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
        'business_permit_number', 'rdo_code',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'name'  => 'required|min_length[2]|max_length[190]',
        'email' => 'permit_empty|valid_email',
    ];

    protected $validationMessages = [
        'name' => ['required' => 'Company name is required.'],
    ];

    /** Companies with their branch counts and HQ city, for the list view. */
    public function withBranchCounts(): array
    {
        return $this->db->table('companies c')
            ->select("c.*, COALESCE(b.cnt, 0) AS branch_count, hq.city AS hq_city")
            ->join('(SELECT company_id, COUNT(*) AS cnt FROM branches GROUP BY company_id) b', 'b.company_id = c.id', 'left')
            ->join('branches hq', 'hq.company_id = c.id AND hq.is_hq', 'left')
            ->orderBy('c.name')
            ->get()->getResultArray();
    }
}
