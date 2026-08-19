<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectFileModel extends Model
{
    protected $table = 'project_files';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'project_id',
        'original_name',
        'mime_type',
        'file_extension',
        'file_size',
        'file_data',
        'uploaded_by',
        'created_at',
    ];

    public function getFilesByProject(int $projectId): array
    {
        return $this->select('project_files.id, project_files.project_id, project_files.original_name, project_files.mime_type, project_files.file_extension, project_files.file_size, project_files.uploaded_by, project_files.created_at, users.name AS uploaded_by_name')
            ->join('users', 'users.id = project_files.uploaded_by', 'left')
            ->where('project_files.project_id', $projectId)
            ->orderBy('project_files.created_at', 'DESC')
            ->findAll();
    }

    public function getFileForDownload(int $id): ?array
    {
        return $this->select('project_files.*, users.name AS uploaded_by_name')
            ->join('users', 'users.id = project_files.uploaded_by', 'left')
            ->where('project_files.id', $id)
            ->first();
    }
}
