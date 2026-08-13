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
        'status',
        'progress',
        'start_date',
        'end_date',
        'unit_testing_date',
        'sit_date',
        'uat_date',
        'promote_date'
    ];

    /**Ambil semua project beserta list developer(PIC) and filter status */
    public function getProjectsWithDevelopers($statusFilter = null, $keyword = null)
    {
        $builder = $this->builder();
        $builder->select('projects.*');

        if(!empty($statusFilter)) {
            $builder->where('projects.status', $statusFilter);
        }

        if(!empty($keyword)) {
            $builder->groupStart()
                    ->like('projects.name', $keyword)
                    ->orLike('projects.projects_code', $keyword)
                    ->groupEnd();
        }

        $projects = $builder->orderBy('projects.id', 'DESC')->get()->getResultArray();

        //ambil developer untuk setiap project
        $db = \Config\Database::connect();
        foreach ($projects as &$prj) {
            $devs = $db->table('project_developers pd')
                       ->select('u.id as user_id, u.name, u.job_title')
                       ->join('users u', 'u.id = pd.user_id')
                       ->where('pd.project_id', $prj['id'])
                       ->get()
                       ->getResultArray();
            $prj['developers'] = $devs;
        }

        return $projects;
    }

    /**ambil detail project beserta developer yang bertanggung jawab */
    public function getProjectDetail($id)
    {
        $project = $this->find($id);
        if (!$project) return null;

        $db = \Config\Database::connect();
        $project['developers'] = $db->table('project_developers pd')
                                     ->select('u.id as user_id, u.name, u.email, u.job_title')
                                     ->join('users u', 'u.id = pd.user_id')
                                     ->where('pd.project_id', $id)
                                     ->get()
                                     ->getResultArray();
        return $project;
    }
}   