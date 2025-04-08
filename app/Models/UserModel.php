<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Interfaces\Models\UserModelInterface;
use App\Entities\User;

class UserModel extends Model implements UserModelInterface
{
    protected $table = 'users';
    protected $primaryKey = 'id_user';
    protected $allowedFields = ['name', 'email', 'password', 'cpf_cnpj', 'user_type_id'];
    protected $returnType = 'App\Entities\User';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = true;
    protected $protectFields = true;

    // Configurações de tempo e formato
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function getUserById(int $id): ?User
    {
        return $this->select('users.*, user_types.type_name')
            ->join('user_types', 'users.user_type_id = user_types.id_user_type')
            ->where('id_user', $id)
            ->first();
    }
}
