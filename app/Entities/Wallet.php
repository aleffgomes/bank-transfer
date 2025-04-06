<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Wallet extends Entity
{
    protected $attributes = [
        'id_wallet' => null,
        'user_id' => null,
        'balance' => 0,
        'created_at' => null,
        'updated_at' => null,
    ];

    protected $casts = [
        'id_wallet' => 'integer',
        'user_id' => 'integer',
        'balance' => 'float',
    ];
    
    /**
     * Internal money object for precise operations
     * 
     * @var Money
     */
    private ?Money $moneyBalance = null;
    
    /**
     * Returns the balance as a Money object
     * 
     * @return Money
     */
    public function getBalanceAsMoney(): Money
    {
        if ($this->moneyBalance === null) {
            $this->moneyBalance = new Money($this->attributes['balance']);
        }
        
        return $this->moneyBalance;
    }
    
    /**
     * Sets the balance from a Money object
     * 
     * @param Money $money
     * @return self
     */
    public function setBalanceFromMoney(Money $money): self
    {
        $this->moneyBalance = $money;
        $this->attributes['balance'] = $money->getAmount();
        return $this;
    }

    /**
     * Check if the wallet has sufficient balance for a transaction.
     * 
     * @param float|Money $amount Amount to be checked
     * @return bool True if there is sufficient balance
     */
    public function hasSufficientBalance($amount): bool
    {
        // If amount is not a Money object, convert it
        if (!($amount instanceof Money)) {
            $amount = new Money($amount);
        }
        
        return $this->getBalanceAsMoney()->isGreaterThanOrEqual($amount);
    }

    /**
     * Debits an amount from the wallet
     *
     * @param float|Money $amount
     * @return self
     */
    public function debit($amount): self
    {
        $moneyAmount = $amount instanceof Money ? $amount : new Money($amount);
        
        if (!$this->hasSufficientBalance($moneyAmount)) {
            throw new \Exception('Insufficient balance');
        }

        $newBalance = $this->getBalanceAsMoney()->subtract($moneyAmount);
        $this->setBalanceFromMoney($newBalance);
        
        return $this;
    }

    /**
     * Credits an amount to the wallet
     *
     * @param float|Money $amount
     * @return self
     */
    public function credit($amount): self
    {
        $moneyAmount = $amount instanceof Money ? $amount : new Money($amount);
        
        $newBalance = $this->getBalanceAsMoney()->add($moneyAmount);
        $this->setBalanceFromMoney($newBalance);
        
        return $this;
    }
} 