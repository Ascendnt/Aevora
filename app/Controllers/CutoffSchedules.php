<?php

namespace App\Controllers;

use App\Models\BranchModel;
use App\Models\CompanyModel;
use App\Models\CutoffScheduleModel;
use App\Models\DepartmentModel;
use App\Models\EmployeeModel;
use App\Traits\CompanyScoped;
use CodeIgniter\Exceptions\PageNotFoundException;

class CutoffSchedules extends BaseController
{
    use CompanyScoped;

    protected CutoffScheduleModel $cutoffs;

    public function __construct()
    {
        $this->cutoffs = new CutoffScheduleModel();
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

    /** Dropdown data for the dependent scope_id select, scoped to one company. */
    private function scopeOptions(?int $companyId): array
    {
        if (! $companyId) {
            return ['departments' => [], 'branches' => [], 'employees' => []];
        }

        return [
            'departments' => (new DepartmentModel())->where('company_id', $companyId)->orderBy('name')->findAll(),
            'branches'    => (new BranchModel())->where('company_id', $companyId)->orderBy('name')->findAll(),
            'employees'   => (new EmployeeModel())->withDetails($companyId),
        ];
    }

    /** Attach a human-readable label for each row's scope_type/scope_id, for the list view. */
    private function withScopeLabels(array $schedules, ?int $companyId): array
    {
        $options   = $this->scopeOptions($companyId);
        $deptMap   = array_column($options['departments'], 'name', 'id');
        $branchMap = array_column($options['branches'], 'name', 'id');
        $empMap    = array_column($options['employees'], 'user_name', 'id');

        foreach ($schedules as &$row) {
            // Cast to string first: scope_id may be null (PHP 8.1+ deprecates null array offsets),
            // and a null/missing key simply falls through to the "deleted" fallback below either way.
            $scopeKey = (string) $row['scope_id'];

            $row['scope_label'] = match ($row['scope_type']) {
                'department' => $deptMap[$scopeKey] ?? '— deleted department —',
                'branch'     => $branchMap[$scopeKey] ?? '— deleted branch —',
                'employee'   => $empMap[$scopeKey] ?? '— deleted employee —',
                default      => 'Whole company',
            };
        }

        return $schedules;
    }

    public function index()
    {
        $scoped    = scoped_company_id();
        $requested = (int) ($this->request->getGet('company') ?? 0) ?: null;
        $companyId = $scoped ?? $requested;

        return view('cutoff_schedules/index', [
            'title'     => 'Cutoff schedules',
            'active'    => 'attendance',
            'schedules' => $this->withScopeLabels($this->cutoffs->withCompany($companyId), $companyId),
            'companies' => $this->selectableCompanies(),
            'filter'    => $companyId,
        ]);
    }

    public function new()
    {
        $scoped    = scoped_company_id();
        $companyId = $scoped ?? (int) ($this->request->getGet('company') ?? 0);

        return view('cutoff_schedules/form', array_merge([
            'title'     => 'Add cutoff schedule',
            'active'    => 'attendance',
            'cutoff'    => null,
            'companies' => $this->selectableCompanies(),
            'preselect' => $companyId,
        ], $this->scopeOptions($companyId ?: null)));
    }

    public function create()
    {
        $post      = $this->request->getPost();
        $companyId = (int) ($post['company_id'] ?? 0);
        $this->assertOwnsCompany($companyId);

        if (! $this->cutoffs->insert($this->fields($post, $companyId))) {
            return redirect()->back()->withInput()->with('errors', $this->cutoffs->errors());
        }

        return redirect()->to('/cutoff-schedules')->with('success', 'Cutoff schedule added.');
    }

    public function edit(int $id)
    {
        $cutoff = $this->cutoffs->find($id);
        if (! $cutoff) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $cutoff['company_id']);

        return view('cutoff_schedules/form', array_merge([
            'title'     => 'Edit cutoff schedule',
            'active'    => 'attendance',
            'cutoff'    => $cutoff,
            'companies' => $this->selectableCompanies(),
            'preselect' => 0,
        ], $this->scopeOptions((int) $cutoff['company_id'])));
    }

    public function update(int $id)
    {
        $cutoff = $this->cutoffs->find($id);
        if (! $cutoff) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $cutoff['company_id']);

        $post      = $this->request->getPost();
        $companyId = (int) ($post['company_id'] ?? $cutoff['company_id']);
        $this->assertOwnsCompany($companyId);

        if (! $this->cutoffs->update($id, $this->fields($post, $companyId))) {
            return redirect()->back()->withInput()->with('errors', $this->cutoffs->errors());
        }

        return redirect()->to('/cutoff-schedules')->with('success', 'Cutoff schedule updated.');
    }

    public function delete(int $id)
    {
        $cutoff = $this->cutoffs->find($id);
        if (! $cutoff) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $cutoff['company_id']);

        $this->cutoffs->delete($id);

        return redirect()->to('/cutoff-schedules')->with('success', 'Cutoff schedule deleted.');
    }

    /**
     * Builds period_config JSON from the HR-entered day(s), falling back to a
     * sensible default only when left blank. Shape matches what
     * CutoffReminderService actually parses:
     *   monthly:      {"day_of_month": 31}                     — 31 means "last day of month"
     *   semi_monthly: {"cutoff_days": [15, 31]}                — 31 means "last day of month"
     *   weekly:       {"weekday": 5}                           — 0=Sunday .. 6=Saturday (5=Friday)
     */
    private function buildPeriodConfig(array $post, string $frequency): string
    {
        $parseDay = static function ($raw, int $default): int|string {
            $raw = trim((string) $raw);
            if ($raw === '') {
                return $default;
            }
            if (strtolower($raw) === 'last') {
                return 'last';
            }

            $n = (int) $raw;

            return ($n >= 1 && $n <= 31) ? $n : $default;
        };

        $config = match ($frequency) {
            'weekly' => [
                'weekday' => (($post['cutoff_weekday'] ?? '') !== '') ? max(0, min(6, (int) $post['cutoff_weekday'])) : 5,
            ],
            'monthly' => [
                'day_of_month' => $parseDay($post['cutoff_day'] ?? '', 31),
            ],
            default => [
                'cutoff_days' => [
                    $parseDay($post['cutoff_day_1'] ?? '', 15),
                    $parseDay($post['cutoff_day_2'] ?? '', 31),
                ],
            ],
        };

        return json_encode($config);
    }

    /** Confirms a scope_id actually belongs to this company before trusting it. */
    private function validScopeId(string $scopeType, ?int $scopeId, int $companyId): ?int
    {
        if ($scopeType === 'company' || ! $scopeId) {
            return null;
        }

        $exists = match ($scopeType) {
            'department' => (new DepartmentModel())->where('id', $scopeId)->where('company_id', $companyId)->first(),
            'branch'      => (new BranchModel())->where('id', $scopeId)->where('company_id', $companyId)->first(),
            'employee'    => (new EmployeeModel())->where('id', $scopeId)->where('company_id', $companyId)->first(),
            default       => null,
        };

        return $exists ? $scopeId : null;
    }

    private function fields(array $post, int $companyId): array
    {
        $scopeType = in_array($post['scope_type'] ?? 'company', ['company', 'department', 'branch', 'employee'], true)
            ? $post['scope_type']
            : 'company';
        $frequency = in_array($post['frequency'] ?? 'semi_monthly', ['monthly', 'semi_monthly', 'weekly'], true)
            ? $post['frequency']
            : 'semi_monthly';
        $scopeId = ($post['scope_id'] ?? '') !== '' ? (int) $post['scope_id'] : null;

        return [
            'company_id'           => $companyId,
            'scope_type'           => $scopeType,
            'scope_id'             => $this->validScopeId($scopeType, $scopeId, $companyId),
            'frequency'            => $frequency,
            'period_config'        => $this->buildPeriodConfig($post, $frequency),
            'pay_date_offset_days' => ($post['pay_date_offset_days'] ?? '') !== '' ? (int) $post['pay_date_offset_days'] : 5,
            'reminder_days_before' => ($post['reminder_days_before'] ?? '') !== '' ? (int) $post['reminder_days_before'] : 2,
        ];
    }
}
