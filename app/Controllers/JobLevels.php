<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\JobLevelModel;
use App\Traits\CompanyScoped;
use CodeIgniter\Exceptions\PageNotFoundException;

class JobLevels extends BaseController
{
    use CompanyScoped;

    protected JobLevelModel $jobLevels;

    public function __construct()
    {
        $this->jobLevels = new JobLevelModel();
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

        return view('job_levels/index', [
            'title'     => 'Job levels',
            'active'    => 'employee-mgmt',
            'jobLevels' => $this->jobLevels->withCompany($companyId),
            'companies' => $this->selectableCompanies(),
            'filter'    => $companyId,
        ]);
    }

    public function new()
    {
        $scoped = scoped_company_id();

        return view('job_levels/form', [
            'title'     => 'Add job level',
            'active'    => 'employee-mgmt',
            'jobLevel'  => null,
            'companies' => $this->selectableCompanies(),
            'preselect' => $scoped ?? (int) ($this->request->getGet('company') ?? 0),
        ]);
    }

    public function create()
    {
        $data = $this->request->getPost(['company_id', 'name', 'sort_order']);
        $this->assertOwnsCompany((int) ($data['company_id'] ?? 0));

        $data['sort_order'] = ($data['sort_order'] ?? '') !== '' ? (int) $data['sort_order'] : 0;

        if (! $this->jobLevels->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->jobLevels->errors());
        }

        return redirect()->to('/job-levels')->with('success', 'Job level added.');
    }

    public function edit(int $id)
    {
        $jobLevel = $this->jobLevels->find($id);
        if (! $jobLevel) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $jobLevel['company_id']);

        return view('job_levels/form', [
            'title'     => 'Edit job level',
            'active'    => 'employee-mgmt',
            'jobLevel'  => $jobLevel,
            'companies' => $this->selectableCompanies(),
            'preselect' => 0,
        ]);
    }

    public function update(int $id)
    {
        $jobLevel = $this->jobLevels->find($id);
        if (! $jobLevel) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $jobLevel['company_id']);

        $data = $this->request->getPost(['company_id', 'name', 'sort_order']);
        $this->assertOwnsCompany((int) ($data['company_id'] ?? 0));

        $data['sort_order'] = ($data['sort_order'] ?? '') !== '' ? (int) $data['sort_order'] : 0;

        if (! $this->jobLevels->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->jobLevels->errors());
        }

        return redirect()->to('/job-levels')->with('success', 'Job level updated.');
    }

    public function delete(int $id)
    {
        $jobLevel = $this->jobLevels->find($id);
        if (! $jobLevel) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $jobLevel['company_id']);

        $this->jobLevels->delete($id);

        return redirect()->to('/job-levels')->with('success', 'Job level deleted.');
    }
}
