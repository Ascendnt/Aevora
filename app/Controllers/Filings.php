<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Models\FilingModel;
use App\Models\LeaveTypeModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Filings extends BaseController
{
    protected FilingModel $filings;
    protected LeaveTypeModel $leaveTypes;
    protected EmployeeModel $employees;

    public function __construct()
    {
        $this->filings    = new FilingModel();
        $this->leaveTypes = new LeaveTypeModel();
        $this->employees  = new EmployeeModel();
    }

    /** The signed-in user's own filings. */
    public function index()
    {
        $employee = current_employee();

        return view('filings/index', [
            'title'    => 'My filings',
            'active'   => 'filings',
            'filings'  => $employee ? $this->filings->forEmployee((int) $employee['id']) : [],
            'employee' => $employee,
        ]);
    }

    public function new()
    {
        $employee = current_employee();
        if (! $employee) {
            return redirect()->to('/filings')->with('error', 'Only employees can submit filings.');
        }

        $companyId = (int) $employee['company_id'];

        $thisMonth = new \DateTimeImmutable('first day of this month');
        $nextMonth = $thisMonth->modify('+1 month');

        return view('filings/new', [
            'title'          => 'New filing',
            'active'         => 'filings',
            'leaveTypes'     => $this->leaveTypes->where('company_id', $companyId)->orderBy('name')->findAll(),
            'workSchedules'  => \Config\Database::connect()->table('work_schedules')
                ->where('company_id', $companyId)->orderBy('name')->get()->getResultArray(),
            'calendarMonths' => [
                $this->calendarMonth((int) $thisMonth->format('Y'), (int) $thisMonth->format('n')),
                $this->calendarMonth((int) $nextMonth->format('Y'), (int) $nextMonth->format('n')),
            ],
            'supervisorName' => $this->supervisorName($employee['supervisor_id'] ? (int) $employee['supervisor_id'] : null),
        ]);
    }

    public function create()
    {
        $employee = current_employee();
        if (! $employee) {
            return redirect()->to('/filings')->with('error', 'Only employees can submit filings.');
        }

        $post       = $this->request->getPost();
        $filingType = (string) ($post['filing_type'] ?? '');

        if (! in_array($filingType, FilingModel::TYPES, true)) {
            return redirect()->back()->withInput()->with('error', 'Please choose a valid filing type.');
        }

        $dates = array_values(array_unique(array_filter(
            (array) ($post['dates'] ?? []),
            static fn ($d) => is_string($d) && $d !== '',
        )));
        sort($dates);

        if ($dates === []) {
            return redirect()->back()->withInput()->with('error', 'Please select at least one date.');
        }

        $data = [
            'employee_id' => (int) $employee['id'],
            'filing_type' => $filingType,
            'reason'      => trim((string) ($post['reason'] ?? '')) ?: null,
            'status'      => 'pending',
            'filed_at'    => date('Y-m-d H:i:s'),
        ];

        if ($filingType === 'leave') {
            $leaveTypeId = (int) ($post['leave_type_id'] ?? 0);
            $leaveType   = $leaveTypeId ? $this->leaveTypes->find($leaveTypeId) : null;

            if (! $leaveType || (int) $leaveType['company_id'] !== (int) $employee['company_id']) {
                return redirect()->back()->withInput()->with('error', 'Please choose a valid leave type.');
            }

            $data['leave_type_id'] = $leaveTypeId;
            $data['days_count']    = count($dates);
        }

        if ($filingType === 'schedule_change') {
            $scheduleId = (int) ($post['requested_work_schedule_id'] ?? 0);
            $schedule   = $scheduleId
                ? \Config\Database::connect()->table('work_schedules')
                    ->where('id', $scheduleId)->where('company_id', $employee['company_id'])->get()->getRowArray()
                : null;

            if (! $schedule) {
                return redirect()->back()->withInput()->with('error', 'Please choose a valid work schedule.');
            }

            $data['requested_work_schedule_id'] = $scheduleId;
        }

        if (in_array($filingType, ['official_business', 'time_adjustment'], true)) {
            $data['requested_time_in']  = trim((string) ($post['requested_time_in'] ?? '')) ?: null;
            $data['requested_time_out'] = trim((string) ($post['requested_time_out'] ?? '')) ?: null;
        }

        $data['approver_employee_id'] = $employee['supervisor_id'] ?: null;

        $filingId = $this->filings->createFiling($data, $dates);

        if (! $filingId) {
            $errors = $this->filings->errors();

            return redirect()->back()->withInput()->with('errors', $errors !== [] ? $errors : ['Could not submit the filing. Please try again.']);
        }

        $message = $employee['supervisor_id']
            ? 'Filing submitted.'
            : 'Filing submitted. Note: no supervisor is assigned to you yet — this filing has no approver.';

        return redirect()->to('/filings')->with('success', $message);
    }

    /** Filings waiting on the signed-in user's decision, as someone else's supervisor. */
    public function myApprovals()
    {
        $employee = current_employee();

        return view('filings/my_approvals', [
            'title'     => 'My approvals',
            'active'    => 'filings',
            'approvals' => $employee ? $this->filings->pendingApprovalsFor((int) $employee['id']) : [],
            'employee'  => $employee,
        ]);
    }

    public function decide(int $filingId)
    {
        $employee = current_employee();
        $filing   = $this->filings->find($filingId);

        if (! $filing) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $employee || (int) $filing['approver_employee_id'] !== (int) $employee['id']) {
            return redirect()->to('/filings/my-approvals')->with('error', 'You are not the approver for that filing.');
        }

        if ($filing['status'] !== 'pending') {
            return redirect()->to('/filings/my-approvals')->with('error', 'That filing has already been decided.');
        }

        $decision = (string) $this->request->getPost('decision');
        if (! in_array($decision, ['approved', 'rejected'], true)) {
            return redirect()->to('/filings/my-approvals')->with('error', 'Invalid decision.');
        }

        $note   = trim((string) $this->request->getPost('note')) ?: null;
        $userId = (int) session()->get('user_id');

        $this->filings->decide($filingId, $decision, $userId, $note);

        return redirect()->to('/filings/my-approvals')->with('success', 'Filing ' . $decision . '.');
    }

    private function supervisorName(?int $supervisorId): ?string
    {
        if (! $supervisorId) {
            return null;
        }

        $supervisor = $this->employees->findWithDetails($supervisorId);

        return $supervisor['user_name'] ?? null;
    }

    /** Builds a printable calendar grid (Mon-first weeks, null = blank leading/trailing cell) for one month. */
    private function calendarMonth(int $year, int $month): array
    {
        $first        = sprintf('%04d-%02d-01', $year, $month);
        $daysInMonth  = (int) date('t', strtotime($first));
        $startWeekday = (int) date('N', strtotime($first)); // 1 (Mon) .. 7 (Sun)

        $days = array_fill(0, $startWeekday - 1, null);

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $days[] = sprintf('%04d-%02d-%02d', $year, $month, $d);
        }

        while (count($days) % 7 !== 0) {
            $days[] = null;
        }

        return [
            'label' => date('F Y', strtotime($first)),
            'weeks' => array_chunk($days, 7),
        ];
    }
}
