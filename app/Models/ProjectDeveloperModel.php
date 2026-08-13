<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectDeveloperModel extends Model
{
    protected $table = 'project_developers';
    protected $allowedFields = ['project_id', 'user_id'];
    protected $useTimestamps = false;
}