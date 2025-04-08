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

    // Configurações de tempo e formato
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getStatusId(string $statusName): int
    {
        return $this->where('status_name', $statusName)->first()['id_transaction_status'];
    }
}
