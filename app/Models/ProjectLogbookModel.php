<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectLogbookModel extends Model
{
    protected $table = 'project_logbooks';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = [
        'project_id',
        'user_id',
        'project_status_id',
        'log_type',
        'log_date',
        'achievements',
        'blockers',
        'next_plans',
        'kadept_notes',
        'kadept_reviewed_by',
        'kadept_reviewed_at',
    ];

    /**
     * Mengambil seluruh logbook milik satu project dengan data user dan status SDLC snapshot.
     */
    public function getLogbooksByProjectId(int $projectId): array
    {
        return $this->select('
                project_logbooks.*,
                users.name as author_name,
                users.username as author_username,
                roles.name as role_name,
                roles.category as role_category,
                project_status.status_name as status_name,
                reviewer.name as reviewer_name
            ')
            ->join('users', 'users.id = project_logbooks.user_id', 'left')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->join('project_status', 'project_status.id = project_logbooks.project_status_id', 'left')
            ->join('users as reviewer', 'reviewer.id = project_logbooks.kadept_reviewed_by', 'left')
            ->where('project_logbooks.project_id', $projectId)
            ->orderBy('project_logbooks.log_date', 'DESC')
            ->orderBy('project_logbooks.id', 'DESC')
            ->findAll();
    }
}
