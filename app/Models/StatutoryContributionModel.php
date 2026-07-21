<?php

namespace App\Models;

use CodeIgniter\Model;

class StatutoryContributionModel extends Model
{
    protected $table         = 'statutory_contributions';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'country_code', 'name', 'employee_share_percent', 'employer_share_percent',
        'min_base', 'max_base', 'min_contribution', 'max_contribution', 'effective_year',
    ];
    protected $useTimestamps = false;

    /**
     * Every contribution row (SSS, PhilHealth, Superannuation, ...) that applies to a
     * country, using the latest effective_year on file that is <= $asOfYear. This is
     * the generic, data-driven list the payroll engine loops over — adding support for
     * a new country/contribution is a data insert, not new PHP.
     */
    public function forCountry(string $countryCode, int $asOfYear): array
    {
        $year = $this->where('country_code', $countryCode)
            ->where('effective_year <=', $asOfYear)
            ->selectMax('effective_year')
            ->get()->getRowArray()['effective_year'] ?? null;

        if ($year === null) {
            return [];
        }

        return $this->where('country_code', $countryCode)
            ->where('effective_year', $year)
            ->orderBy('name')
            ->findAll();
    }
}
