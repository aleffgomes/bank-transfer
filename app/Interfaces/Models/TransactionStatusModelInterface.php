<?php

namespace App\Interfaces\Models;

interface TransactionStatusModelInterface
{
    /**
     * Get the ID of a transaction status by its name.
     *
     * @param string $statusName The status name.
     * @return int The ID of the status.
     */
    public function getStatusId(string $statusName): int;
}
