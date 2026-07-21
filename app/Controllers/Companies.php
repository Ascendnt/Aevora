<?php

namespace App\Controllers;

use App\Models\BranchModel;
use App\Models\CompanyModel;
use App\Models\DepartmentModel;
use App\Models\PositionModel;
use App\Traits\CompanyScoped;
use CodeIgniter\Exceptions\PageNotFoundException;

class Companies extends BaseController
{
    use CompanyScoped;

    protected CompanyModel $companies;

    public function __construct()
    {
        $this->companies = new CompanyModel();
    }

    private function handleLogo(array $data, ?array $existing = null): array
    {
        $file = $this->request->getFile('logo');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            // Delete the old file if replacing
            if ($existing && ! empty($existing['logo_path']) && is_file(FCPATH . $existing['logo_path'])) {
                @unlink(FCPATH . $existing['logo_path']);
            }
            $name = $file->getRandomName();
            $file->move(FCPATH . 'uploads/logos', $name);
            $data['logo_path'] = 'uploads/logos/' . $name;
        }
        unset($data['logo']);
        return $data;
    }

    public function organization(int $id)
    {
        $this->assertOwnsCompany($id);

        $company = $this->companies->find($id);
        if (! $company) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('companies/organization', [
            'title'       => 'Organizational structure',
            'active'      => 'companies',
            'company'     => $company,
            'branches'    => (new BranchModel())->withCompany($id),
            'departments' => (new DepartmentModel())->where('company_id', $id)->orderBy('name')->findAll(),
            'positions'   => (new PositionModel())->where('company_id', $id)->orderBy('title')->findAll(),
        ]);
    }

    public function addDepartment(int $id)
    {
        $this->assertOwnsCompany($id);

        $model = new DepartmentModel();
        if (! $model->insert(['company_id' => $id, 'name' => $this->request->getPost('name')])) {
            return redirect()->back()->with('errors', $model->errors());
        }
        return redirect()->back()->with('success', 'Department added.');
    }

    public function deleteDepartment(int $deptId)
    {
        $model = new DepartmentModel();
        $dept  = $model->find($deptId);
        if (! $dept) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $dept['company_id']);

        $model->delete($deptId);
        return redirect()->back()->with('success', 'Department removed.');
    }

    public function addPosition(int $id)
    {
        $this->assertOwnsCompany($id);

        $model = new PositionModel();
        $dept  = (int) $this->request->getPost('department_id') ?: null;
        if (! $model->insert(['company_id' => $id, 'department_id' => $dept, 'title' => $this->request->getPost('title')])) {
            return redirect()->back()->with('errors', $model->errors());
        }
        return redirect()->back()->with('success', 'Position added.');
    }

    public function deletePosition(int $posId)
    {
        $model = new PositionModel();
        $pos   = $model->find($posId);
        if (! $pos) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $pos['company_id']);

        $model->delete($posId);
        return redirect()->back()->with('success', 'Position removed.');
    }

    public function deleteLogo(int $id)
    {
        $this->assertOwnsCompany($id);

        $company = $this->companies->find($id);
        if (! $company) {
            throw PageNotFoundException::forPageNotFound();
        }

        // Remove the file from disk if it exists
        if (! empty($company['logo_path'])) {
            $file = FCPATH . $company['logo_path'];
            if (is_file($file)) {
                @unlink($file);
            }
        }

        // Clear the reference in the database
        $this->companies->update($id, ['logo_path' => null]);

        return redirect()->to('/companies/' . $id . '/edit')->with('success', 'Logo removed.');
    }

    private function cleanInput(array $data): array
    {
        // Postgres rejects '' for DATE/INT columns — convert blanks to NULL
        foreach (['date_established', 'company_size'] as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }
        return $data;
    }

    public function index()
    {
        return view('companies/index', [
            'title'     => 'Company settings',
            'active'    => 'companies',
            'companies' => $this->companies->withBranchCounts(scoped_company_id()),
        ]);
    }

    public function new()
    {
        if (! is_superadmin()) {
            return redirect()->to('/companies')->with('error', 'Only a superadmin can add new companies.');
        }

        return view('companies/form', [
            'title'   => 'Add company',
            'active'  => 'companies',
            'company' => null,
        ]);
    }

    public function create()
    {
        if (! is_superadmin()) {
            return redirect()->to('/companies')->with('error', 'Only a superadmin can add new companies.');
        }

        $data          = $this->cleanInput($this->handleLogo($this->request->getPost()));
        $data['is_hq'] = ! empty($data['is_hq']);

        if (! $this->companies->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->companies->errors());
        }

        if ($data['is_hq']) {
            $this->companies->makeHq((int) $this->companies->getInsertID());
        }

        return redirect()->to('/companies')->with('success', 'Company added.');
    }

    public function edit(int $id)
    {
        $this->assertOwnsCompany($id);

        $company = $this->companies->find($id);
        if (! $company) {
            throw PageNotFoundException::forPageNotFound();
        }

        $branches = (new BranchModel())->withCompany($id);

        return view('companies/form', [
            'title'    => 'Edit company',
            'active'   => 'companies',
            'company'  => $company,
            'branches' => $branches,
        ]);
    }

    public function update(int $id)
    {
        $this->assertOwnsCompany($id);

        if (! $this->companies->find($id)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $data = $this->cleanInput($this->handleLogo($this->request->getPost()));

        // Only a superadmin may change HQ status — the field isn't even
        // rendered for anyone else, so don't let its absence silently clear it.
        if (is_superadmin()) {
            $data['is_hq'] = ! empty($data['is_hq']);
        } else {
            unset($data['is_hq']);
        }

        if (! $this->companies->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->companies->errors());
        }

        if (! empty($data['is_hq'])) {
            $this->companies->makeHq($id);
        }

        return redirect()->to('/companies')->with('success', 'Company updated.');
    }

    public function delete(int $id)
    {
        if (! is_superadmin()) {
            return redirect()->to('/companies')->with('error', 'Only a superadmin can delete companies.');
        }

        $this->companies->delete($id); // branches cascade via FK

        return redirect()->to('/companies')->with('success', 'Company deleted (including its branches).');
    }
}
