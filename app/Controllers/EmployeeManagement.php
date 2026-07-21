<?php

namespace App\Controllers;

use App\Constants\Modules;
use App\Models\AccessProfileModel;
use App\Models\BranchModel;
use App\Models\CompanyModel;
use App\Models\DepartmentModel;
use App\Models\EmployeeModel;
use App\Models\PositionModel;
use App\Models\UserModel;
use App\Traits\CompanyScoped;
use CodeIgniter\Exceptions\PageNotFoundException;

class EmployeeManagement extends BaseController
{
    use CompanyScoped;

    protected EmployeeModel $employees;
    protected UserModel $users;

    public function __construct()
    {
        $this->employees = new EmployeeModel();
        $this->users     = new UserModel();
    }

    /** Dropdown data for the add/edit form, scoped to a single company when given. */
    private function formOptions(?int $companyId): array
    {
        $companyBuilder = (new CompanyModel())->orderBy('name');
        $branchBuilder  = (new BranchModel())->orderBy('name');
        $deptBuilder    = (new DepartmentModel())->orderBy('name');
        $posBuilder     = (new PositionModel())->orderBy('title');

        if ($companyId !== null) {
            $companyBuilder->where('id', $companyId);
            $branchBuilder->where('company_id', $companyId);
            $deptBuilder->where('company_id', $companyId);
            $posBuilder->where('company_id', $companyId);
        }

        return [
            'companies'      => $companyBuilder->findAll(),
            'branches'       => $branchBuilder->findAll(),
            'departments'    => $deptBuilder->findAll(),
            'positions'      => $posBuilder->findAll(),
            'accessProfiles' => (new AccessProfileModel())->orderBy('name')->findAll(),
            'modules'        => Modules::all(),
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
        ], $this->formOptions(scoped_company_id())));
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

    private function employeeFields(array $post, int $companyId): array
    {
        return [
            'company_id'        => $companyId,
            'branch_id'         => (int) ($post['branch_id'] ?? 0) ?: null,
            'department_id'     => (int) ($post['department_id'] ?? 0) ?: null,
            'position_id'       => (int) ($post['position_id'] ?? 0) ?: null,
            'access_profile_id' => (int) ($post['access_profile_id'] ?? 0) ?: null,
            'employee_number'   => trim((string) ($post['employee_number'] ?? '')) ?: null,
            'status'            => in_array($post['status'] ?? 'active', ['active', 'inactive'], true) ? $post['status'] : 'active',
            'hire_date'         => ($post['hire_date'] ?? '') !== '' ? $post['hire_date'] : null,
        ];
    }
}
