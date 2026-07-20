<?php

namespace App\Controllers;

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

        session()->regenerate();
        session()->set([
            'user_id'   => $user['id'],
            'user_name' => $user['name'],
            'user_email' => $user['email'],
        ]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login');
    }
}