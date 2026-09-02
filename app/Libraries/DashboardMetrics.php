<?php

namespace App\Libraries;

use App\Models\ProjectModel;
use App\Models\UserModel;
use DateTimeImmutable;

/** Provides database-backed personal and department project metrics. */
class DashboardMetrics
{
    private ProjectModel $projectModel;
    private UserModel $userModel;

    public function __construct()
    {
        helper('deadline');
        $this->projectModel = new ProjectModel();
        $this->userModel = new UserModel();
    }

    public function personal(int $userId): array
    {
        $projects = $this->projectModel->getDashboardProjects($userId, false);
        $metrics = $this->buildMetrics($projects);
        
        $appModel = new \App\Models\ApplicationModel();
        $metrics['stats']['total_apps_managed'] = $appModel->where('assigned_user_id', $userId)->countAllResults();
        
        return $metrics;
    }

    public function team(?array $dateRange = null): array
    {
        $projects = $this->projectModel->getDashboardProjects(null, true, $dateRange);
        $metrics = $this->buildMetrics($projects);
        $users = $this->userModel->getActiveTeamMembers();
        $members = [];
        foreach ($users as $user) {
            $memberProjects = array_values(array_filter($projects, static function (array $project) use ($user): bool {
                return in_array((int) $user['id'], array_map('intval', explode(',', (string) ($project['assigned_to'] ?? ''))), true);
            }));
            $memberMetrics = $this->buildMetrics($memberProjects);
            $memberTotal = count($memberProjects);
            $memberCompleted = $memberMetrics['stats']['total_completed'];
            $members[] = ['id' => (int) $user['id'], 'name' => $user['name'], 'role' => $user['role_name'] ?? '-', 'job' => $user['job_title'] ?? '-', 'active_tasks' => $memberMetrics['stats']['active_projects'], 'completed' => $memberCompleted, 'late' => $memberMetrics['stats']['late_done'], 'overdue' => $memberMetrics['stats']['overdue'], 'completion_rate' => $memberTotal > 0 ? round(($memberCompleted / $memberTotal) * 100, 1) : 0];
        }
        $metrics['members'] = $members;
        return $metrics;
    }

    private function buildMetrics(array $projects): array
    {
        $stats = ['active_projects' => 0, 'on_time_done' => 0, 'late_done' => 0, 'total_completed' => 0, 'overdue' => 0, 'risk_urgent' => 0];
        $overview = [];
        $sdlc_distribution = [];
        $workload_timeline = [];
        
        // Initialize rolling 6-month window (from 5 months ago to current month)
        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = date('Y-m', strtotime("-{$i} months"));
            $monthly[$m] = ['on_time' => 0, 'late' => 0];
        }
        
        foreach ($projects as $project) {
            $completed = $this->isCompleted($project);
            $deadline = $this->deadline($project['end_date'] ?? null, $completed);
            $statusName = $project['status'] ?? 'Unknown';
            
            if ($completed) {
                $stats['total_completed']++;
                if (!empty($project['promote_date']) && !empty($project['end_date']) && $project['promote_date'] <= $project['end_date']) {
                    $stats['on_time_done']++;
                } else {
                    $stats['late_done']++;
                }
                
                if (!empty($project['promote_date'])) {
                    $month = date('Y-m', strtotime($project['promote_date']));
                    $type = !empty($project['end_date']) && $project['promote_date'] <= $project['end_date'] ? 'on_time' : 'late';
                    if (isset($monthly[$month])) {
                        $monthly[$month][$type]++;
                    }
                }
            } else {
                $stats['active_projects']++;
                if ($deadline['class'] === 'danger' && $deadline['label'] === 'Overdue') $stats['overdue']++;
                if (in_array($deadline['label'], ['Risk', 'Urgent', 'Critical'], true)) $stats['risk_urgent']++;
                
                // SDLC Phase Distribution
                $sdlc_distribution[$statusName] = ($sdlc_distribution[$statusName] ?? 0) + 1;
                
                // Workload Timeline (Gantt Chart Data)
                if (!empty($project['start_date']) && !empty($project['end_date'])) {
                    $workload_timeline[] = [
                        'name' => $project['name'],
                        'start' => $project['start_date'],
                        'end' => $project['end_date'],
                        'status' => $statusName,
                        'class' => $this->statusClass($statusName)
                    ];
                }
            }
            $project['deadline_label'] = $deadline['label'];
            $project['deadline_class'] = $deadline['class'];
            $project['status_class'] = $this->statusClass($statusName);
            $overview[] = $project;
        }
        
        $labels = array_keys($monthly);
        $chart = [
            'months' => array_map(static fn ($month) => date('M Y', strtotime($month . '-01')), $labels),
            'on_time' => [],
            'late' => []
        ];
        foreach ($labels as $month) {
            $chart['on_time'][] = $monthly[$month]['on_time'];
            $chart['late'][] = $monthly[$month]['late'];
        }
        
        return [
            'stats' => $stats, 
            'projects' => $overview, 
            'chart' => $chart,
            'sdlc_distribution' => $sdlc_distribution,
            'workload_timeline' => $workload_timeline
        ];
    }

    private function isCompleted(array $project): bool
    {
        return is_project_completed($project);
    }

    private function deadline(?string $endDate, bool $completed): array
    {
        return get_deadline_status($endDate, $completed);
    }

    private function statusClass(string $status): string
    {
        return match (strtolower(trim($status))) {
            'planning' => 'bg-secondary',
            'defining' => 'bg-info',
            'designing' => 'bg-primary',
            'building', 'in progress' => 'bg-warning text-dark',
            'testing', 'testing/qa' => 'bg-danger',
            'deployment', 'completed', 'selesai', 'done' => 'bg-success',
            default => 'bg-secondary',
        };
    }
}
