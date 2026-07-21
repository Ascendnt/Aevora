<?php

namespace App\Controllers;

use App\Models\AttendanceLogModel;
use App\Models\HolidayModel;
use DateInterval;
use DatePeriod;
use DateTime;

class TimeAttendance extends BaseController
{
    protected AttendanceLogModel $logs;

    public function __construct()
    {
        $this->logs = new AttendanceLogModel();
    }

    /**
     * The logged-in employee's own attendance for the current calendar
     * month to date: per-day time in/out, computed status, and a
     * "File correction" hand-off into Filings for anything that isn't
     * on_time. Weekends/holidays with no log at all are skipped rather
     * than flagged as false "absent" days (unless the employee's schedule
     * is "shifting", where any day could be a working day).
     */
    public function index()
    {
        $employee = current_employee();

        if (! $employee) {
            // Superadmin accounts (and any login with no employee row) have no personal attendance.
            return view('time_attendance/index', [
                'title'      => 'Time & attendance',
                'active'     => 'attendance',
                'noEmployee' => true,
                'today'      => null,
                'todayLog'   => null,
                'rows'       => [],
                'periodLabel' => '',
            ]);
        }

        $employeeId  = (int) $employee['id'];
        $today       = date('Y-m-d');
        $periodStart = date('Y-m-01');

        $logsByDate = $this->logs->rangeForEmployee($employeeId, $periodStart, $today);

        $holidayNamesByDate = array_column(
            (new HolidayModel())
                ->where('company_id', (int) $employee['company_id'])
                ->where('date >=', $periodStart)
                ->where('date <=', $today)
                ->findAll(),
            'name',
            'date',
        );

        $rows   = [];
        $period = new DatePeriod(
            new DateTime($periodStart),
            new DateInterval('P1D'),
            (new DateTime($today))->modify('+1 day'),
        );

        foreach ($period as $day) {
            $date = $day->format('Y-m-d');
            $dow  = (int) $day->format('N'); // ISO-8601: 1 = Monday .. 7 = Sunday
            $log  = $logsByDate[$date] ?? null;

            $schedule  = $this->logs->effectiveScheduleFor($employeeId, $date);
            $isHoliday = isset($holidayNamesByDate[$date]);
            $isRestDay = $dow >= 6 && (! $schedule || $schedule['schedule_type'] !== 'shifting');

            if (! $log && ($isRestDay || $isHoliday)) {
                // Nothing logged on a weekend/holiday for a non-shifting schedule — not a real absence.
                continue;
            }

            $row = [
                'date'              => $date,
                'dow'               => $dow,
                'log'               => $log,
                'schedule'          => $schedule,
                'status'            => null,
                'late_minutes'      => 0,
                'undertime_minutes' => 0,
                'is_holiday'        => $isHoliday,
                'holiday_name'      => $holidayNamesByDate[$date] ?? null,
            ];

            if ($schedule) {
                $computed                    = $this->logs->computeStatus($log ?? [], $schedule);
                $row['status']               = $computed['status'];
                $row['late_minutes']         = $computed['late_minutes'];
                $row['undertime_minutes']    = $computed['undertime_minutes'];
            }

            $rows[] = $row;
        }

        return view('time_attendance/index', [
            'title'       => 'Time & attendance',
            'active'      => 'attendance',
            'noEmployee'  => false,
            'today'       => $today,
            'todayLog'    => $logsByDate[$today] ?? null,
            'rows'        => array_reverse($rows), // most recent day first
            'periodLabel' => date('F Y', strtotime($periodStart)),
        ]);
    }

    public function clockIn()
    {
        $employee = current_employee();
        if (! $employee) {
            return redirect()->to('/attendance')->with('error', 'No employee profile is linked to this account.');
        }

        $employeeId = (int) $employee['id'];
        $today      = date('Y-m-d');
        $existing   = $this->logs->findForDate($employeeId, $today);

        if ($existing && ! empty($existing['time_in'])) {
            return redirect()->to('/attendance')->with('error', 'You already clocked in today at ' . $existing['time_in'] . '.');
        }

        $now = date('H:i:s');

        if ($existing) {
            $this->logs->update($existing['id'], ['time_in' => $now]);
        } else {
            $this->logs->insert(['employee_id' => $employeeId, 'log_date' => $today, 'time_in' => $now, 'source' => 'clock']);
        }

        return redirect()->to('/attendance')->with('success', 'Clocked in at ' . $now . '.');
    }

    public function clockOut()
    {
        $employee = current_employee();
        if (! $employee) {
            return redirect()->to('/attendance')->with('error', 'No employee profile is linked to this account.');
        }

        $employeeId = (int) $employee['id'];
        $today      = date('Y-m-d');
        $existing   = $this->logs->findForDate($employeeId, $today);

        if ($existing && ! empty($existing['time_out'])) {
            return redirect()->to('/attendance')->with('error', 'You already clocked out today at ' . $existing['time_out'] . '.');
        }

        $now = date('H:i:s');

        if ($existing) {
            $this->logs->update($existing['id'], ['time_out' => $now]);
        } else {
            $this->logs->insert(['employee_id' => $employeeId, 'log_date' => $today, 'time_out' => $now, 'source' => 'clock']);
        }

        return redirect()->to('/attendance')->with('success', 'Clocked out at ' . $now . '.');
    }
}
