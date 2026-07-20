<?php

namespace App\Controllers;

use App\Models\BranchModel;
use App\Models\CompanyModel;

class Branches extends BaseController
{
    protected BranchModel $branches;

    public function __construct()
    {
        $this->branches = new BranchModel();
    }

    public function index()
    {
        $companyId = (int) ($this->request->getGet('company') ?? 0) ?: null;

        return view('branches/index', [
            'title'      => 'Branches',
            'active'     => 'companies',
            'branches'   => $this->branches->withCompany($companyId),
            'companies'  => (new CompanyModel())->orderBy('name')->findAll(),
            'filter'     => $companyId,
        ]);
    }

    public function new()
    {
        return view('branches/form', [
            'title'     => 'Add branch',
            'active'    => 'companies',
            'branch'    => null,
            'companies' => (new CompanyModel())->orderBy('name')->findAll(),
            'preselect' => (int) ($this->request->getGet('company') ?? 0),
        ]);
    }

    public function create()
    {
        $data          = $this->request->getPost();
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
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('branches/form', [
            'title'     => 'Edit branch',
            'active'    => 'companies',
            'branch'    => $branch,
            'companies' => (new CompanyModel())->orderBy('name')->findAll(),
            'preselect' => 0,
        ]);
    }

    public function update(int $id)
    {
        if (! $this->branches->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data          = $this->request->getPost();
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
        $this->branches->delete($id);

        return redirect()->to('/branches')->with('success', 'Branch deleted.');
    }
}
