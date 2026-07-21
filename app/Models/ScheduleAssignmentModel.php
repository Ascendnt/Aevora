<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Per-date override of an employee's work schedule (for shifting employees).
 * The table only has a created_at column (no updated_at), so the updated
 * timestamp field is disabled — leaving it enabled would make every insert()
 * fail against Postgres with an "unknown column" error.
 */
class ScheduleAssignmentModel extends Model
{
    protected $table         = 'schedule_assignments';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['employee_id', 'date', 'work_schedule_id'];
    protected $useTimestamps = true;
    protected $updatedField  = '';

    protected $validationRules = [
        'employee_id'      => 'required|is_natural_no_zero',
        'date'             => 'required|valid_date',
        'work_schedule_id' => 'required|is_natural_no_zero',
    ];

    /** The assignment for one employee on one specific date, if any. */
    public function forEmployeeDate(int $employeeId, string $date): ?array
    {
        return $this->where('employee_id', $employeeId)->where('date', $date)->first();
    }

    /** Upsert: replace any existing assignment for this employee/date. */
    public function setForDate(int $employeeId, string $date, int $workScheduleId): void
    {
        $existing = $this->forEmployeeDate($employeeId, $date);

        if ($existing) {
            $this->update($existing['id'], ['work_schedule_id' => $workScheduleId]);
        } else {
            $this->insert(['employee_id' => $employeeId, 'date' => $date, 'work_schedule_id' => $workScheduleId]);
        }
    }
}
