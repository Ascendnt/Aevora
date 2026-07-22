<?php

namespace App\Controllers;

use App\Models\AttendancePolicyModel;
use App\Models\CompanyModel;
use App\Traits\CompanyScoped;
use CodeIgniter\Exceptions\PageNotFoundException;

class AttendancePolicies extends BaseController
{
    use CompanyScoped;

    protected AttendancePolicyModel $policies;

    public function __construct()
    {
        $this->policies = new AttendancePolicyModel();
    }

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
        return view('attendance_policies/index', [
            'title'    => 'Attendance policies',
            'active'   => 'attendance',
            'policies' => $this->policies->withCompany(scoped_company_id()),
        ]);
    }

    public function new()
    {
        $scoped = scoped_company_id();

        return view('attendance_policies/form', [
            'title'     => 'Add attendance policy',
            'active'    => 'attendance',
            'policy'    => null,
            'companies' => $this->selectableCompanies(),
            'preselect' => $scoped ?? 0,
        ]);
    }

    public function create()
    {
        $post      = $this->request->getPost();
        $companyId = (int) ($post['company_id'] ?? 0);
        $this->assertOwnsCompany($companyId);

        if (! $this->policies->insert($this->fields($post, $companyId))) {
            return redirect()->back()->withInput()->with('errors', $this->policies->errors());
        }

        return redirect()->to('/attendance-policies')->with('success', 'Attendance policy added.');
    }

    public function edit(int $id)
    {
        $policy = $this->policies->find($id);
        if (! $policy) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $policy['company_id']);

        return view('attendance_policies/form', [
            'title'     => 'Edit attendance policy',
            'active'    => 'attendance',
            'policy'    => $policy,
            'companies' => $this->selectableCompanies(),
            'preselect' => 0,
        ]);
    }

    public function update(int $id)
    {
        $policy = $this->policies->find($id);
        if (! $policy) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $policy['company_id']);

        $post      = $this->request->getPost();
        $companyId = (int) ($post['company_id'] ?? $policy['company_id']);
        $this->assertOwnsCompany($companyId);

        if (! $this->policies->update($id, $this->fields($post, $companyId))) {
            return redirect()->back()->withInput()->with('errors', $this->policies->errors());
        }

        return redirect()->to('/attendance-policies')->with('success', 'Attendance policy updated.');
    }

    public function delete(int $id)
    {
        $policy = $this->policies->find($id);
        if (! $policy) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $policy['company_id']);

        $this->policies->delete($id);

        return redirect()->to('/attendance-policies')->with('success', 'Attendance policy deleted.');
    }

    private function fields(array $post, int $companyId): array
    {
        return [
            'company_id'                          => $companyId,
            'name'                                 => trim((string) ($post['name'] ?? '')),
            'absent_before_holiday_forfeits_pay'   => ! empty($post['absent_before_holiday_forfeits_pay']),
            'absent_after_holiday_forfeits_pay'    => ! empty($post['absent_after_holiday_forfeits_pay']),
            'consecutive_absence_alert_days'       => ($post['consecutive_absence_alert_days'] ?? '') !== '' ? (int) $post['consecutive_absence_alert_days'] : null,
            'notes'                                => trim((string) ($post['notes'] ?? '')) ?: null,
        ];
    }
}
