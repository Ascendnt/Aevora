<?php

namespace App\Models;

use CodeIgniter\Model;

/** Same pattern as LoanDeductionHistoryModel, for employee_benefits. */
class BenefitApplicationHistoryModel extends Model
{
    protected $table         = 'benefit_application_history';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['benefit_id', 'payslip_id', 'amount_applied', 'applied_at'];
    protected $useTimestamps = false;

    public function countForBenefit(int $benefitId): int
    {
        return $this->where('benefit_id', $benefitId)->countAllResults();
    }
}
