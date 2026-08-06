<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['role_id', 'name', 'email', 'phone_number', 'job_title', 'password_hash', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    //mengambil user active + role
    public function getUserByEmail(string $email)
    {
        return $this->db->table('users u')
            ->select('u.*, r.role_name, r.category')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('u.email', $email)
            ->where('u.is_active', 1)
            ->get()
            ->getRowArray();
    }
}