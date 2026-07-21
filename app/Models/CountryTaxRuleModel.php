<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * country_tax_rules is keyed by country_code (no surrogate id) — one row
 * per supported country, e.g. how many pay periods/year to annualize
 * against and whether minimum-wage earners are exempt from withholding.
 */
class CountryTaxRuleModel extends Model
{
    protected $table         = 'country_tax_rules';
    protected $primaryKey    = 'country_code';
    protected $useAutoIncrement = false;
    protected $allowedFields = [
        'country_code', 'currency_code', 'pay_periods_per_year_default',
        'exempt_minimum_wage_earners', 'notes',
    ];
    protected $useTimestamps = true;

    /** Returns a safe default (12 pay periods/year, no MWE exemption) when a country has no seeded row. */
    public function findOrDefault(string $countryCode): array
    {
        $row = $this->find($countryCode);

        return $row ?? [
            'country_code'                 => $countryCode,
            'currency_code'                => '',
            'pay_periods_per_year_default' => 12,
            'exempt_minimum_wage_earners'  => false,
            'notes'                        => 'No country_tax_rules row seeded for this country — defaulted to 12 pay periods/year, no minimum-wage exemption. Add a row before relying on this for a real payroll run.',
        ];
    }
}
