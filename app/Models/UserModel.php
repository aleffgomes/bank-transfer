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

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get user by ID.
     *
     * @param int $id The user ID.
     * @return User|null The user data or null if not found.
     */
    public function getUserById(int $id): ?User
    {
        $user = $this->select('users.*, user_types.name as type_name')
            ->join('user_types', 'users.user_type_id = user_types.id_user_type')
            ->where('id_user', $id)
            ->first();
            
        return $user;
    }
}
