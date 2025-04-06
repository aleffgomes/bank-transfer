<?php

namespace App\Interfaces\Models;

use App\Entities\Wallet;
use App\Entities\Money;

interface WalletModelInterface
{
    /**
     * Get the wallet for a given user ID.
     *
     * @param int $userId
     * @return Wallet|null
     */
    public function getWalletByUserId(int $userId): ?Wallet;
    
    /**
     * Update the balances of the wallets of the given payer and payee.
     *
     * @param int $payerId The ID of the payer.
     * @param int $payeeId The ID of the payee.
     * @param float|Money $amount The amount to update the balances by.
     * @return bool Whether the update was successful.
     */
    public function updateWalletBalances(int $payerId, int $payeeId, $amount): bool;
}
