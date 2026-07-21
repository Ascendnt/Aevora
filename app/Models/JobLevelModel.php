<?php

namespace App\Models;

use CodeIgniter\Model;

class JobLevelModel extends Model
{
    protected $table         = 'job_levels';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['company_id', 'name', 'sort_order'];
    protected $useTimestamps = true;
    protected $validationRules = ['name' => 'required|min_length[2]|max_length[100]'];

    /** Job levels with their company name attached, optionally scoped to one company. */
    public function withCompany(?int $companyId = null): array
    {
        $builder = $this->db->table('job_levels jl')
            ->select('jl.*, c.name AS company_name')
            ->join('companies c', 'c.id = jl.company_id')
            ->orderBy('c.name')->orderBy('jl.sort_order')->orderBy('jl.name');

        if ($companyId !== null) {
            $builder->where('jl.company_id', $companyId);
        }

        return $builder->get()->getResultArray();
    }
}
