<?php

namespace App\Controllers;

use App\Libraries\CutoffReminderService;
use App\Libraries\PayrollEngine;
use App\Libraries\XlsxWriter;
use App\Models\BenefitApplicationHistoryModel;
use App\Models\CompanyModel;
use App\Models\CutoffScheduleModel;
use App\Models\EmployeeBenefitModel;
use App\Models\EmployeeLoanModel;
use App\Models\EmployeeModel;
use App\Models\LoanDeductionHistoryModel;
use App\Models\PayrollRunModel;
use App\Models\PayslipModel;
use App\Traits\CompanyScoped;
use CodeIgniter\Exceptions\PageNotFoundException;

class Payroll extends BaseController
{
    use CompanyScoped;

    protected PayrollRunModel $runs;
    protected PayslipModel $payslips;
    protected EmployeeBenefitModel $benefits;
    protected EmployeeLoanModel $loans;

    public function __construct()
    {
        $this->runs     = new PayrollRunModel();
        $this->payslips = new PayslipModel();
        $this->benefits = new EmployeeBenefitModel();
        $this->loans    = new EmployeeLoanModel();
    }

    // ---------------------------------------------------------------- Dashboard

    public function dashboard()
    {
        $scoped    = scoped_company_id();
        $employees = new EmployeeModel();

        $headcountBuilder = $employees->where('status', 'active');
        if ($scoped !== null) {
            $headcountBuilder->where('company_id', $scoped);
        }
        $headcount = $headcountBuilder->countAllResults();

        $draftRun     = $this->runs->latestDraft($scoped);
        $draftSummary = $draftRun ? $this->summarizeDraftRun($draftRun) : null;

        return view('payroll/dashboard', [
            'title'        => 'Payroll',
            'active'       => 'payroll',
            'headcount'    => $headcount,
            'draftRun'     => $draftRun,
            'draftSummary' => $draftSummary,
            'draftCount'   => $this->runs->draftCount($scoped),
            'recentRuns'   => array_slice($this->runs->withCompany($scoped), 0, 5),
            'reminders'    => $this->collectReminders($scoped),
        ]);
    }

    /** Live-recomputed gross/net totals for every active employee in a draft run's company. */
    private function summarizeDraftRun(array $run): array
    {
        $engine    = new PayrollEngine();
        $employees = (new EmployeeModel())->where('company_id', $run['company_id'])->where('status', 'active')->findAll();

        $grossTotal   = 0.0;
        $netTotal     = 0.0;
        $errorCount   = 0;

        foreach ($employees as $employee) {
            $result = $engine->computeForEmployee((int) $employee['id'], $run['period_start'], $run['period_end']);
            if (! empty($result['error'])) {
                $errorCount++;
                continue;
            }
            $grossTotal += $result['gross_pay'];
            $netTotal   += $result['net_pay'];
        }

        return [
            'employee_count' => count($employees),
            'error_count'    => $errorCount,
            'gross_total'    => round($grossTotal, 2),
            'net_total'      => round($netTotal, 2),
        ];
    }

    /** Upcoming cutoff reminders — reuses the Time & Attendance module's CutoffReminderService. */
    private function collectReminders(?int $scoped): array
    {
        $service   = new CutoffReminderService();
        $reminders = [];

        if ($scoped !== null) {
            foreach ($service->pendingReminders($scoped) as $reminder) {
                $reminders[] = $reminder;
            }

            return $reminders;
        }

        // Superadmin: fan out across every company, attaching the company name for display.
        foreach ((new CompanyModel())->orderBy('name')->findAll() as $company) {
            foreach ($service->pendingReminders((int) $company['id']) as $reminder) {
                $reminder['company_name'] = $company['name'];
                $reminders[]              = $reminder;
            }
        }

        return $reminders;
    }

    // ---------------------------------------------------------------- Runs

    public function runs()
    {
        return view('payroll/runs_index', [
            'title'  => 'Payroll runs',
            'active' => 'payroll',
            'runs'   => $this->runs->withCompany(scoped_company_id()),
        ]);
    }

    public function newRun()
    {
        return view('payroll/run_form', [
            'title'          => 'New payroll run',
            'active'         => 'payroll',
            'companies'      => $this->selectableCompanies(),
            'cutoffSchedules' => (new CutoffScheduleModel())->withCompany(scoped_company_id()),
        ]);
    }

    public function createRun()
    {
        $post      = $this->request->getPost();
        $companyId = (int) ($post['company_id'] ?? 0);
        $this->assertOwnsCompany($companyId);

        $periodStart = (string) ($post['period_start'] ?? '');
        $periodEnd   = (string) ($post['period_end'] ?? '');

        if ($periodStart === '' || $periodEnd === '' || $periodEnd < $periodStart) {
            return redirect()->back()->withInput()->with('error', 'Choose a valid period start and end (end on or after start).');
        }

        $cutoffScheduleId = (int) ($post['cutoff_schedule_id'] ?? 0) ?: null;
        $payDate          = (string) ($post['pay_date'] ?? '') ?: null;

        if (! $payDate && $cutoffScheduleId) {
            $cutoff = (new CutoffScheduleModel())->find($cutoffScheduleId);
            if ($cutoff && ! empty($cutoff['pay_date_offset_days'])) {
                $payDate = date('Y-m-d', strtotime($periodEnd . ' +' . (int) $cutoff['pay_date_offset_days'] . ' days'));
            }
        }

        $runId = $this->runs->insert([
            'company_id'         => $companyId,
            'cutoff_schedule_id' => $cutoffScheduleId,
            'period_start'       => $periodStart,
            'period_end'         => $periodEnd,
            'pay_date'           => $payDate,
            'status'             => 'draft',
        ], true);

        if (! $runId) {
            return redirect()->back()->withInput()->with('errors', $this->runs->errors());
        }

        return redirect()->to('/payroll/runs/' . $runId)->with('success', 'Payroll run created as a draft.');
    }

    public function viewRun(int $runId)
    {
        $run = $this->runs->findWithCompany($runId);
        if (! $run) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $run['company_id']);

        if ($run['status'] === 'draft') {
            $engine    = new PayrollEngine();
            $employees = (new EmployeeModel())->where('company_id', $run['company_id'])->where('status', 'active')->findAll();

            $rows        = [];
            $grossTotal  = 0.0;
            $netTotal    = 0.0;

            foreach ($employees as $employee) {
                $result   = $engine->computeForEmployee((int) $employee['id'], $run['period_start'], $run['period_end']);
                $rows[]   = $result;
                if (empty($result['error'])) {
                    $grossTotal += $result['gross_pay'];
                    $netTotal   += $result['net_pay'];
                }
            }

            return view('payroll/run_view', [
                'title'      => 'Payroll run — ' . $run['period_start'] . ' to ' . $run['period_end'],
                'active'     => 'payroll',
                'run'        => $run,
                'rows'       => $rows,
                'grossTotal' => round($grossTotal, 2),
                'netTotal'   => round($netTotal, 2),
                'isDraft'    => true,
            ]);
        }

        return view('payroll/run_view', [
            'title'      => 'Payroll run — ' . $run['period_start'] . ' to ' . $run['period_end'],
            'active'     => 'payroll',
            'run'        => $run,
            'rows'       => $this->payslips->byRun($runId),
            'isDraft'    => false,
        ]);
    }

    public function finalizeRun(int $runId)
    {
        $run = $this->runs->findWithCompany($runId);
        if (! $run) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $run['company_id']);

        if ($run['status'] !== 'draft') {
            return redirect()->to('/payroll/runs/' . $runId)->with('error', 'This run is already finalized.');
        }

        $engine    = new PayrollEngine();
        $employees = (new EmployeeModel())->where('company_id', $run['company_id'])->where('status', 'active')->findAll();

        // Compute everyone first — abort without writing anything if any employee can't
        // be computed (e.g. missing basic pay), rather than finalizing a partial run.
        $computed = [];
        $blocked  = [];

        foreach ($employees as $employee) {
            $result = $engine->computeForEmployee((int) $employee['id'], $run['period_start'], $run['period_end']);
            if (! empty($result['error'])) {
                $blocked[] = ($result['employee_name'] ?? ('Employee #' . $employee['id'])) . ': ' . $result['error'];
                continue;
            }
            $computed[] = $result;
        }

        if ($blocked !== []) {
            return redirect()->to('/payroll/runs/' . $runId)
                ->with('error', 'Cannot finalize — fix these employees first: ' . implode(' | ', $blocked));
        }

        $db = db_connect();
        $db->transStart();

        foreach ($computed as $result) {
            $payslipId = $this->payslips->insert([
                'payroll_run_id'        => $runId,
                'employee_id'           => $result['employee_id'],
                'basic_pay'             => $result['basic_pay'],
                'days_worked'           => $result['days_worked'],
                'late_minutes'          => $result['late_minutes'],
                'undertime_minutes'     => $result['undertime_minutes'],
                'absence_days'          => $result['absence_days'],
                'late_deduction'        => $result['late_deduction'],
                'undertime_deduction'   => $result['undertime_deduction'],
                'absence_deduction'     => $result['absence_deduction'],
                'gross_pay'             => $result['gross_pay'],
                'taxable_income'        => $result['taxable_income'],
                'tax_withheld'          => $result['tax_withheld'],
                'statutory_deductions'  => $result['statutory_deductions'],
                'benefits_total'        => $result['benefits_total'],
                'loan_deductions_total' => $result['loan_deductions_total'],
                'total_deductions'      => $result['total_deductions'],
                'net_pay'               => $result['net_pay'],
                'computed_at'           => $result['computed_at'],
            ], true);

            $this->ledgerizeLoans($payslipId, $result['details']['loans'] ?? []);
            $this->ledgerizeBenefits($payslipId, $result['details']['benefits'] ?? []);
        }

        $this->runs->update($runId, ['status' => 'finalized']);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to('/payroll/runs/' . $runId)->with('error', 'Finalizing failed — no changes were saved. Please try again.');
        }

        return redirect()->to('/payroll/runs/' . $runId)->with('success', 'Payroll run finalized. Payslips are now locked historical records.');
    }

    private function ledgerizeLoans(int $payslipId, array $loanLines): void
    {
        if ($loanLines === []) {
            return;
        }

        $history = new LoanDeductionHistoryModel();
        $now     = date('Y-m-d H:i:s');

        foreach ($loanLines as $line) {
            $history->insert([
                'loan_id'         => $line['loan_id'],
                'payslip_id'      => $payslipId,
                'amount_deducted' => $line['amount'],
                'deducted_at'     => $now,
            ]);

            $newBalance = round($line['balance_before'] - $line['amount'], 2);
            $update     = ['balance_remaining' => max(0, $newBalance)];
            if ($newBalance <= 0) {
                $update['status'] = 'completed';
            }
            $this->loans->update($line['loan_id'], $update);
        }
    }

    private function ledgerizeBenefits(int $payslipId, array $benefitLines): void
    {
        if ($benefitLines === []) {
            return;
        }

        $history = new BenefitApplicationHistoryModel();
        $now     = date('Y-m-d H:i:s');

        foreach ($benefitLines as $line) {
            $history->insert([
                'benefit_id'     => $line['benefit_id'],
                'payslip_id'     => $payslipId,
                'amount_applied' => $line['amount'],
                'applied_at'     => $now,
            ]);
        }
    }

    // ---------------------------------------------------------------- Export

    public function export(int $runId)
    {
        $run = $this->runs->findWithCompany($runId);
        if (! $run) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $run['company_id']);

        $writer = new XlsxWriter('Payroll Register');
        $writer->addRow([
            'Employee Number', 'Employee Name', 'Basic Pay', 'Days Worked', 'Late (min)', 'Undertime (min)',
            'Absence (days)', 'Late Deduction', 'Undertime Deduction', 'Absence Deduction', 'Gross Pay',
            'Statutory Deductions', 'Tax Withheld', 'Benefits', 'Loan Deductions', 'Total Deductions', 'Net Pay',
        ]);

        if ($run['status'] === 'draft') {
            $engine    = new PayrollEngine();
            $employees = (new EmployeeModel())->withDetails((int) $run['company_id']);

            foreach ($employees as $employee) {
                $result = $engine->computeForEmployee((int) $employee['id'], $run['period_start'], $run['period_end']);
                if (! empty($result['error'])) {
                    $writer->addRow([$employee['employee_number'] ?? '', $employee['user_name'] ?? '', 'ERROR: ' . $result['error']]);
                    continue;
                }
                $this->addExportRow($writer, $employee['employee_number'] ?? '', $result['employee_name'], $result);
            }
        } else {
            foreach ($this->payslips->byRun($runId) as $slip) {
                $this->addExportRow($writer, $slip['employee_number'] ?? '', $slip['employee_name'], $slip);
            }
        }

        $writer->download('payroll-register-' . $run['period_start'] . '-to-' . $run['period_end'] . '.xlsx');
    }

    private function addExportRow(XlsxWriter $writer, string $employeeNumber, string $employeeName, array $row): void
    {
        $statutoryTotal = round(((float) $row['total_deductions']) - ((float) $row['tax_withheld']) - ((float) $row['loan_deductions_total']), 2);

        $writer->addRow([
            $employeeNumber,
            $employeeName,
            (float) $row['basic_pay'],
            (float) $row['days_worked'],
            (int) $row['late_minutes'],
            (int) $row['undertime_minutes'],
            (float) $row['absence_days'],
            (float) $row['late_deduction'],
            (float) $row['undertime_deduction'],
            (float) $row['absence_deduction'],
            (float) $row['gross_pay'],
            $statutoryTotal,
            (float) $row['tax_withheld'],
            (float) $row['benefits_total'],
            (float) $row['loan_deductions_total'],
            (float) $row['total_deductions'],
            (float) $row['net_pay'],
        ]);
    }

    // ---------------------------------------------------------------- Benefits

    public function benefits()
    {
        return view('payroll/benefits_index', [
            'title'         => 'Employee benefits',
            'active'        => 'payroll',
            'benefits'      => $this->attachBenefitHistoryCounts($this->benefits->withEmployee(scoped_company_id())),
        ]);
    }

    private function attachBenefitHistoryCounts(array $benefits): array
    {
        $history = new BenefitApplicationHistoryModel();
        foreach ($benefits as &$benefit) {
            $benefit['applied_count'] = $history->countForBenefit((int) $benefit['id']);
        }

        return $benefits;
    }

    public function newBenefit()
    {
        return view('payroll/benefit_form', [
            'title'        => 'Add benefit',
            'active'       => 'payroll',
            'benefit'      => null,
            'appliedCount' => 0,
            'employees'    => (new EmployeeModel())->withDetails(scoped_company_id()),
        ]);
    }

    public function createBenefit()
    {
        $post      = $this->request->getPost();
        $employee  = $this->employeeOrFail((int) ($post['employee_id'] ?? 0));

        $data = [
            'employee_id'    => $employee['id'],
            'benefit_type'   => trim((string) ($post['benefit_type'] ?? '')),
            'amount'         => (float) ($post['amount'] ?? 0),
            'is_recurring'   => ! empty($post['is_recurring']),
            'effective_date' => (string) ($post['effective_date'] ?? '') ?: date('Y-m-d'),
            'end_date'       => (string) ($post['end_date'] ?? '') ?: null,
            'notes'          => trim((string) ($post['notes'] ?? '')) ?: null,
        ];

        if (! $this->benefits->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->benefits->errors());
        }

        return redirect()->to('/payroll/benefits')->with('success', 'Benefit added.');
    }

    public function editBenefit(int $id)
    {
        $benefit = $this->benefits->findWithEmployee($id);
        if (! $benefit) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $benefit['company_id']);

        return view('payroll/benefit_form', [
            'title'        => 'Edit benefit',
            'active'       => 'payroll',
            'benefit'      => $benefit,
            'appliedCount' => (new BenefitApplicationHistoryModel())->countForBenefit($id),
            'employees'    => (new EmployeeModel())->withDetails(scoped_company_id()),
        ]);
    }

    public function updateBenefit(int $id)
    {
        $benefit = $this->benefits->find($id);
        if (! $benefit) {
            throw PageNotFoundException::forPageNotFound();
        }
        $employeeRow = $this->employeeOrFail((int) $benefit['employee_id']);
        $this->assertOwnsCompany((int) $employeeRow['company_id']);

        $appliedCount = (new BenefitApplicationHistoryModel())->countForBenefit($id);
        $post         = $this->request->getPost();

        if ($appliedCount > 0 && empty($post['confirm_override'])) {
            return redirect()->back()->withInput()->with('error', 'This benefit has already been applied in ' . $appliedCount . ' payslip(s). Check the confirmation box to save changes anyway.');
        }

        $data = [
            'benefit_type'   => trim((string) ($post['benefit_type'] ?? '')),
            'amount'         => (float) ($post['amount'] ?? 0),
            'is_recurring'   => ! empty($post['is_recurring']),
            'effective_date' => (string) ($post['effective_date'] ?? '') ?: date('Y-m-d'),
            'end_date'       => (string) ($post['end_date'] ?? '') ?: null,
            'notes'          => trim((string) ($post['notes'] ?? '')) ?: null,
        ];

        if (! $this->benefits->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->benefits->errors());
        }

        return redirect()->to('/payroll/benefits')->with('success', 'Benefit updated.');
    }

    public function deleteBenefit(int $id)
    {
        $benefit = $this->benefits->find($id);
        if (! $benefit) {
            throw PageNotFoundException::forPageNotFound();
        }
        $employeeRow = $this->employeeOrFail((int) $benefit['employee_id']);
        $this->assertOwnsCompany((int) $employeeRow['company_id']);

        $appliedCount = (new BenefitApplicationHistoryModel())->countForBenefit($id);
        if ($appliedCount > 0 && empty($this->request->getPost('confirm_override'))) {
            return redirect()->to('/payroll/benefits/' . $id . '/edit')
                ->with('error', 'This benefit has already been applied in ' . $appliedCount . ' payslip(s). Check the confirmation box to delete it anyway.');
        }

        $this->benefits->delete($id);

        return redirect()->to('/payroll/benefits')->with('success', 'Benefit deleted.');
    }

    // ---------------------------------------------------------------- Loans

    public function loansIndex()
    {
        return view('payroll/loans_index', [
            'title'  => 'Employee loans',
            'active' => 'payroll',
            'loans'  => $this->attachLoanHistoryCounts($this->loans->withEmployee(scoped_company_id())),
        ]);
    }

    private function attachLoanHistoryCounts(array $loans): array
    {
        $history = new LoanDeductionHistoryModel();
        foreach ($loans as &$loan) {
            $loan['deduction_count'] = $history->countForLoan((int) $loan['id']);
        }

        return $loans;
    }

    public function newLoan()
    {
        return view('payroll/loan_form', [
            'title'          => 'Add loan',
            'active'         => 'payroll',
            'loan'           => null,
            'deductionCount' => 0,
            'employees'      => (new EmployeeModel())->withDetails(scoped_company_id()),
        ]);
    }

    public function createLoan()
    {
        $post     = $this->request->getPost();
        $employee = $this->employeeOrFail((int) ($post['employee_id'] ?? 0));

        $principal = (float) ($post['principal_amount'] ?? 0);

        $data = [
            'employee_id'          => $employee['id'],
            'loan_type'            => trim((string) ($post['loan_type'] ?? '')),
            'principal_amount'     => $principal,
            'monthly_amortization' => (float) ($post['monthly_amortization'] ?? 0),
            'balance_remaining'    => (string) ($post['balance_remaining'] ?? '') !== '' ? (float) $post['balance_remaining'] : $principal,
            'start_date'           => (string) ($post['start_date'] ?? '') ?: date('Y-m-d'),
            'end_date'             => (string) ($post['end_date'] ?? '') ?: null,
            'status'               => in_array($post['status'] ?? 'active', ['active', 'completed', 'cancelled'], true) ? $post['status'] : 'active',
            'notes'                => trim((string) ($post['notes'] ?? '')) ?: null,
        ];

        if (! $this->loans->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->loans->errors());
        }

        return redirect()->to('/payroll/loans')->with('success', 'Loan added.');
    }

    public function editLoan(int $id)
    {
        $loan = $this->loans->findWithEmployee($id);
        if (! $loan) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $loan['company_id']);

        return view('payroll/loan_form', [
            'title'          => 'Edit loan',
            'active'         => 'payroll',
            'loan'           => $loan,
            'deductionCount' => (new LoanDeductionHistoryModel())->countForLoan($id),
            'employees'      => (new EmployeeModel())->withDetails(scoped_company_id()),
        ]);
    }

    public function updateLoan(int $id)
    {
        $loan = $this->loans->find($id);
        if (! $loan) {
            throw PageNotFoundException::forPageNotFound();
        }
        $employeeRow = $this->employeeOrFail((int) $loan['employee_id']);
        $this->assertOwnsCompany((int) $employeeRow['company_id']);

        $deductionCount = (new LoanDeductionHistoryModel())->countForLoan($id);
        $post           = $this->request->getPost();

        if ($deductionCount > 0 && empty($post['confirm_override'])) {
            return redirect()->back()->withInput()->with('error', 'This loan has already been applied in ' . $deductionCount . ' payslip(s). Check the confirmation box to save changes anyway.');
        }

        $data = [
            'loan_type'            => trim((string) ($post['loan_type'] ?? '')),
            'principal_amount'     => (float) ($post['principal_amount'] ?? 0),
            'monthly_amortization' => (float) ($post['monthly_amortization'] ?? 0),
            'balance_remaining'    => (float) ($post['balance_remaining'] ?? 0),
            'start_date'           => (string) ($post['start_date'] ?? '') ?: date('Y-m-d'),
            'end_date'             => (string) ($post['end_date'] ?? '') ?: null,
            'status'               => in_array($post['status'] ?? 'active', ['active', 'completed', 'cancelled'], true) ? $post['status'] : 'active',
            'notes'                => trim((string) ($post['notes'] ?? '')) ?: null,
        ];

        if (! $this->loans->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->loans->errors());
        }

        return redirect()->to('/payroll/loans')->with('success', 'Loan updated.');
    }

    public function deleteLoan(int $id)
    {
        $loan = $this->loans->find($id);
        if (! $loan) {
            throw PageNotFoundException::forPageNotFound();
        }
        $employeeRow = $this->employeeOrFail((int) $loan['employee_id']);
        $this->assertOwnsCompany((int) $employeeRow['company_id']);

        $deductionCount = (new LoanDeductionHistoryModel())->countForLoan($id);
        if ($deductionCount > 0 && empty($this->request->getPost('confirm_override'))) {
            return redirect()->to('/payroll/loans/' . $id . '/edit')
                ->with('error', 'This loan has already been applied in ' . $deductionCount . ' payslip(s). Check the confirmation box to delete it anyway.');
        }

        $this->loans->delete($id);

        return redirect()->to('/payroll/loans')->with('success', 'Loan deleted.');
    }

    // ---------------------------------------------------------------- Bulk import

    public function importForm()
    {
        return view('payroll/import', [
            'title'   => 'Import benefits & loans',
            'active'  => 'payroll',
            'results' => null,
        ]);
    }

    public function import()
    {
        $type = (string) $this->request->getPost('import_type');
        if (! in_array($type, ['benefit', 'loan'], true)) {
            return redirect()->back()->with('error', 'Choose whether you are importing benefits or loans.');
        }

        $file = $this->request->getFile('csv');
        if (! $file || ! $file->isValid()) {
            return redirect()->back()->with('error', 'Choose a CSV file to upload.');
        }

        $results = $type === 'benefit'
            ? $this->importBenefitsCsv($file->getTempName())
            : $this->importLoansCsv($file->getTempName());

        return view('payroll/import', [
            'title'   => 'Import benefits & loans',
            'active'  => 'payroll',
            'results' => $results,
        ]);
    }

    /**
     * @return array{type: string, rows: array, success_count: int, error_count: int}
     */
    private function importBenefitsCsv(string $path): array
    {
        $rows        = [];
        $successCount = 0;
        $errorCount   = 0;

        $this->readCsv($path, function (array $data, int $line) use (&$rows, &$successCount, &$errorCount) {
            $email = trim((string) ($data['employee_email'] ?? ''));
            $employee = $email !== '' ? $this->findEmployeeByEmail($email) : null;

            if (! $employee) {
                $errorCount++;
                $rows[] = ['line' => $line, 'status' => 'error', 'message' => "No employee found for email \"{$email}\".", 'input' => $data];

                return;
            }

            if (scoped_company_id() !== null && (int) $employee['company_id'] !== scoped_company_id()) {
                $errorCount++;
                $rows[] = ['line' => $line, 'status' => 'error', 'message' => 'Employee belongs to a different company.', 'input' => $data];

                return;
            }

            $benefitType = trim((string) ($data['benefit_type'] ?? ''));
            $amount      = (string) ($data['amount'] ?? '');

            if ($benefitType === '' || $amount === '' || ! is_numeric($amount)) {
                $errorCount++;
                $rows[] = ['line' => $line, 'status' => 'error', 'message' => 'benefit_type and a numeric amount are required.', 'input' => $data];

                return;
            }

            $payload = [
                'employee_id'    => (int) $employee['id'],
                'benefit_type'   => $benefitType,
                'amount'         => (float) $amount,
                'is_recurring'   => in_array(strtolower(trim((string) ($data['is_recurring'] ?? 'true'))), ['1', 'true', 'yes', 'y'], true),
                'effective_date' => trim((string) ($data['effective_date'] ?? '')) ?: date('Y-m-d'),
                'end_date'       => trim((string) ($data['end_date'] ?? '')) ?: null,
                'notes'          => trim((string) ($data['notes'] ?? '')) ?: null,
            ];

            $existing = (new EmployeeBenefitModel())
                ->where('employee_id', $employee['id'])
                ->where('benefit_type', $benefitType)
                ->first();

            $model = new EmployeeBenefitModel();
            if ($existing) {
                $model->update($existing['id'], $payload);
                $successCount++;
                $rows[] = ['line' => $line, 'status' => 'success', 'message' => 'Updated existing benefit.', 'action' => 'updated', 'input' => $data];
            } else {
                if (! $model->insert($payload)) {
                    $errorCount++;
                    $rows[] = ['line' => $line, 'status' => 'error', 'message' => implode(' ', $model->errors()), 'input' => $data];

                    return;
                }
                $successCount++;
                $rows[] = ['line' => $line, 'status' => 'success', 'message' => 'Created new benefit.', 'action' => 'created', 'input' => $data];
            }
        });

        return ['type' => 'benefit', 'rows' => $rows, 'success_count' => $successCount, 'error_count' => $errorCount];
    }

    /**
     * @return array{type: string, rows: array, success_count: int, error_count: int}
     */
    private function importLoansCsv(string $path): array
    {
        $rows         = [];
        $successCount = 0;
        $errorCount   = 0;

        $this->readCsv($path, function (array $data, int $line) use (&$rows, &$successCount, &$errorCount) {
            $email    = trim((string) ($data['employee_email'] ?? ''));
            $employee = $email !== '' ? $this->findEmployeeByEmail($email) : null;

            if (! $employee) {
                $errorCount++;
                $rows[] = ['line' => $line, 'status' => 'error', 'message' => "No employee found for email \"{$email}\".", 'input' => $data];

                return;
            }

            if (scoped_company_id() !== null && (int) $employee['company_id'] !== scoped_company_id()) {
                $errorCount++;
                $rows[] = ['line' => $line, 'status' => 'error', 'message' => 'Employee belongs to a different company.', 'input' => $data];

                return;
            }

            $loanType             = trim((string) ($data['loan_type'] ?? ''));
            $principal            = (string) ($data['principal_amount'] ?? '');
            $monthlyAmortization  = (string) ($data['monthly_amortization'] ?? '');

            if ($loanType === '' || ! is_numeric($principal) || ! is_numeric($monthlyAmortization)) {
                $errorCount++;
                $rows[] = ['line' => $line, 'status' => 'error', 'message' => 'loan_type, principal_amount and monthly_amortization are required and numeric.', 'input' => $data];

                return;
            }

            $balance = trim((string) ($data['balance_remaining'] ?? ''));

            $payload = [
                'employee_id'           => (int) $employee['id'],
                'loan_type'             => $loanType,
                'principal_amount'      => (float) $principal,
                'monthly_amortization'  => (float) $monthlyAmortization,
                'balance_remaining'     => $balance !== '' && is_numeric($balance) ? (float) $balance : (float) $principal,
                'start_date'            => trim((string) ($data['start_date'] ?? '')) ?: date('Y-m-d'),
                'end_date'              => trim((string) ($data['end_date'] ?? '')) ?: null,
                'status'                => in_array($data['status'] ?? '', ['active', 'completed', 'cancelled'], true) ? $data['status'] : 'active',
                'notes'                 => trim((string) ($data['notes'] ?? '')) ?: null,
            ];

            $existing = (new EmployeeLoanModel())
                ->where('employee_id', $employee['id'])
                ->where('loan_type', $loanType)
                ->first();

            $model = new EmployeeLoanModel();
            if ($existing) {
                $model->update($existing['id'], $payload);
                $successCount++;
                $rows[] = ['line' => $line, 'status' => 'success', 'message' => 'Updated existing loan.', 'action' => 'updated', 'input' => $data];
            } else {
                if (! $model->insert($payload)) {
                    $errorCount++;
                    $rows[] = ['line' => $line, 'status' => 'error', 'message' => implode(' ', $model->errors()), 'input' => $data];

                    return;
                }
                $successCount++;
                $rows[] = ['line' => $line, 'status' => 'success', 'message' => 'Created new loan.', 'action' => 'created', 'input' => $data];
            }
        });

        return ['type' => 'loan', 'rows' => $rows, 'success_count' => $successCount, 'error_count' => $errorCount];
    }

    /** Streams a CSV file, calling $onRow(assocRowMappedByHeader, lineNumber) for every data row. */
    private function readCsv(string $path, callable $onRow): void
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            return;
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);

            return;
        }
        $header = array_map(static fn ($h) => strtolower(trim((string) $h)), $header);

        $line = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $line++;
            if ($row === [null] || $row === []) {
                continue;
            }
            $data = [];
            foreach ($header as $i => $key) {
                $data[$key] = $row[$i] ?? '';
            }
            $onRow($data, $line);
        }

        fclose($handle);
    }

    /**
     * This module writes its own "find employee by email" lookup rather than
     * depending on another module's importer — payroll's CSV import stays
     * self-contained even if that code changes shape elsewhere.
     */
    private function findEmployeeByEmail(string $email): ?array
    {
        $row = db_connect()->table('employees e')
            ->select('e.*')
            ->join('users u', 'u.id = e.user_id')
            ->where('LOWER(u.email)', strtolower($email))
            ->get()->getRowArray();

        return $row ?: null;
    }

    // ---------------------------------------------------------------- Shared helpers

    /** Companies the current user is allowed to pick from (all for superadmin, just their own otherwise). */
    private function selectableCompanies(): array
    {
        $builder = (new CompanyModel())->orderBy('name');
        $scoped  = scoped_company_id();

        if ($scoped !== null) {
            $builder->where('id', $scoped);
        }

        return $builder->findAll();
    }

    /** Loads an employee row and enforces company ownership, or 404s. */
    private function employeeOrFail(int $employeeId): array
    {
        $employee = (new EmployeeModel())->find($employeeId);
        if (! $employee) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $employee['company_id']);

        return $employee;
    }
}
