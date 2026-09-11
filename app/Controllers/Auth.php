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
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function attemptLogin()
    {
        //validasi input
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $throttler = service('throttler');
        $ipAddress = $this->request->getIPAddress();
        $throttleKey = md5('login_attempt_' . $ipAddress);

        // Batasi maksimal 5 percobaan login per menit per IP
        if ($throttler->check($throttleKey, 5, 60) === false) {
            $retrySeconds = max(1, $throttler->getTokenTime());
            return redirect()->back()->withInput()->with('error', "Terlalu banyak percobaan login. Silakan tunggu {$retrySeconds} detik sebelum mencoba lagi.");
        }

        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');

        // Cek user di database
        $user = $this->userModel->getUserByUsername($username);

        // Netralisasi error untuk mencegah username enumeration
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Username atau password salah.');
        }

        // Cek status akun (is_active)
        if (isset($user['is_active']) && (int) $user['is_active'] !== 1) {
            return redirect()->back()->withInput()->with('error', 'Akun Anda sudah dinonaktifkan. Silakan hubungi administrator.');
        }

        // Hapus token throttle setelah login berhasil
        cache()->delete('throttler_' . $throttleKey);

        // Rotate the session ID after authentication to prevent session fixation.
        session()->regenerate(true);

        // Set session handling
        $sessionData = [
            'user_id'       => $user['id'],
            'username'      => $user['username'],
            'name'          => $user['name'],
            'email'         => $user['email'],
            'role_id'       => $user['role_id'],
            'role_name'     => $user['role_name'],
            'category'      => $user['category'],
            'isLoggedIn'    => true,
            'last_activity' => time(),
        ];
        session()->set($sessionData);

        return redirect()->to('/dashboard')
            ->with('success', 'Selamat datang kembali, ' . $user['name'])
            ->with('just_logged_in', true);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Anda telah berhasil logout');
    }

    public function activity()
    {
        return $this->response->setJSON(['active' => true]);
    }
}
