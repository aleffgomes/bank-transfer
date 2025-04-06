<?php

namespace App\Interfaces\Services;

interface TransferServiceInterface
{
    public function transfer(int $payerId, int $payeeId, float $amount): array;
}
