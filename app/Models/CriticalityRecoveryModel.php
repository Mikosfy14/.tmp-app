<?php

namespace App\Models;

use CodeIgniter\Model;

class CriticalityRecoveryModel extends Model
{
    protected $table = 'criticality_recovery';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'criticality_name',
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
