<?php

namespace App\Interfaces\Services;

interface TransferServiceInterface
{
    /**
     * Transfer money from one user to another.
     *
     * @param int $payerId The ID of the payer.
     * @param int $payeeId The ID of the payee.
     * @param float $amount The amount to transfer.
     * @return array Response containing status and message.
     */
    public function transfer(int $payerId, int $payeeId, float $amount): array;
}
