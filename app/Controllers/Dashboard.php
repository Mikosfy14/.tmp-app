<?php

namespace App\Controllers;

use App\Libraries\DashboardMetrics;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $user = (new UserModel())->getUserByUsername((string) session()->get('username')) ?: [];
        $metrics = (new DashboardMetrics())->personal((int) session()->get('user_id'));
        $stats = $metrics['stats'];

        $totalCompleted = $stats['total_completed'] ?? 0;
        $totalProjects = $totalCompleted + ($stats['active_projects'] ?? 0);
        $completionRate = $totalProjects > 0 ? round(($stats['on_time_done'] / $totalCompleted) * 100, 1) : 0; // Or just on_time_done / totalCompleted ? Wait, total_completed / total projects ? No, usually completion rate is how many are completed vs active, or how many on_time vs completed. Let's use total_completed / totalProjects.
        $completionRate = $totalProjects > 0 ? round(($totalCompleted / $totalProjects) * 100, 1) : 0;

        return view('dashboard/index', [
            'title' => 'User Dashboard', 'user_detail' => $user,
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
            'completion_chart' => $metrics['chart'],
            'sdlc_distribution' => $metrics['sdlc_distribution'] ?? [],
            'workload_timeline' => $metrics['workload_timeline'] ?? [],
            'my_timeline' => $this->timeline($metrics['projects']),
            'show_team_dashboard' => false,
        ]);
    }

    private function timeline(array $projects): array
    {
        $timeline = [];
        foreach ($projects as $project) {
            if (($project['deadline_label'] ?? '') === 'On Track' || empty($project['end_date'])) continue;
            $timeline[] = ['title' => $project['name'], 'date' => date('d M Y', strtotime($project['end_date'])), 'badge' => $project['deadline_label'], 'class' => $project['deadline_class']];
        }
        return array_slice($timeline, 0, 5);
    }
}
