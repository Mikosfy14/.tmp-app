<?php

namespace App\Models;

use CodeIgniter\Model;

class ApplicationModel extends Model
{
    protected $table = 'applications';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'app_component',
        'description',
        'app_type',
        'arch_type',
        'criticality_recovery_id',
        'access_type',
        'login_auth',
        'platform',
        'url_prod',
        'url_dev',
        'url_uat',
        'development_type',
        'vendor',
        'license_scheme',
        'deployment_type',
        'business_owner',
        'system_owner',
        'has_source_code',
        'assigned_user_id',
    ];

    public function getApplicationsWithDetails(?int $criticalityFilter = null, ?string $keyword = null): array
    {
        $builder = $this->builder();
        $builder->select(
            'applications.*, ' .
            'criticality_recovery.criticality_name AS criticality_recovery, ' .
            'criticality_recovery.criticality_name, ' .
            'criticality_recovery.description AS criticality_recovery_description, ' .
            'criticality_recovery.sort_order AS criticality_sort_order, ' .
            'users.name AS assigned_user_name, ' .
            'users.email AS assigned_user_email, ' .
            'users.job_title AS assigned_user_job_title'
        );
        $builder->join('criticality_recovery', 'criticality_recovery.id = applications.criticality_recovery_id', 'left');
        $builder->join('users', 'users.id = applications.assigned_user_id', 'left');

        if (!empty($criticalityFilter)) {
            $builder->where('applications.criticality_recovery_id', $criticalityFilter);
        }

        if (!empty($keyword)) {
            $builder->groupStart()
                ->like('applications.app_component', $keyword)
                ->orLike('applications.description', $keyword)
                ->orLike('applications.business_owner', $keyword)
                ->orLike('applications.system_owner', $keyword)
                ->orLike('applications.url_prod', $keyword)
                ->orLike('applications.url_dev', $keyword)
                ->orLike('applications.url_uat', $keyword)
                ->orLike('users.name', $keyword)
                ->groupEnd();
        }

        return $builder
            ->orderBy('applications.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getApplicationDetail(int $id): ?array
    {
        $builder = $this->builder();
        $builder->select(
            'applications.*, ' .
            'criticality_recovery.criticality_name AS criticality_recovery, ' .
            'criticality_recovery.criticality_name, ' .
            'criticality_recovery.description AS criticality_recovery_description, ' .
            'criticality_recovery.sort_order AS criticality_sort_order, ' .
            'users.name AS assigned_user_name, ' .
            'users.email AS assigned_user_email, ' .
            'users.job_title AS assigned_user_job_title'
        );
        $builder->join('criticality_recovery', 'criticality_recovery.id = applications.criticality_recovery_id', 'left');
        $builder->join('users', 'users.id = applications.assigned_user_id', 'left');
        $builder->where('applications.id', $id);

        return $builder->get()->getRowArray();
    }
}
