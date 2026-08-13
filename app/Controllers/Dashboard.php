<?php

namespace App\Controllers;

use App\Models\UserModel;

class Dashboard extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        //pengecekan session login
        if(!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $username = session()->get('username');

        //ambil detail data user dari database
        $userDetail = $this->userModel->getUserByUsername($username);

        //DATA DUMMY (nanti akan diganti dengan data rill dari sql server database apabila backend pengambilan data sudah selesai dibuat)
        $data = [
            'title'       => 'User Dashboard',
            'user_detail' => $userDetail,

            //bagian 2: statistik peforma user
            'my_stats' => [
                'active_projects'  => 5,
                'on_time_done'     => 12,
                'late_done'        => 2,
                'total_completed'  => 14,
            ],

            //list project aktif user (PIC = Current User)
            'my_active_projects' => [
                [
                    'id'          => 'PRJ-001',
                    'title'       => 'Migrasi Core System Ke Cloud',
                    'deadline'    => '2026-09-15',
                    'progress'    => 75,
                    'status'      => 'In Progress',
                    'status_class' => 'bg-primary'
                ],
                [
                    'id'          => 'PRJ-004',
                    'title'       => 'Integrasi Payment Gateway QRIS',
                    'deadline'    => '2026-08-30',
                    'progress'    => 40,
                    'status'      => 'In Progress',
                    'status_class' => 'bg-info'
                ],
                [
                    'id'          => 'PRJ-007',
                    'title'       => 'Redesign UI/UX Portal Internal',
                    'deadline'    => '2026-08-20',
                    'progress'    => 90,
                    'status'      => 'Review',
                    'status_class' => 'bg-warning'
                ],
            ],

            //timeline / Milestone Terdekat
            'my_timeline' => [
                ['title' => 'UAT Testing System', 'date' => '18 Agu 2026', 'badge' => 'Urgent', 'class' => 'danger'],
                ['title' => 'Freeze Code Sprint 3', 'date' => '22 Agu 2026', 'badge' => 'Normal', 'class' => 'info'],
                ['title' => 'Deployment Staging Environment', 'date' => '28 Agu 2026', 'badge' => 'Normal', 'class' => 'success'],
            ],

            //bagian 3: data khusus ditampikan oleh kepala departemen
            'team_stats' => [
                'total_team_projects' => 18,
                'active_members'      => 8,
                'overdue_projects'    => 1,
                'efficiency_rate'     => '91.5%',
            ],

            //list tim untuk kadept
            'team_members' => [
                ['id' => 2, 'name' => 'Ahmad Rizki', 'role' => 'Staff', 'job' => 'Backend Developer', 'active_tasks' => 4, 'completed' => 15, 'performance' => 'Sangat Baik'],
                ['id' => 3, 'name' => 'Siti Nurhaliza', 'role' => 'Staff', 'job' => 'Frontend Developer', 'active_tasks' => 3, 'completed' => 12, 'performance' => 'Baik'],
                ['id' => 4, 'name' => 'Budi Santoso', 'role' => 'Manmonth', 'job' => 'QA Specialist', 'active_tasks' => 2, 'completed' => 8, 'performance' => 'Cukup'],
            ]
        ];

        return view('dashboard/index', $data);

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
