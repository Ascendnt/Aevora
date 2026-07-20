<?php

namespace App\Controllers;

class Landing extends BaseController
{
    public function index()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('landing/index');
    }
}