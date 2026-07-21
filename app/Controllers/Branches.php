<?php

namespace App\Controllers;

use App\Models\BranchModel;
use App\Models\CompanyModel;
use App\Traits\CompanyScoped;
use CodeIgniter\Exceptions\PageNotFoundException;

class Branches extends BaseController
{
    use CompanyScoped;

    protected BranchModel $branches;

    public function __construct()
    {
        $this->branches = new BranchModel();
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

        return view('branches/index', [
            'title'     => 'Branches',
            'active'    => 'companies',
            'branches'  => $this->branches->withCompany($companyId),
            'companies' => $this->selectableCompanies(),
            'filter'    => $companyId,
        ]);
    }

    public function new()
    {
        $scoped = scoped_company_id();

        return view('branches/form', [
            'title'     => 'Add branch',
            'active'    => 'companies',
            'branch'    => null,
            'companies' => $this->selectableCompanies(),
            'preselect' => $scoped ?? (int) ($this->request->getGet('company') ?? 0),
        ]);
    }

    public function create()
    {
        $data = $this->request->getPost();
        $this->assertOwnsCompany((int) $data['company_id']);
        $data['is_hq'] = ! empty($data['is_hq']);

        if (! $this->branches->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->branches->errors());
        }

        if ($data['is_hq']) {
            $this->branches->makeHq((int) $data['company_id'], (int) $this->branches->getInsertID());
        }

        return redirect()->to('/branches')->with('success', 'Branch added.');
    }

    public function edit(int $id)
    {
        $branch = $this->branches->find($id);
        if (! $branch) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $branch['company_id']);

        return view('branches/form', [
            'title'     => 'Edit branch',
            'active'    => 'companies',
            'branch'    => $branch,
            'companies' => $this->selectableCompanies(),
            'preselect' => 0,
        ]);
    }

    public function update(int $id)
    {
        $branch = $this->branches->find($id);
        if (! $branch) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $branch['company_id']);

        $data = $this->request->getPost();
        $this->assertOwnsCompany((int) $data['company_id']);
        $data['is_hq'] = ! empty($data['is_hq']);

        if (! $this->branches->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->branches->errors());
        }

        if ($data['is_hq']) {
            $this->branches->makeHq((int) $data['company_id'], $id);
        }

        return redirect()->to('/branches')->with('success', 'Branch updated.');
    }

    public function delete(int $id)
    {
        $branch = $this->branches->find($id);
        if (! $branch) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $branch['company_id']);

        $this->branches->delete($id);

        return redirect()->to('/branches')->with('success', 'Branch deleted.');
    }
}
