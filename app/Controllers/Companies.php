<?php

namespace App\Controllers;

use App\Models\BranchModel;
use App\Models\CompanyModel;

class Companies extends BaseController
{
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
        $company = $this->companies->find($id);
        if (! $company) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('companies/organization', [
            'title'       => 'Organizational structure',
            'active'      => 'companies',
            'company'     => $company,
            'branches'    => (new \App\Models\BranchModel())->withCompany($id),
            'departments' => (new \App\Models\DepartmentModel())->where('company_id', $id)->orderBy('name')->findAll(),
            'positions'   => (new \App\Models\PositionModel())->where('company_id', $id)->orderBy('title')->findAll(),
        ]);
    }

    public function addDepartment(int $id)
    {
        $model = new \App\Models\DepartmentModel();
        if (! $model->insert(['company_id' => $id, 'name' => $this->request->getPost('name')])) {
            return redirect()->back()->with('errors', $model->errors());
        }
        return redirect()->back()->with('success', 'Department added.');
    }

    public function deleteDepartment(int $deptId)
    {
        (new \App\Models\DepartmentModel())->delete($deptId);
        return redirect()->back()->with('success', 'Department removed.');
    }

    public function addPosition(int $id)
    {
        $model = new \App\Models\PositionModel();
        $dept  = (int) $this->request->getPost('department_id') ?: null;
        if (! $model->insert(['company_id' => $id, 'department_id' => $dept, 'title' => $this->request->getPost('title')])) {
            return redirect()->back()->with('errors', $model->errors());
        }
        return redirect()->back()->with('success', 'Position added.');
    }

    public function deletePosition(int $posId)
    {
        (new \App\Models\PositionModel())->delete($posId);
        return redirect()->back()->with('success', 'Position removed.');
    }

    public function deleteLogo(int $id)
    {
        $company = $this->companies->find($id);
        if (! $company) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
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
            'companies' => $this->companies->withBranchCounts(),
        ]);
    }

    public function new()
    {
        return view('companies/form', [
            'title'   => 'Add company',
            'active'  => 'companies',
            'company' => null,
        ]);
    }

    public function create()
    {
        if (! $this->companies->insert($this->cleanInput($this->handleLogo($this->request->getPost())))) {
            return redirect()->back()->withInput()->with('errors', $this->companies->errors());
        }

        return redirect()->to('/companies')->with('success', 'Company added.');
    }

    public function edit(int $id)
    {
        $company = $this->companies->find($id);
        if (! $company) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
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
        if (! $this->companies->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (! $this->companies->update($id, $this->cleanInput($this->handleLogo($this->request->getPost())))) {
            return redirect()->back()->withInput()->with('errors', $this->companies->errors());
        }

        return redirect()->to('/companies')->with('success', 'Company updated.');
    }

    public function delete(int $id)
    {
        $this->companies->delete($id); // branches cascade via FK

        return redirect()->to('/companies')->with('success', 'Company deleted (including its branches).');
    }
}
