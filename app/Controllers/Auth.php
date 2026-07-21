<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function attempt()
    {
        $email    = (string) $this->request->getPost('email');
        $password = (string) $this->request->getPost('password');

        $user = (new UserModel())->findByEmail($email);

        if (! $user || ! password_verify($password, $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        $isSuperadmin = (bool) $user['is_superadmin'];
        $employee     = $isSuperadmin ? null : (new EmployeeModel())->findByUserId((int) $user['id']);

        if ($employee && $employee['status'] === 'inactive') {
            return redirect()->back()->withInput()->with('error', 'This account has been deactivated.');
        }

        session()->regenerate();
        session()->set([
            'user_id'       => $user['id'],
            'user_name'     => $user['name'],
            'user_email'    => $user['email'],
            'is_superadmin' => $isSuperadmin,
        ]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login');
    }
}