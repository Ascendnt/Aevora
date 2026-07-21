<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\EmployeeRankModel;
use App\Traits\CompanyScoped;
use CodeIgniter\Exceptions\PageNotFoundException;

class EmployeeRanks extends BaseController
{
    use CompanyScoped;

    protected EmployeeRankModel $employeeRanks;

    public function __construct()
    {
        $this->employeeRanks = new EmployeeRankModel();
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

        return view('employee_ranks/index', [
            'title'         => 'Employee ranks',
            'active'        => 'employee-mgmt',
            'employeeRanks' => $this->employeeRanks->withCompany($companyId),
            'companies'     => $this->selectableCompanies(),
            'filter'        => $companyId,
        ]);
    }

    public function new()
    {
        $scoped = scoped_company_id();

        return view('employee_ranks/form', [
            'title'         => 'Add employee rank',
            'active'        => 'employee-mgmt',
            'employeeRank'  => null,
            'companies'     => $this->selectableCompanies(),
            'preselect'     => $scoped ?? (int) ($this->request->getGet('company') ?? 0),
        ]);
    }

    public function create()
    {
        $data = $this->request->getPost(['company_id', 'name', 'sort_order']);
        $this->assertOwnsCompany((int) ($data['company_id'] ?? 0));

        $data['sort_order'] = ($data['sort_order'] ?? '') !== '' ? (int) $data['sort_order'] : 0;
        $data['is_exempt']  = ! empty($this->request->getPost('is_exempt'));

        if (! $this->employeeRanks->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->employeeRanks->errors());
        }

        return redirect()->to('/employee-ranks')->with('success', 'Employee rank added.');
    }

    public function edit(int $id)
    {
        $employeeRank = $this->employeeRanks->find($id);
        if (! $employeeRank) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $employeeRank['company_id']);

        return view('employee_ranks/form', [
            'title'        => 'Edit employee rank',
            'active'       => 'employee-mgmt',
            'employeeRank' => $employeeRank,
            'companies'    => $this->selectableCompanies(),
            'preselect'    => 0,
        ]);
    }

    public function update(int $id)
    {
        $employeeRank = $this->employeeRanks->find($id);
        if (! $employeeRank) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $employeeRank['company_id']);

        $data = $this->request->getPost(['company_id', 'name', 'sort_order']);
        $this->assertOwnsCompany((int) ($data['company_id'] ?? 0));

        $data['sort_order'] = ($data['sort_order'] ?? '') !== '' ? (int) $data['sort_order'] : 0;
        $data['is_exempt']  = ! empty($this->request->getPost('is_exempt'));

        if (! $this->employeeRanks->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->employeeRanks->errors());
        }

        return redirect()->to('/employee-ranks')->with('success', 'Employee rank updated.');
    }

    public function delete(int $id)
    {
        $employeeRank = $this->employeeRanks->find($id);
        if (! $employeeRank) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $employeeRank['company_id']);

        $this->employeeRanks->delete($id);

        return redirect()->to('/employee-ranks')->with('success', 'Employee rank deleted.');
    }
}
