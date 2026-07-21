<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\WorkScheduleModel;
use App\Traits\CompanyScoped;
use CodeIgniter\Exceptions\PageNotFoundException;

class WorkSchedules extends BaseController
{
    use CompanyScoped;

    protected WorkScheduleModel $schedules;

    public function __construct()
    {
        $this->schedules = new WorkScheduleModel();
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

    public function index()
    {
        $scoped    = scoped_company_id();
        $requested = (int) ($this->request->getGet('company') ?? 0) ?: null;
        $companyId = $scoped ?? $requested;

        return view('work_schedules/index', [
            'title'     => 'Work schedules',
            'active'    => 'attendance',
            'schedules' => $this->schedules->withCompany($companyId),
            'companies' => $this->selectableCompanies(),
            'filter'    => $companyId,
        ]);
    }

    public function new()
    {
        $scoped = scoped_company_id();

        return view('work_schedules/form', [
            'title'     => 'Add work schedule',
            'active'    => 'attendance',
            'schedule'  => null,
            'companies' => $this->selectableCompanies(),
            'preselect' => $scoped ?? (int) ($this->request->getGet('company') ?? 0),
        ]);
    }

    public function create()
    {
        $post      = $this->request->getPost();
        $companyId = (int) ($post['company_id'] ?? 0);
        $this->assertOwnsCompany($companyId);

        if (! $this->schedules->insert($this->fields($post, $companyId))) {
            return redirect()->back()->withInput()->with('errors', $this->schedules->errors());
        }

        return redirect()->to('/work-schedules')->with('success', 'Work schedule added.');
    }

    public function edit(int $id)
    {
        $schedule = $this->schedules->find($id);
        if (! $schedule) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $schedule['company_id']);

        return view('work_schedules/form', [
            'title'     => 'Edit work schedule',
            'active'    => 'attendance',
            'schedule'  => $schedule,
            'companies' => $this->selectableCompanies(),
            'preselect' => 0,
        ]);
    }

    public function update(int $id)
    {
        $schedule = $this->schedules->find($id);
        if (! $schedule) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $schedule['company_id']);

        $post      = $this->request->getPost();
        $companyId = (int) ($post['company_id'] ?? $schedule['company_id']);
        $this->assertOwnsCompany($companyId);

        if (! $this->schedules->update($id, $this->fields($post, $companyId))) {
            return redirect()->back()->withInput()->with('errors', $this->schedules->errors());
        }

        return redirect()->to('/work-schedules')->with('success', 'Work schedule updated.');
    }

    public function delete(int $id)
    {
        $schedule = $this->schedules->find($id);
        if (! $schedule) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $schedule['company_id']);

        $this->schedules->delete($id);

        return redirect()->to('/work-schedules')->with('success', 'Work schedule deleted.');
    }

    private function fields(array $post, int $companyId): array
    {
        $scheduleType = in_array($post['schedule_type'] ?? 'fixed', ['fixed', 'shifting', 'executive'], true)
            ? $post['schedule_type']
            : 'fixed';

        return [
            'company_id'    => $companyId,
            'name'          => trim((string) ($post['name'] ?? '')),
            'time_in'       => trim((string) ($post['time_in'] ?? '')),
            'time_out'      => trim((string) ($post['time_out'] ?? '')),
            'grace_minutes' => ($post['grace_minutes'] ?? '') !== '' ? (int) $post['grace_minutes'] : 10,
            'break_minutes' => ($post['break_minutes'] ?? '') !== '' ? (int) $post['break_minutes'] : null,
            'schedule_type' => $scheduleType,
        ];
    }
}
