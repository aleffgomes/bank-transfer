<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Interfaces\Models\TransactionModelInterface;
use App\Entities\Money;

class TransactionModel extends Model implements TransactionModelInterface
{
    protected $table = 'transactions';
    protected $primaryKey = 'id_transaction';
    protected $allowedFields = ['payer_id', 'payee_id', 'amount', 'status_id'];
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    // Configurações de tempo e formato
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function saveTransaction(int $payerId, int $payeeId, $amount, int $statusId): int
    {
        // Convert to Money object if it's not already one
        $moneyAmount = $amount instanceof Money ? $amount : new Money($amount);
        
        return $this->insert([
            'payer_id' => $payerId,
            'payee_id' => $payeeId,
            'amount' => $moneyAmount->getAmount(),
            'status_id' => $statusId
        ]);
    }

    public function updateTransactionStatus(int $transactionId, int $statusId): bool
    {
        return $this->update($transactionId, [
            'status_id' => $statusId
        ]);
    }
}
