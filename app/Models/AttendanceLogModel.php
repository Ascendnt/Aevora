<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceLogModel extends Model
{
    protected $table         = 'attendance_logs';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['employee_id', 'log_date', 'time_in', 'time_out', 'source', 'timezone'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'employee_id' => 'required|is_natural_no_zero',
        'log_date'    => 'required|valid_date',
    ];

    /** The attendance log for one employee on one date, if any. */
    public function findForDate(int $employeeId, string $date): ?array
    {
        return $this->where('employee_id', $employeeId)->where('log_date', $date)->first();
    }

    /** All attendance logs for an employee within a date range, keyed by log_date. */
    public function rangeForEmployee(int $employeeId, string $startDate, string $endDate): array
    {
        $rows = $this->where('employee_id', $employeeId)
            ->where('log_date >=', $startDate)
            ->where('log_date <=', $endDate)
            ->orderBy('log_date')
            ->findAll();

        return array_column($rows, null, 'log_date');
    }

    /**
     * The schedule that applies to an employee on a specific date: a
     * per-date override in schedule_assignments takes priority, falling
     * back to the employee's default employees.work_schedule_id.
     */
    public function effectiveScheduleFor(int $employeeId, string $date): ?array
    {
        $assigned = $this->db->table('schedule_assignments sa')
            ->select('ws.*')
            ->join('work_schedules ws', 'ws.id = sa.work_schedule_id')
            ->where('sa.employee_id', $employeeId)
            ->where('sa.date', $date)
            ->get()->getRowArray();

        if ($assigned) {
            return $assigned;
        }

        $employee = $this->db->table('employees')
            ->select('work_schedule_id')
            ->where('id', $employeeId)
            ->get()->getRowArray();

        if (! $employee || empty($employee['work_schedule_id'])) {
            return null;
        }

        return (new WorkScheduleModel())->find((int) $employee['work_schedule_id']);
    }

    /**
     * Compares a log's time_in/time_out against a schedule's time_in/time_out
     * (+ grace_minutes) and returns the computed attendance status plus the
     * minutes late/undertime. $log may be an empty/partial array (e.g. no
     * row yet for that date) — an empty log with no times at all is "absent".
     *
     * @return array{status: string, late_minutes: int, undertime_minutes: int}
     */
    public function computeStatus(array $log, array $schedule): array
    {
        $timeIn  = $log['time_in']  ?? null;
        $timeOut = $log['time_out'] ?? null;

        if (empty($timeIn) && empty($timeOut)) {
            return ['status' => 'absent', 'late_minutes' => 0, 'undertime_minutes' => 0];
        }

        $graceMinutes = (int) ($schedule['grace_minutes'] ?? 0);

        $lateMinutes = 0;
        if (! empty($timeIn) && ! empty($schedule['time_in'])) {
            $scheduledInTs = strtotime(substr((string) $schedule['time_in'], 0, 5)) + ($graceMinutes * 60);
            $actualInTs    = strtotime(substr((string) $timeIn, 0, 5));

            if ($scheduledInTs !== false && $actualInTs !== false && $actualInTs > $scheduledInTs) {
                $lateMinutes = (int) round(($actualInTs - $scheduledInTs) / 60);
            }
        }

        $undertimeMinutes = 0;
        if (! empty($timeOut) && ! empty($schedule['time_out'])) {
            $scheduledOutTs = strtotime(substr((string) $schedule['time_out'], 0, 5));
            $actualOutTs    = strtotime(substr((string) $timeOut, 0, 5));

            if ($scheduledOutTs !== false && $actualOutTs !== false && $actualOutTs < $scheduledOutTs) {
                $undertimeMinutes = (int) round(($scheduledOutTs - $actualOutTs) / 60);
            }
        }

        if ($lateMinutes > 0) {
            $status = 'late';
        } elseif ($undertimeMinutes > 0) {
            $status = 'undertime';
        } else {
            $status = 'on_time';
        }

        return ['status' => $status, 'late_minutes' => $lateMinutes, 'undertime_minutes' => $undertimeMinutes];
    }
}
