<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        //mengambil data dari session yang disimpan saat login
        $data = [
            'title' => 'Dashboard Utama',
            'name' => session()->get('name'),
            'role_name' => session()->get('role_name'),
            'category' => session()->get('category'),
        ];

        return view('dashboard/index', $data);
    }
}
