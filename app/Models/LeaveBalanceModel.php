<?php

namespace App\Models;

use CodeIgniter\Model;

class LeaveBalanceModel extends Model
{
    protected $table         = 'leave_balances';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['employee_id', 'leave_type_id', 'year', 'entitled_days', 'used_days'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'employee_id'   => 'required|is_natural_no_zero',
        'leave_type_id' => 'required|is_natural_no_zero',
        'year'          => 'required|is_natural_no_zero',
    ];

    /**
     * Adds $days to an employee's used-days tally for a leave type/year,
     * creating the balance row (with a zero entitlement) if none exists yet.
     * Called when a leave filing is approved.
     */
    public function incrementUsed(int $employeeId, int $leaveTypeId, int $year, float $days): void
    {
        $existing = $this->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();

        if ($existing) {
            $this->update((int) $existing['id'], ['used_days' => (float) $existing['used_days'] + $days]);

            return;
        }

        $this->insert([
            'employee_id'   => $employeeId,
            'leave_type_id' => $leaveTypeId,
            'year'          => $year,
            'entitled_days' => 0,
            'used_days'     => $days,
        ]);
    }
}
