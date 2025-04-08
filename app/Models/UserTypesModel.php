<?php

namespace App\Models;

use CodeIgniter\Model;

class UserTypesModel extends Model
{
    protected $table = 'user_types';
    protected $primaryKey = 'id_user_type';
    protected $allowedFields = ['type_name'];
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = true;
    protected $protectFields = true;

    // Configurações de tempo e formato
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
}
