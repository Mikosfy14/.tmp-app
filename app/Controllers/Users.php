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

        if (empty($users)) {
            $users = $this->getPreviewUsers();
        }

        if (empty($roles)) {
            $roles = $this->getPreviewRoles();
        }

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

    private function getPreviewRoles(): array
    {
        return [
            ['id' => 1, 'role_name' => 'Kepala Departemen', 'category' => 'Organik', 'description' => 'Full access user and team management'],
            ['id' => 2, 'role_name' => 'Staff', 'category' => 'Organik', 'description' => 'Organic staff user'],
            ['id' => 3, 'role_name' => 'Manmonth', 'category' => 'NonOrganik', 'description' => 'Non-organic user'],
        ];
    }

    private function getPreviewUsers(): array
    {
        return [
            [
                'id' => 1,
                'role_id' => 1,
                'name' => 'Rendra Aditya',
                'username' => 'rendra.kadept',
                'email' => 'rendra.aditya@example.local',
                'phone_number' => '081200000001',
                'job_title' => 'Kepala Departemen',
                'is_active' => 1,
                'created_at' => '2026-08-01 09:00:00',
                'updated_at' => null,
                'role_name' => 'Kepala Departemen',
                'category' => 'Organik',
            ],
            [
                'id' => 2,
                'role_id' => 2,
                'name' => 'Diego Pratama',
                'username' => 'diego.pratama',
                'email' => 'diego.pratama@example.local',
                'phone_number' => '081200000002',
                'job_title' => 'Backend Developer',
                'is_active' => 1,
                'created_at' => '2026-08-03 10:15:00',
                'updated_at' => '2026-08-12 14:30:00',
                'role_name' => 'Staff',
                'category' => 'Organik',
            ],
            [
                'id' => 3,
                'role_id' => 2,
                'name' => 'Indah Permata',
                'username' => 'indah.permata',
                'email' => 'indah.permata@example.local',
                'phone_number' => '081200000003',
                'job_title' => 'Business Analyst',
                'is_active' => 1,
                'created_at' => '2026-08-04 11:20:00',
                'updated_at' => null,
                'role_name' => 'Staff',
                'category' => 'Organik',
            ],
            [
                'id' => 4,
                'role_id' => 3,
                'name' => 'Bagas Wicaksono',
                'username' => 'bagas.manmonth',
                'email' => 'bagas.wicaksono@example.local',
                'phone_number' => '081200000004',
                'job_title' => 'QA Specialist',
                'is_active' => 1,
                'created_at' => '2026-08-05 13:10:00',
                'updated_at' => null,
                'role_name' => 'Manmonth',
                'category' => 'NonOrganik',
            ],
            [
                'id' => 5,
                'role_id' => 3,
                'name' => 'Maya Salsabila',
                'username' => 'maya.manmonth',
                'email' => 'maya.salsabila@example.local',
                'phone_number' => '081200000005',
                'job_title' => 'Frontend Developer',
                'is_active' => 0,
                'created_at' => '2026-07-25 08:45:00',
                'updated_at' => '2026-08-10 16:50:00',
                'role_name' => 'Manmonth',
                'category' => 'NonOrganik',
            ],
        ];
    }
}
