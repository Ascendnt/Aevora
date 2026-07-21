<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * "Filings" unifies leave requests, official business, schedule-change
 * requests, and attendance-correction ("time adjustment") requests under
 * one table with a filing_type discriminator, all approved by the filer's
 * direct supervisor (employees.supervisor_id at filing time).
 */
class FilingModel extends Model
{
    protected $table         = 'filings';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'employee_id', 'filing_type', 'leave_type_id', 'requested_time_in', 'requested_time_out',
        'requested_work_schedule_id', 'reason', 'days_count', 'status', 'approver_employee_id',
        'decided_by_user_id', 'decision_note', 'decided_at', 'filed_at',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'employee_id' => 'required|is_natural_no_zero',
        'filing_type' => 'required|in_list[leave,official_business,schedule_change,time_adjustment]',
        'status'      => 'permit_empty|in_list[pending,approved,rejected,cancelled]',
    ];

    public const TYPES = ['leave', 'official_business', 'schedule_change', 'time_adjustment'];

    /**
     * Inserts a filing and its filing_dates rows together in one transaction.
     * $dates is a list of 'Y-m-d' strings. Returns the new filing id, or 0 on failure.
     */
    public function createFiling(array $data, array $dates): int
    {
        $this->db->transStart();

        $filingId = $this->insert($data, true);

        if ($filingId && $dates !== []) {
            $rows = array_map(
                static fn ($d) => ['filing_id' => $filingId, 'date' => $d],
                array_values(array_unique($dates)),
            );
            $this->db->table('filing_dates')->insertBatch($rows);
        }

        $this->db->transComplete();

        return $this->db->transStatus() ? (int) $filingId : 0;
    }

    /** All filings with employee/company display fields, optionally scoped to one company. */
    public function withDetails(?int $companyId = null): array
    {
        $builder = $this->db->table('filings f')
            ->select("f.*, u.name AS employee_name, e.company_id AS employee_company_id, c.name AS company_name,
                      lt.name AS leave_type_name, ws.name AS requested_schedule_name, apu.name AS approver_name")
            ->join('employees e', 'e.id = f.employee_id')
            ->join('users u', 'u.id = e.user_id')
            ->join('companies c', 'c.id = e.company_id')
            ->join('leave_types lt', 'lt.id = f.leave_type_id', 'left')
            ->join('work_schedules ws', 'ws.id = f.requested_work_schedule_id', 'left')
            ->join('employees ae', 'ae.id = f.approver_employee_id', 'left')
            ->join('users apu', 'apu.id = ae.user_id', 'left')
            ->orderBy('f.filed_at', 'DESC');

        if ($companyId !== null) {
            $builder->where('e.company_id', $companyId);
        }

        return $this->attachDates($builder->get()->getResultArray());
    }

    /** One employee's own filings (newest first), for their personal "My filings" list. */
    public function forEmployee(int $employeeId): array
    {
        $rows = $this->db->table('filings f')
            ->select("f.*, lt.name AS leave_type_name, ws.name AS requested_schedule_name, apu.name AS approver_name")
            ->join('leave_types lt', 'lt.id = f.leave_type_id', 'left')
            ->join('work_schedules ws', 'ws.id = f.requested_work_schedule_id', 'left')
            ->join('employees ae', 'ae.id = f.approver_employee_id', 'left')
            ->join('users apu', 'apu.id = ae.user_id', 'left')
            ->where('f.employee_id', $employeeId)
            ->orderBy('f.created_at', 'DESC')
            ->get()->getResultArray();

        return $this->attachDates($rows);
    }

    /** Filings awaiting a decision from this employee acting as someone else's supervisor. */
    public function pendingApprovalsFor(int $approverEmployeeId): array
    {
        $rows = $this->db->table('filings f')
            ->select("f.*, u.name AS employee_name, lt.name AS leave_type_name, ws.name AS requested_schedule_name")
            ->join('employees e', 'e.id = f.employee_id')
            ->join('users u', 'u.id = e.user_id')
            ->join('leave_types lt', 'lt.id = f.leave_type_id', 'left')
            ->join('work_schedules ws', 'ws.id = f.requested_work_schedule_id', 'left')
            ->where('f.approver_employee_id', $approverEmployeeId)
            ->where('f.status', 'pending')
            ->orderBy('f.filed_at', 'ASC')
            ->get()->getResultArray();

        return $this->attachDates($rows);
    }

    /**
     * Approve/reject a filing. This does NOT check whether the acting user is
     * actually allowed to decide it — callers must verify the current user is
     * the filing's approver_employee_id before calling this. Approving a leave
     * filing also tallies its days_count against the employee's leave_balances
     * row for that leave type and the year of its (earliest) filed date.
     */
    public function decide(int $filingId, string $status, int $decidedByUserId, ?string $note): bool
    {
        $filing = $this->find($filingId);
        if (! $filing) {
            return false;
        }

        $this->db->transStart();

        $this->update($filingId, [
            'status'             => $status,
            'decided_by_user_id' => $decidedByUserId,
            'decision_note'      => $note,
            'decided_at'         => date('Y-m-d H:i:s'),
        ]);

        if ($status === 'approved' && $filing['filing_type'] === 'leave' && $filing['leave_type_id']) {
            $year = (int) date('Y');

            $firstDate = $this->db->table('filing_dates')
                ->select('date')
                ->where('filing_id', $filingId)
                ->orderBy('date', 'ASC')
                ->get()->getRowArray();

            if ($firstDate) {
                $year = (int) date('Y', strtotime((string) $firstDate['date']));
            }

            (new LeaveBalanceModel())->incrementUsed(
                (int) $filing['employee_id'],
                (int) $filing['leave_type_id'],
                $year,
                (float) ($filing['days_count'] ?? 0),
            );
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /** The 'Y-m-d' dates a filing applies to, oldest first. */
    public function datesFor(int $filingId): array
    {
        return array_column(
            $this->db->table('filing_dates')->select('date')->where('filing_id', $filingId)->orderBy('date', 'ASC')->get()->getResultArray(),
            'date',
        );
    }

    /** Attaches a 'dates' => ['Y-m-d', ...] array to each filing row, in one extra query. */
    private function attachDates(array $rows): array
    {
        if ($rows === []) {
            return $rows;
        }

        $ids = array_column($rows, 'id');

        $dateRows = $this->db->table('filing_dates')
            ->select('filing_id, date')
            ->whereIn('filing_id', $ids)
            ->orderBy('date', 'ASC')
            ->get()->getResultArray();

        $byFiling = [];
        foreach ($dateRows as $dr) {
            $byFiling[$dr['filing_id']][] = $dr['date'];
        }

        foreach ($rows as &$row) {
            $row['dates'] = $byFiling[$row['id']] ?? [];
        }
        unset($row);

        return $rows;
    }
}
