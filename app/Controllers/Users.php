<?php

namespace App\Controllers;

class Users extends BaseController
{
    public function index()
    {
        if (!$this->isKepalaDepartemen()) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak. User Management hanya dapat diakses oleh Kepala Departemen.');
        }

        $keyword = trim((string) $this->request->getGet('keyword'));
        $roleFilter = (string) $this->request->getGet('role');
        $statusFilter = (string) $this->request->getGet('status');
        $pager = service('pager');
        $currentPage = $pager->getCurrentPage('users');
        $builder = db_connect()
            ->table('users u')
            ->select('u.id, u.role_id, u.name, u.username, u.email, u.phone_number, u.job_title, u.is_active, u.created_at, u.updated_at, r.role_name, r.category')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->orderBy('u.id', 'ASC');

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('u.name', $keyword)
                ->orLike('u.username', $keyword)
                ->orLike('u.email', $keyword)
                ->orLike('u.phone_number', $keyword)
                ->orLike('u.job_title', $keyword)
                ->groupEnd();
        }

        if ($roleFilter !== '') {
            $builder->where('u.role_id', (int) $roleFilter);
        }

        if ($statusFilter !== '') {
            $builder->where('u.is_active', (int) $statusFilter);
        }

        $totalUsers = $builder->countAllResults(false);
        $pager->store('users', $currentPage, 5, $totalUsers);
        $users = $builder->get(5, ($currentPage - 1) * 5)->getResultArray();
        $roles = $this->getRoles();

        return view('users/index', [
            'title' => 'User Management',
            'users' => $users,
            'pager' => $pager,
            'roles' => $roles,
            'selectedKeyword' => $keyword,
            'selectedRole' => $roleFilter,
            'selectedStatus' => $statusFilter,
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
