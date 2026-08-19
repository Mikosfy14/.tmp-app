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
                    'id'                => 1,
                    'project_code'      => 'PRJ-2026-014',
                    'name'              => 'Modernisasi Dashboard Monitoring Project',
                    'status'            => 'In Progress',
                    'status_class'      => 'bg-primary',
                    'start_date'        => '2026-08-03',
                    'end_date'          => '2026-08-28',
                    'promote_date'      => '2026-08-31',
                    'deadline_label'    => 'On Track',
                    'deadline_class'    => 'success',
                    'assigned_users'    => [
                        ['name' => session()->get('name'), 'job_title' => $userDetail['job_title'] ?? 'Staff'],
                        ['name' => 'Shafiq', 'job_title' => 'Backend Developer'],
                    ],
                ],
                [
                    'id'                => 2,
                    'project_code'      => 'PRJ-2026-015',
                    'name'              => 'Integrasi Auth Lokal Berbasis Role',
                    'status'            => 'Testing/QA',
                    'status_class'      => 'bg-info',
                    'start_date'        => '2026-07-27',
                    'end_date'          => '2026-08-15',
                    'promote_date'      => '2026-08-18',
                    'deadline_label'    => 'Urgent',
                    'deadline_class'    => 'danger',
                    'assigned_users'    => [
                        ['name' => session()->get('name'), 'job_title' => $userDetail['job_title'] ?? 'Staff'],
                        ['name' => 'Shafiq Manmonth', 'job_title' => 'Business Analyst'],
                    ],
                ],
                [
                    'id'                => 3,
                    'project_code'      => 'PRJ-2026-016',
                    'name'              => 'Katalog Aplikasi Pengelolaan Tim',
                    'status'            => 'Review',
                    'status_class'      => 'bg-warning text-dark',
                    'start_date'        => '2026-08-10',
                    'end_date'          => '2026-08-20',
                    'promote_date'      => '2026-08-21',
                    'deadline_label'    => 'Risk',
                    'deadline_class'    => 'warning',
                    'assigned_users'    => [
                        ['name' => session()->get('name'), 'job_title' => $userDetail['job_title'] ?? 'Staff'],
                    ],
                ],
            ],

            'completion_chart' => [
                'months'  => ['Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu'],
                'on_time' => [1, 2, 2, 3, 2, 2],
                'late'    => [0, 1, 0, 1, 0, 0],
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
    }
}
