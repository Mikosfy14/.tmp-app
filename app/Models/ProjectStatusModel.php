<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectStatusModel extends Model
{
    protected $table = 'project_status';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'status_name',
        'description',
        'sort_order',
        'is_active',
    ];

    public function getActiveOptions(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}
