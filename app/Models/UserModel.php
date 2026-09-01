<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['role_id', 'name', 'email', 'phone_number', 'job_title', 'password_hash', 'is_active', 'username'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    //mengambil user active + role
    public function getUserByUsername(string $username)
    {
        $escapedUsername = $this->db->escape($username);

        return $this->db->table('users u')
            ->select('u.*, r.role_name, r.category')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where("u.username COLLATE Latin1_General_BIN2 = {$escapedUsername}", null, false)
            ->get()
            ->getRowArray();
    }

    public function getUserByEmail(string $email)
    {
        return $this->db->table('users u')
            ->select('u.*, r.role_name, r.category')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('u.email', $email)
            ->get()
            ->getRowArray();
    }

    public function getActiveTeamMembers(): array
    {
        return $this->db->table('users u')->select('u.id, u.name, u.job_title, r.role_name, r.category')
            ->join('roles r', 'r.id = u.role_id', 'left')->where('u.is_active', 1)
            ->where('r.role_name !=', 'Kepala Departemen')->orderBy('u.name', 'ASC')->get()->getResultArray();
    }

    protected function hashPassword(array $data): array
    {
        if (empty($data['data']['password_hash'])) {
            return $data;
        }

        $password = (string) $data['data']['password_hash'];
        $info = password_get_info($password);

        if ($info['algo'] === null || $info['algo'] === 0) {
            $data['data']['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        return $data;
    }
}

