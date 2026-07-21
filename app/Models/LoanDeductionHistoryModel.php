<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Ledger of which payslips already deducted a given loan. Rows are written
 * only when a payroll run is finalized (never for a live draft preview),
 * so this table doubles as the "has this loan already hit real payroll?"
 * check before letting HR edit/delete it.
 */
class LoanDeductionHistoryModel extends Model
{
    protected $table         = 'loan_deduction_history';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['loan_id', 'payslip_id', 'amount_deducted', 'deducted_at'];
    protected $useTimestamps = false;

    public function countForLoan(int $loanId): int
    {
        return $this->where('loan_id', $loanId)->countAllResults();
    }
}
