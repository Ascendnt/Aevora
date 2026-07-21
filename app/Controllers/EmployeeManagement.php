<?php

namespace App\Controllers;

use App\Constants\Modules;
use App\Models\AccessProfileModel;
use App\Models\BranchModel;
use App\Models\CompanyModel;
use App\Models\DepartmentModel;
use App\Models\EmployeeModel;
use App\Models\EmployeeRankModel;
use App\Models\JobLevelModel;
use App\Models\PositionModel;
use App\Models\UserModel;
use App\Traits\CompanyScoped;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Model;

class EmployeeManagement extends BaseController
{
    use CompanyScoped;

    /**
     * CSV import columns, in the order documented for HR. Only "email" is
     * strictly required in the uploaded file — any other column can be
     * omitted entirely, which is what makes the same template usable both
     * for onboarding new hires and for bulk-updating a single field (e.g.
     * re-upload with just email + basic_pay present to bulk-update pay).
     */
    private const IMPORT_COLUMNS = [
        'name', 'email', 'password', 'employee_number', 'department', 'position',
        'job_level', 'employee_rank', 'supervisor_email', 'basic_pay', 'pay_frequency',
        'is_minimum_wage_earner', 'date_of_birth', 'hire_date', 'branch',
    ];

    protected EmployeeModel $employees;
    protected UserModel $users;

    public function __construct()
    {
        $this->employees = new EmployeeModel();
        $this->users     = new UserModel();
    }

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

    /** Work schedules for a company (or all, for the superadmin/no-company-selected case). */
    private function workSchedulesFor(?int $companyId): array
    {
        $builder = db_connect()->table('work_schedules')->orderBy('name');

        if ($companyId !== null) {
            $builder->where('company_id', $companyId);
        }

        return $builder->get()->getResultArray();
    }

    private function companyMaxApprovalLevels(int $companyId): int
    {
        $company = (new CompanyModel())->find($companyId);
        $max     = (int) ($company['max_approval_levels'] ?? 0);

        return $max > 0 ? $max : 5;
    }

    /**
     * Dropdown data for the add/edit form, scoped to a single company when given.
     * $excludeEmployeeId keeps an employee from being offered as their own supervisor.
     */
    private function formOptions(?int $companyId, ?int $excludeEmployeeId = null): array
    {
        $branchBuilder   = (new BranchModel())->orderBy('name');
        $deptBuilder     = (new DepartmentModel())->orderBy('name');
        $posBuilder      = (new PositionModel())->orderBy('title');
        $jobLevelBuilder = (new JobLevelModel())->orderBy('sort_order')->orderBy('name');
        $rankBuilder     = (new EmployeeRankModel())->orderBy('sort_order')->orderBy('name');

        if ($companyId !== null) {
            $branchBuilder->where('company_id', $companyId);
            $deptBuilder->where('company_id', $companyId);
            $posBuilder->where('company_id', $companyId);
            $jobLevelBuilder->where('company_id', $companyId);
            $rankBuilder->where('company_id', $companyId);
        }

        $companies = $this->selectableCompanies();

        $supervisors = $this->employees->withDetails($companyId);
        if ($excludeEmployeeId !== null) {
            $supervisors = array_values(array_filter(
                $supervisors,
                static fn (array $e): bool => (int) $e['id'] !== $excludeEmployeeId,
            ));
        }

        $maxApprovalLevels = 5;
        if ($companyId !== null) {
            $maxApprovalLevels = $this->companyMaxApprovalLevels($companyId);
        } elseif (count($companies) === 1) {
            $maxApprovalLevels = $this->companyMaxApprovalLevels((int) $companies[0]['id']);
        }

        return [
            'companies'         => $companies,
            'branches'          => $branchBuilder->findAll(),
            'departments'       => $deptBuilder->findAll(),
            'positions'         => $posBuilder->findAll(),
            'jobLevels'         => $jobLevelBuilder->findAll(),
            'employeeRanks'     => $rankBuilder->findAll(),
            'supervisors'       => $supervisors,
            'workSchedules'     => $this->workSchedulesFor($companyId),
            'maxApprovalLevels' => $maxApprovalLevels,
            'accessProfiles'    => (new AccessProfileModel())->orderBy('name')->findAll(),
            'modules'           => Modules::all(),
        ];
    }

    public function index()
    {
        return view('employee_management/index', [
            'title'     => 'Employee management',
            'active'    => 'employee-mgmt',
            'employees' => $this->employees->withDetails(scoped_company_id()),
        ]);
    }

    public function new()
    {
        return view('employee_management/form', array_merge([
            'title'    => 'Add employee',
            'active'   => 'employee-mgmt',
            'employee' => null,
            'grants'   => [],
        ], $this->formOptions(scoped_company_id())));
    }

    public function create()
    {
        $post = $this->request->getPost();

        $companyId = (int) ($post['company_id'] ?? 0);
        $this->assertOwnsCompany($companyId);

        $name     = trim((string) ($post['name'] ?? ''));
        $email    = trim((string) ($post['email'] ?? ''));
        $password = (string) ($post['password'] ?? '');

        if ($name === '' || $email === '' || strlen($password) < 8) {
            return redirect()->back()->withInput()->with('error', 'Name, email, and an initial password (min. 8 characters) are required.');
        }

        if ($this->users->emailExists($email)) {
            return redirect()->back()->withInput()->with('error', 'That email is already in use.');
        }

        $assignmentError = $this->validateAssignmentFields($post, $companyId, null);
        if ($assignmentError !== null) {
            return redirect()->back()->withInput()->with('error', $assignmentError);
        }

        $userId = $this->users->insert([
            'name'          => $name,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'is_superadmin' => false,
        ], true);

        $employeeId = $this->employees->insert(
            array_merge(['user_id' => $userId], $this->employeeFields($post, $companyId)),
            true,
        );

        if (! $employeeId) {
            // Roll back the just-created login so we don't leave an orphaned user account.
            $this->users->delete($userId, true);

            return redirect()->back()->withInput()->with('errors', $this->employees->errors());
        }

        $grants = array_intersect((array) ($post['modules'] ?? []), Modules::keys());
        $this->employees->setIndividualModules((int) $employeeId, $grants);

        return redirect()->to('/employee-management')->with('success', 'Employee added.');
    }

    public function edit(int $id)
    {
        $employee = $this->employees->findWithDetails($id);
        if (! $employee) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $employee['company_id']);

        return view('employee_management/form', array_merge([
            'title'    => 'Edit employee',
            'active'   => 'employee-mgmt',
            'employee' => $employee,
            'grants'   => $this->employees->individualModules($id),
        ], $this->formOptions(scoped_company_id(), $id)));
    }

    public function update(int $id)
    {
        $employee = $this->employees->find($id);
        if (! $employee) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $employee['company_id']);

        $post      = $this->request->getPost();
        $companyId = (int) ($post['company_id'] ?? $employee['company_id']);
        $this->assertOwnsCompany($companyId);

        $name  = trim((string) ($post['name'] ?? ''));
        $email = trim((string) ($post['email'] ?? ''));

        if ($name === '' || $email === '') {
            return redirect()->back()->withInput()->with('error', 'Name and email are required.');
        }

        if ($this->users->emailExists($email, (int) $employee['user_id'])) {
            return redirect()->back()->withInput()->with('error', 'That email is already in use.');
        }

        $assignmentError = $this->validateAssignmentFields($post, $companyId, $id);
        if ($assignmentError !== null) {
            return redirect()->back()->withInput()->with('error', $assignmentError);
        }

        $this->users->update($employee['user_id'], ['name' => $name, 'email' => $email]);
        $this->employees->update($id, $this->employeeFields($post, $companyId));

        $grants = array_intersect((array) ($post['modules'] ?? []), Modules::keys());
        $this->employees->setIndividualModules($id, $grants);

        return redirect()->to('/employee-management')->with('success', 'Employee updated.');
    }

    public function resetPassword(int $id)
    {
        $employee = $this->employees->find($id);
        if (! $employee) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $employee['company_id']);

        $password = (string) $this->request->getPost('password');
        if (strlen($password) < 8) {
            return redirect()->back()->with('error', 'New password must be at least 8 characters.');
        }

        $this->users->setPassword((int) $employee['user_id'], $password);

        return redirect()->to('/employee-management/' . $id . '/edit')->with('success', 'Password reset.');
    }

    public function toggleStatus(int $id)
    {
        $employee = $this->employees->find($id);
        if (! $employee) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $employee['company_id']);

        $newStatus = $employee['status'] === 'active' ? 'inactive' : 'active';
        $this->employees->update($id, ['status' => $newStatus]);

        return redirect()->to('/employee-management')->with('success', $newStatus === 'active' ? 'Employee reactivated.' : 'Employee deactivated.');
    }

    /**
     * Cross-field checks that need a DB lookup, so they can't live inside the
     * pure employeeFields() mapper. Returns an error message, or null if OK.
     */
    private function validateAssignmentFields(array $post, int $companyId, ?int $currentEmployeeId): ?string
    {
        $supervisorId = (int) ($post['supervisor_id'] ?? 0) ?: null;
        if ($supervisorId !== null) {
            if ($currentEmployeeId !== null && $supervisorId === $currentEmployeeId) {
                return 'An employee cannot be set as their own supervisor.';
            }

            $supervisor = $this->employees->find($supervisorId);
            if (! $supervisor || (int) $supervisor['company_id'] !== $companyId) {
                return 'Supervisor must be another employee in the same company.';
            }
        }

        $approvalLevel = (($post['approval_level'] ?? '') !== '') ? (int) $post['approval_level'] : null;
        if ($approvalLevel !== null) {
            $maxLevels = $this->companyMaxApprovalLevels($companyId);
            if ($approvalLevel < 1 || $approvalLevel > $maxLevels) {
                return "Approval level must be between 1 and {$maxLevels} for this company.";
            }
        }

        return null;
    }

    private function employeeFields(array $post, int $companyId): array
    {
        return [
            'company_id'             => $companyId,
            'branch_id'              => (int) ($post['branch_id'] ?? 0) ?: null,
            'department_id'          => (int) ($post['department_id'] ?? 0) ?: null,
            'position_id'            => (int) ($post['position_id'] ?? 0) ?: null,
            'access_profile_id'      => (int) ($post['access_profile_id'] ?? 0) ?: null,
            'employee_number'        => trim((string) ($post['employee_number'] ?? '')) ?: null,
            'status'                 => in_array($post['status'] ?? 'active', ['active', 'inactive'], true) ? $post['status'] : 'active',
            'hire_date'              => ($post['hire_date'] ?? '') !== '' ? $post['hire_date'] : null,
            'date_of_birth'          => ($post['date_of_birth'] ?? '') !== '' ? $post['date_of_birth'] : null,
            'supervisor_id'          => (int) ($post['supervisor_id'] ?? 0) ?: null,
            'job_level_id'           => (int) ($post['job_level_id'] ?? 0) ?: null,
            'employee_rank_id'       => (int) ($post['employee_rank_id'] ?? 0) ?: null,
            'basic_pay'              => ($post['basic_pay'] ?? '') !== '' ? (float) $post['basic_pay'] : null,
            'pay_frequency'          => in_array($post['pay_frequency'] ?? '', ['monthly', 'semi_monthly', 'hourly', 'daily'], true) ? $post['pay_frequency'] : 'monthly',
            'is_minimum_wage_earner' => ! empty($post['is_minimum_wage_earner']),
            'approval_level'         => ($post['approval_level'] ?? '') !== '' ? (int) $post['approval_level'] : null,
            'work_schedule_id'       => (int) ($post['work_schedule_id'] ?? 0) ?: null,
        ];
    }

    // ---------------------------------------------------------------------
    // Bulk CSV import
    // ---------------------------------------------------------------------

    public function importForm()
    {
        return view('employee_management/import', [
            'title'     => 'Bulk import employees',
            'active'    => 'employee-mgmt',
            'companies' => $this->selectableCompanies(),
            'preselect' => scoped_company_id() ?? (int) ($this->request->getGet('company') ?? 0),
            'results'   => null,
            'summary'   => null,
        ]);
    }

    /** Streams a starter CSV template with the expected headers and one example row. */
    public function importTemplate()
    {
        $handle = fopen('php://temp', 'w+b');
        fputcsv($handle, self::IMPORT_COLUMNS);
        fputcsv($handle, [
            'Juan Dela Cruz', 'juan.delacruz@example.com', 'ChangeMe123', 'EMP-0001', 'Sales',
            'Sales Associate', 'Junior', 'Rank and File', 'supervisor@example.com', '25000',
            'monthly', 'no', '1995-06-15', '2024-01-15', 'Main Branch',
        ]);
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="employee_import_template.csv"')
            ->setBody($csv);
    }

    public function import()
    {
        $companies = $this->selectableCompanies();
        $companyId = scoped_company_id() ?? (int) $this->request->getPost('company_id');

        if (! $companyId) {
            return redirect()->back()->with('error', 'Choose a company to import into.');
        }
        $this->assertOwnsCompany($companyId);

        $file = $this->request->getFile('csv_file');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'Choose a valid CSV file to upload.');
        }

        $handle = fopen($file->getTempName(), 'rb');
        if (! $handle) {
            return redirect()->back()->with('error', 'Could not read the uploaded file.');
        }

        $header = fgetcsv($handle);
        if ($header === false || $header === null) {
            fclose($handle);

            return redirect()->back()->with('error', 'The CSV file appears to be empty.');
        }

        $columnIndex = [];
        foreach ($header as $i => $col) {
            $key = strtolower(trim((string) $col));
            if (in_array($key, self::IMPORT_COLUMNS, true)) {
                $columnIndex[$key] = $i;
            }
        }

        if (! isset($columnIndex['email'])) {
            fclose($handle);

            return redirect()->back()->with('error', 'The CSV must include an "email" column.');
        }

        $results    = [];
        $lineNumber = 1; // the header itself is line 1

        while (($raw = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if ($raw === [null] || $raw === false) {
                continue; // a stray blank line
            }
            $isBlank = true;
            foreach ($raw as $cell) {
                if (trim((string) $cell) !== '') {
                    $isBlank = false;
                    break;
                }
            }
            if ($isBlank) {
                continue;
            }

            $row = [];
            foreach ($columnIndex as $key => $idx) {
                $row[$key] = trim((string) ($raw[$idx] ?? ''));
            }

            $result        = $this->importRow($row, $companyId);
            $result['row'] = $lineNumber;
            $results[]     = $result;
        }

        fclose($handle);

        $summary = ['created' => 0, 'updated' => 0, 'errors' => 0, 'total' => count($results)];
        foreach ($results as $r) {
            $summary[$r['status'] === 'created' ? 'created' : ($r['status'] === 'updated' ? 'updated' : 'errors')]++;
        }

        return view('employee_management/import', [
            'title'     => 'Bulk import employees',
            'active'    => 'employee-mgmt',
            'companies' => $companies,
            'preselect' => $companyId,
            'results'   => $results,
            'summary'   => $summary,
        ]);
    }

    /**
     * Processes a single CSV data row: creates a new user+employee, or updates
     * an existing one matched by email. Returns a structured result —
     * {status, identifier, messages} — the same shape a future AI-assisted
     * import reviewer could consume to suggest fixes for error rows.
     */
    private function importRow(array $row, int $companyId): array
    {
        $blocking = [];
        $notes    = [];

        $email = strtolower(trim((string) ($row['email'] ?? '')));
        $name  = trim((string) ($row['name'] ?? ''));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['status' => 'error', 'identifier' => ($row['email'] ?? '') !== '' ? $row['email'] : '(blank)', 'messages' => ['A valid email is required.']];
        }

        $existingUser     = $this->users->findByEmail($email);
        $existingEmployee = $existingUser ? $this->employees->findByUserId((int) $existingUser['id']) : null;
        $isUpdate         = $existingEmployee !== null;

        if ($isUpdate && (int) $existingEmployee['company_id'] !== $companyId) {
            return ['status' => 'error', 'identifier' => $email, 'messages' => ['This email belongs to an employee of a different company — skipped.']];
        }

        $password = (string) ($row['password'] ?? '');

        if (! $isUpdate) {
            if ($name === '') {
                $blocking[] = 'Name is required for new employees.';
            }
            if (strlen($password) < 8) {
                $blocking[] = 'A password of at least 8 characters is required for new employees.';
            }
            if ($this->users->emailExists($email)) {
                $blocking[] = 'Email already belongs to another account (with no linked employee in this company) — resolve manually.';
            }
        }

        $departmentId = ($row['department'] ?? '') !== '' ? $this->resolveByName(new DepartmentModel(), $companyId, $row['department'], 'name') : null;
        $positionId   = ($row['position'] ?? '') !== '' ? $this->resolveByName(new PositionModel(), $companyId, $row['position'], 'title') : null;
        $jobLevelId   = ($row['job_level'] ?? '') !== '' ? $this->resolveByName(new JobLevelModel(), $companyId, $row['job_level'], 'name') : null;
        $rankId       = ($row['employee_rank'] ?? '') !== '' ? $this->resolveByName(new EmployeeRankModel(), $companyId, $row['employee_rank'], 'name') : null;
        $branchId     = ($row['branch'] ?? '') !== '' ? $this->resolveByName(new BranchModel(), $companyId, $row['branch'], 'name', ['status' => 'active']) : null;

        $supervisorId = null;
        if (($row['supervisor_email'] ?? '') !== '') {
            [$supervisorId, $supervisorNote] = $this->resolveSupervisor($companyId, $row['supervisor_email']);
            if ($supervisorNote !== null) {
                $notes[] = $supervisorNote;
            }
        }

        $payFrequency = null;
        if (($row['pay_frequency'] ?? '') !== '') {
            $candidate = strtolower(trim($row['pay_frequency']));
            if (in_array($candidate, ['monthly', 'semi_monthly', 'hourly', 'daily'], true)) {
                $payFrequency = $candidate;
            } else {
                $notes[] = "Unrecognized pay_frequency '{$row['pay_frequency']}' (expected monthly/semi_monthly/hourly/daily) — left unchanged.";
            }
        }

        if ($blocking !== []) {
            return ['status' => 'error', 'identifier' => $email, 'messages' => array_merge($blocking, $notes)];
        }

        $employeeData = array_filter([
            'branch_id'              => $branchId,
            'department_id'          => $departmentId,
            'position_id'            => $positionId,
            'job_level_id'           => $jobLevelId,
            'employee_rank_id'       => $rankId,
            'supervisor_id'          => $supervisorId,
            'employee_number'        => trim((string) ($row['employee_number'] ?? '')) ?: null,
            'basic_pay'              => ($row['basic_pay'] ?? '') !== '' ? (float) $row['basic_pay'] : null,
            'pay_frequency'          => $payFrequency,
            'is_minimum_wage_earner' => ($row['is_minimum_wage_earner'] ?? '') !== ''
                ? in_array(strtolower(trim($row['is_minimum_wage_earner'])), ['1', 'true', 'yes', 'y'], true)
                : null,
            'date_of_birth' => trim((string) ($row['date_of_birth'] ?? '')) ?: null,
            'hire_date'     => trim((string) ($row['hire_date'] ?? '')) ?: null,
        ], static fn ($v) => $v !== null);

        if ($isUpdate) {
            if ($name !== '') {
                $this->users->update($existingUser['id'], ['name' => $name]);
            }

            if ($password !== '') {
                if (strlen($password) < 8) {
                    $notes[] = 'Password column ignored (must be at least 8 characters).';
                } else {
                    $this->users->setPassword((int) $existingUser['id'], $password);
                }
            }

            if ($employeeData !== []) {
                $this->employees->update($existingEmployee['id'], $employeeData);
            }

            return ['status' => 'updated', 'identifier' => $email, 'messages' => $notes];
        }

        $userId = $this->users->insert([
            'name'          => $name,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'is_superadmin' => false,
        ], true);

        if (! $userId) {
            return ['status' => 'error', 'identifier' => $email, 'messages' => array_merge(['Could not create the login account.'], $this->users->errors())];
        }

        $employeeId = $this->employees->insert(array_merge([
            'user_id'    => $userId,
            'company_id' => $companyId,
            'status'     => 'active',
        ], $employeeData), true);

        if (! $employeeId) {
            $this->users->delete($userId, true);

            return ['status' => 'error', 'identifier' => $email, 'messages' => array_merge(['Could not create the employee record.'], $this->employees->errors())];
        }

        return ['status' => 'created', 'identifier' => $email, 'messages' => $notes];
    }

    /** Finds a company-scoped lookup row by exact name (case-sensitive), creating it on the fly if missing. */
    private function resolveByName(Model $model, int $companyId, string $name, string $column, array $extra = []): ?int
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $existing = $model->where('company_id', $companyId)->where($column, $name)->first();
        if ($existing) {
            return (int) $existing['id'];
        }

        $id = $model->insert(array_merge(['company_id' => $companyId, $column => $name], $extra), true);

        return $id ?: null;
    }

    /** @return array{0: ?int, 1: ?string} [resolved supervisor employee id (or null), a note if it couldn't be resolved] */
    private function resolveSupervisor(int $companyId, string $email): array
    {
        $email = strtolower(trim($email));
        $user  = $this->users->findByEmail($email);

        if (! $user) {
            return [null, "Supervisor email '{$email}' not found — supervisor left blank."];
        }

        $employee = $this->employees->findByUserId((int) $user['id']);

        if (! $employee || (int) $employee['company_id'] !== $companyId) {
            return [null, "Supervisor '{$email}' is not an employee of this company — supervisor left blank."];
        }

        return [(int) $employee['id'], null];
    }
}
