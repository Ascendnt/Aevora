<?php

namespace App\Controllers;

use App\Constants\Modules;
use App\Models\AccessProfileModel;
use App\Models\EmployeeModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class AccessProfiles extends BaseController
{
    protected AccessProfileModel $profiles;

    public function __construct()
    {
        $this->profiles = new AccessProfileModel();
    }

    /** Profile definitions are system-wide, so only a superadmin manages them. */
    private function guard(): ?RedirectResponse
    {
        if (! is_superadmin()) {
            return redirect()->to('/dashboard')->with('error', 'Only a superadmin can manage access profiles.');
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        return view('access_profiles/index', [
            'title'    => 'Access profiles',
            'active'   => 'access-profiles',
            'profiles' => $this->profiles->allWithModules(),
            'modules'  => Modules::all(),
        ]);
    }

    public function new()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        return view('access_profiles/form', [
            'title'      => 'Add access profile',
            'active'     => 'access-profiles',
            'profile'    => null,
            'checked'    => [],
            'modules'    => Modules::all(),
            'assignedTo' => [],
        ]);
    }

    public function create()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $name = trim((string) $this->request->getPost('name'));

        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Name is required.');
        }
        if ($this->profiles->nameExists($name)) {
            return redirect()->back()->withInput()->with('error', 'A profile with that name already exists.');
        }

        $id      = $this->profiles->insert(['name' => $name], true);
        $modules = array_intersect((array) $this->request->getPost('modules'), Modules::keys());
        $this->profiles->setModules((int) $id, $modules);

        return redirect()->to('/access-profiles')->with('success', 'Access profile added.');
    }

    public function edit(int $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $profile = $this->profiles->find($id);
        if (! $profile) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('access_profiles/form', [
            'title'          => 'Edit access profile',
            'active'         => 'access-profiles',
            'profile'        => $profile,
            'checked'        => $this->profiles->moduleKeys($id),
            'modules'        => Modules::all(),
            'assignedTo'     => (new EmployeeModel())->byAccessProfile($id),
        ]);
    }

    public function update(int $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        if (! $this->profiles->find($id)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $name = trim((string) $this->request->getPost('name'));

        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Name is required.');
        }
        if ($this->profiles->nameExists($name, $id)) {
            return redirect()->back()->withInput()->with('error', 'A profile with that name already exists.');
        }

        $this->profiles->update($id, ['name' => $name]);
        $modules = array_intersect((array) $this->request->getPost('modules'), Modules::keys());
        $this->profiles->setModules($id, $modules);

        return redirect()->to('/access-profiles')->with('success', 'Access profile updated.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        // access_profile_modules cascades; employees using this profile just fall back to none (FK SET NULL).
        $this->profiles->delete($id);

        return redirect()->to('/access-profiles')->with('success', 'Access profile deleted.');
    }
}
