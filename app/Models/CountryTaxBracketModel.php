<?php

namespace App\Models;

use CodeIgniter\Model;

class CountryTaxBracketModel extends Model
{
    protected $table         = 'country_tax_brackets';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'country_code', 'bracket_order', 'min_amount', 'max_amount',
        'base_tax', 'rate_percent', 'effective_year',
    ];
    protected $useTimestamps = false;

    /**
     * The bracket that covers a given ANNUALIZED income for a country, using the
     * latest effective_year on file that is <= $asOfYear (so future-dated bracket
     * changes don't apply early, and we don't need a bracket row for every year).
     */
    public function findBracket(string $countryCode, float $annualizedIncome, int $asOfYear): ?array
    {
        $year = $this->where('country_code', $countryCode)
            ->where('effective_year <=', $asOfYear)
            ->selectMax('effective_year')
            ->get()->getRowArray()['effective_year'] ?? null;

        if ($year === null) {
            return null;
        }

        return $this->where('country_code', $countryCode)
            ->where('effective_year', $year)
            ->where('min_amount <=', $annualizedIncome)
            ->groupStart()
                ->where('max_amount', null)
                ->orWhere('max_amount >=', $annualizedIncome)
            ->groupEnd()
            ->orderBy('bracket_order', 'DESC')
            ->get()->getRowArray();
    }
}
