<?php

namespace App\Models;

use CodeIgniter\Model;

class DatabaseTypeModel extends Model
{
    protected $table = 'database_types';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'type_name',
        'description',
        'sort_order',
        'is_active',
    ];

    /**
     * Ambil semua opsi tipe database yang aktif terurut berdasarkan urutan tampilan.
     */
    public function getActiveOptions(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}
