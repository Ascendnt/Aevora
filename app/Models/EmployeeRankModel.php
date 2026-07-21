<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeRankModel extends Model
{
    protected $table         = 'employee_ranks';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['company_id', 'name', 'is_exempt', 'sort_order'];
    protected $useTimestamps = true;
    protected $validationRules = ['name' => 'required|min_length[2]|max_length[100]'];

    /** Employee ranks with their company name attached, optionally scoped to one company. */
    public function withCompany(?int $companyId = null): array
    {
        $builder = $this->db->table('employee_ranks er')
            ->select('er.*, c.name AS company_name')
            ->join('companies c', 'c.id = er.company_id')
            ->orderBy('c.name')->orderBy('er.sort_order')->orderBy('er.name');

        if ($companyId !== null) {
            $builder->where('er.company_id', $companyId);
        }

        return $builder->get()->getResultArray();
    }
}
