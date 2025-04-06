<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Interfaces\Models\TransactionStatusModelInterface;

class TransactionStatusModel extends Model implements TransactionStatusModelInterface
{
    protected $table = 'transaction_status';
    protected $primaryKey = 'id_transaction_status';
    protected $allowedFields = ['status_name'];
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

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

    public function getStatusId(string $statusName): int
    {
        return $this->where('status_name', $statusName)->first()['id_transaction_status'];
    }
}
