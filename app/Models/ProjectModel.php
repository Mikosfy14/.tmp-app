<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectModel extends Model
{
    protected $table = 'projects';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields    = [
        'project_code',
        'name',
        'notes',
        'project_status_id',
        'start_date',
        'end_date',
        'unit_testing_date',
        'sit_date',
        'uat_date',
        'promote_date',
        'assigned_to'
    ];

    /** Ambil project beserta user penanggung jawab dari kolom assigned_to. */
    public function getProjectsWithAssignees($statusFilter = null, $keyword = null, ?int $userId = null, bool $includeAll = false, ?array $dateRange = null): array
    {
        $builder = $this->select('projects.*, project_status.status_name AS status, project_status.status_name, project_status.sort_order AS status_sort_order')
            ->join('project_status', 'project_status.id = projects.project_status_id', 'left');

        if (!$includeAll && !empty($userId)) {
            $this->whereAssignedToContains($builder, $userId);
        }

        if(!empty($statusFilter)) {
            $builder->where('projects.project_status_id', (int) $statusFilter);
        }

        if(!empty($keyword)) {
            $escapedKeyword = $this->db->escape('%' . $this->db->escapeLikeString((string) $keyword) . '%');
            $builder->groupStart()
                    ->like('projects.name', $keyword)
                    ->orLike('projects.project_code', $keyword)
                    ->orWhere("EXISTS (SELECT 1 FROM users assigned_user WHERE CHARINDEX(',' + CAST(assigned_user.id AS VARCHAR(20)) + ',', ',' + ISNULL(projects.assigned_to, '') + ',') > 0 AND assigned_user.name LIKE {$escapedKeyword})", null, false)
                    ->groupEnd();
        }

        if (!empty($dateRange['start_date']) && !empty($dateRange['end_date'])) {
            $builder->where('projects.start_date <=', $dateRange['end_date'])
                ->where('projects.end_date >=', $dateRange['start_date']);
        }

        $projects = $this->orderBy('projects.id', 'DESC')->paginate(5, 'projects');
        return $this->attachAssignees($projects);
    }

    /** Ambil detail project beserta user penanggung jawab dari kolom assigned_to. */
    public function getProjectDetail($id, ?int $userId = null, bool $includeAll = false): ?array
    {
        $builder = $this->builder();
        $builder->select('projects.*, project_status.status_name AS status, project_status.status_name, project_status.sort_order AS status_sort_order')
            ->join('project_status', 'project_status.id = projects.project_status_id', 'left')
            ->where('projects.id', $id);

        if (!$includeAll && !empty($userId)) {
            $this->whereAssignedToContains($builder, $userId);
        }

        $project = $builder->get()->getRowArray();
        if (!$project) return null;

        $project['assigned_users'] = $this->getUsersFromAssignedTo($project['assigned_to'] ?? '');
        return $project;
    }

    private function whereAssignedToContains($builder, int $userId): void
    {
        $userId = (int) $userId;

        if ($userId <= 0) {
            $builder->where('1 = 0', null, false);
            return;
        }

        $builder->where("CHARINDEX(',$userId,', ',' + ISNULL(projects.assigned_to, '') + ',') >", 0, false);
    }

    private function attachAssignees(array $projects): array
    {
        foreach ($projects as &$project) {
            $project['assigned_users'] = $this->getUsersFromAssignedTo($project['assigned_to'] ?? '');
        }

        return $projects;
    }

    private function getUsersFromAssignedTo(?string $assignedTo): array
    {
        $userIds = $this->parseAssignedTo($assignedTo);

        if (empty($userIds)) {
            return [];
        }

        $users = $this->db->table('users')
            ->select('id as user_id, name, email, job_title')
            ->whereIn('id', $userIds)
            ->get()
            ->getResultArray();

        $usersById = [];
        foreach ($users as $user) {
            $usersById[(int) $user['user_id']] = $user;
        }

        $orderedUsers = [];
        foreach ($userIds as $userId) {
            if (isset($usersById[$userId])) {
                $orderedUsers[] = $usersById[$userId];
            }
        }

        return $orderedUsers;
    }

    private function parseAssignedTo(?string $assignedTo): array
    {
        if (empty($assignedTo)) {
            return [];
        }

        $ids = array_map('trim', explode(',', $assignedTo));
        $ids = array_filter($ids, static fn ($id) => ctype_digit($id) && (int) $id > 0);

        return array_values(array_unique(array_map('intval', $ids)));
    }
}
