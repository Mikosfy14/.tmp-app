<?php

namespace App\Controllers;

class Profile extends BaseController
{
    public function index()
    {
        $user = $this->getCurrentUser();

        if (!$user) {
            return redirect()->to('/logout');
        }

        return view('profile/index', [
            'title' => 'Profil Saya',
            'user' => $user,
        ]);
    }

    public function edit()
    {
        $user = $this->getCurrentUser();

        if (!$user) {
            return redirect()->to('/logout');
        }

        return view('profile/edit', [
            'title' => 'Edit Profil',
            'user' => $user,
        ]);
    }

    public function update()
    {
        $userId = (int) session()->get('user_id');
        if ($userId <= 0) {
            return redirect()->to('/logout');
        }

        $rules = [
            'name' => 'required|max_length[150]',
            'email' => 'permit_empty|valid_email|max_length[150]',
            'phone_number' => 'permit_empty|max_length[50]',
            'job_title' => 'permit_empty|max_length[150]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/profile/edit')->withInput()->with('errors', $this->validator->getErrors());
        }

        db_connect()->table('users')->where('id', $userId)->update([
            'name' => trim((string) $this->request->getPost('name')),
            'email' => $this->nullablePost('email'),
            'phone_number' => $this->nullablePost('phone_number'),
            'job_title' => $this->nullablePost('job_title'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->syncSession($userId);

        return redirect()->to('/profile')->with('success', 'Profil berhasil diperbarui.');
    }

    public function changePassword()
    {
        $userId = (int) session()->get('user_id');
        $user = $this->getCurrentUser(true);

        if ($userId <= 0 || !$user) {
            return redirect()->to('/logout');
        }

        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[8]|max_length[72]',
            'new_password_confirmation' => 'required|matches[new_password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/profile/edit')->withInput()->with('password_errors', $this->validator->getErrors());
        }

        $currentPassword = (string) $this->request->getPost('current_password');
        if (!password_verify($currentPassword, (string) $user['password_hash'])) {
            return redirect()->to('/profile/edit')->withInput()->with('password_error', 'Password lama tidak sesuai.');
        }

        db_connect()->table('users')->where('id', $userId)->update([
            'password_hash' => password_hash((string) $this->request->getPost('new_password'), PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/profile')->with('success', 'Password berhasil diganti.');
    }

    private function getCurrentUser(bool $includePassword = false): ?array
    {
        $userId = (int) session()->get('user_id');
        if ($userId <= 0) {
            return null;
        }

        $fields = 'u.id, u.role_id, u.name, u.username, u.email, u.phone_number, u.job_title, u.is_active, u.created_at, u.updated_at, r.role_name, r.category';
        if ($includePassword) {
            $fields .= ', u.password_hash';
        }

        return db_connect()->table('users u')
            ->select($fields)
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('u.id', $userId)
            ->get()
            ->getRowArray() ?: null;
    }

    private function syncSession(int $userId): void
    {
        $user = $this->getCurrentUser();
        if (!$user || (int) $user['id'] !== $userId) {
            return;
        }

        session()->set([
            'username' => $user['username'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role_id' => $user['role_id'],
            'role_name' => $user['role_name'],
            'category' => $user['category'],
        ]);
    }

    private function nullablePost(string $field): ?string
    {
        $value = trim((string) $this->request->getPost($field));

        return $value === '' ? null : $value;
    }
}
