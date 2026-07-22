<?php

namespace App\Models;

use CodeIgniter\Model;

class HolidayModel extends Model
{
    protected $table         = 'holidays';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'company_id', 'name', 'date', 'holiday_type', 'scope_type', 'scope_value',
        'branch_id', 'employee_id', 'source', 'external_ref',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'company_id'   => 'required|is_natural_no_zero',
        'name'         => 'required|min_length[2]|max_length[190]',
        'date'         => 'required|valid_date',
        'holiday_type' => 'required|in_list[legal,special]',
        'scope_type'   => 'required|in_list[national,regional,local,branch,employee]',
    ];

    protected $validationMessages = [
        'company_id' => ['required' => 'Please choose a company.', 'is_natural_no_zero' => 'Please choose a company.'],
        'name'       => ['required' => 'Holiday name is required.'],
        'date'       => ['required' => 'Date is required.'],
    ];

    /**
     * Holidays joined with their company name, optionally scoped to one
     * company and/or one calendar year.
     */
    public function withCompany(?int $companyId = null, ?int $year = null): array
    {
        $builder = $this->db->table('holidays h')
            ->select("h.*, c.name AS company_name, b.name AS branch_name, u.name AS employee_name")
            ->join('companies c', 'c.id = h.company_id')
            ->join('branches b', 'b.id = h.branch_id', 'left')
            ->join('employees e', 'e.id = h.employee_id', 'left')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->orderBy('h.date');

        if ($companyId !== null) {
            $builder->where('h.company_id', $companyId);
        }
        if ($year !== null) {
            $builder->where('h.date >=', $year . '-01-01')->where('h.date <=', $year . '-12-31');
        }

        return $builder->get()->getResultArray();
    }

    /** Used to skip duplicates on re-sync: does this company already have a holiday with this external_ref? */
    public function externalRefExists(int $companyId, string $externalRef): bool
    {
        return (bool) $this->where('company_id', $companyId)->where('external_ref', $externalRef)->first();
    }

    /** Bulk-remove every API-synced holiday for a company/year — the "undo an accidental wrong-country sync" escape hatch. */
    public function deleteSyncedForYear(int $companyId, int $year): int
    {
        $rows = $this->where('company_id', $companyId)
            ->where('source', 'api_import')
            ->where('date >=', $year . '-01-01')
            ->where('date <=', $year . '-12-31')
            ->findAll();

        if ($rows === []) {
            return 0;
        }

        $this->whereIn('id', array_column($rows, 'id'))->delete();

        return count($rows);
    }
}
