<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController 
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        //jika sudah login, langsung redirect ke password
        if(session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function attemptLogin()
    {
        //1. validasi input
        $rules = [
            'email' => [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email harus diisi.',
                    'valid_email' => 'Email tidak valid.'
                ]
            ],
            'password' => [
                'rules' => 'required|min_length[6]',
                'errors' => [
                    'required' => 'Password wajib diisi',
                    'min_length' => 'Password minimal 6 karakter.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        //2. Cek user di database
        $user = $this->userModel->getUserByEmail($email);

        //3. Verifikasi Enkripsi Password {BCRYPT}
        if($user && password_verify ($password, $user['password_hash'])) {
            //set session handling
            $sessionData = [
                'user_id'    => $user['id'],
                'name'       => $user['name'],
                'email'      => $user['email'],
                'role_id'    => $user['role_id'],
                'role_name'  => $user['role_name'],
                'category'   => $user['category'],
                'isLoggedIn' => true
            ];
            session()->set($sessionData);

            return redirect()->to('/dashboard')->with('success', 'Selamat datang kembali, ' . $user['name']);
        }
        
        return redirect()->back()->withInput()->with('error', 'Email atau password salah, atau akun anda sudah tidak aktif');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Anda telah berhasil logout');
    }
}