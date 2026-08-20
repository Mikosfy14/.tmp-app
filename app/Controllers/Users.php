<?php

namespace App\Controllers;

class Users extends BaseController
{
    public function index()
    {
        if (!$this->isKepalaDepartemen()) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak. User Management hanya dapat diakses oleh Kepala Departemen.');
        }

        $users = $this->getUsersWithRoles();
        $roles = $this->getRoles();

        return view('users/index', [
            'title' => 'User Management',
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    private function isKepalaDepartemen(): bool
    {
        return strtolower((string) session()->get('role_name')) === 'kepala departemen';
    }

    private function getUsersWithRoles(): array
    {
        return db_connect()
            ->table('users u')
            ->select('u.id, u.role_id, u.name, u.username, u.email, u.phone_number, u.job_title, u.is_active, u.created_at, u.updated_at, r.role_name, r.category')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->orderBy('u.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function getRoles(): array
    {
        return db_connect()
            ->table('roles')
            ->select('id, role_name, category, description')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }
}
