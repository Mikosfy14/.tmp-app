<?php

namespace App\Controllers;

use App\Libraries\DashboardMetrics;
use App\Models\ApplicationModel;
use App\Models\ProjectFileModel;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $userId = (int) session()->get('user_id');
        $user = (new UserModel())->getUserByUsername((string) session()->get('username')) ?: [];
        $metrics = (new DashboardMetrics())->personal($userId);
        $stats = $metrics['stats'];

        $totalCompleted = $stats['total_completed'] ?? 0;
        $totalProjects = $totalCompleted + ($stats['active_projects'] ?? 0);
        $completionRate = $totalProjects > 0 ? round(($totalCompleted / $totalProjects) * 100, 1) : 0.0;

        $managedApps = (new ApplicationModel())
            ->select('applications.*, criticality_recovery.criticality_name, criticality_recovery.description as criticality_desc')
            ->join('criticality_recovery', 'criticality_recovery.id = applications.criticality_recovery_id', 'left')
            ->where('applications.assigned_user_id', $userId)
            ->orderBy('applications.updated_at', 'DESC')
            ->findAll();

        $myProjectIds = array_column($metrics['projects'], 'id');
        $recentFiles = [];
        if (!empty($myProjectIds)) {
            $recentFiles = (new ProjectFileModel())
                ->select('project_files.id, project_files.project_id, project_files.original_name, project_files.file_extension, project_files.file_size, project_files.created_at, projects.name as project_name')
                ->join('projects', 'projects.id = project_files.project_id', 'left')
                ->whereIn('project_files.project_id', $myProjectIds)
                ->orderBy('project_files.created_at', 'DESC')
                ->findAll(3);
        }

        return view('dashboard/index', [
            'title' => 'User Dashboard',
            'user_detail' => $user,
            'my_stats' => [
                'active_projects' => $stats['active_projects'],
                'on_time_done' => $stats['on_time_done'],
                'late_done' => $stats['late_done'],
                'total_completed' => $stats['total_completed'],
                'overdue' => $stats['overdue'],
                'risk_urgent' => $stats['risk_urgent'],
                'total_apps_managed' => $stats['total_apps_managed'] ?? 0,
                'completion_rate' => $completionRate
            ],
            'my_active_projects' => $metrics['projects'],
            'my_managed_apps' => $managedApps,
            'recent_project_files' => $recentFiles,
            'completion_chart' => $metrics['chart'],
            'sdlc_distribution' => $metrics['sdlc_distribution'] ?? [],
            'workload_timeline' => $metrics['workload_timeline'] ?? [],
            'show_team_dashboard' => false,
        ]);
    }
}
