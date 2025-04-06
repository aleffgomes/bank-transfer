<?php

namespace App\Interfaces\Models;

use App\Entities\Money;

interface TransactionModelInterface
{
    /**
     * Save a transaction.
     *
     * @param int $payerId The ID of the payer.
     * @param int $payeeId The ID of the payee.
     * @param float|Money $amount The amount of the transaction.
     * @param int $statusId The ID of the transaction status.
     * @return int The ID of the saved transaction.
     */
    public function saveTransaction(int $payerId, int $payeeId, $amount, int $statusId): int;
    
    /**
     * Update the status of a transaction.
     *
     * @param int $transactionId The ID of the transaction.
     * @param int $statusId The ID of the transaction status.
     * @return bool Whether the update was successful.
     */
    public function updateTransactionStatus(int $transactionId, int $statusId): bool;
}
