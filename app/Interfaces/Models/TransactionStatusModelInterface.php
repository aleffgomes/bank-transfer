<?php

namespace App\Interfaces\Models;

interface TransactionStatusModelInterface
{
    public function getStatusId(string $statusName);
}
