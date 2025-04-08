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
    
    private ?Money $moneyBalance = null;
    
    public function getBalanceAsMoney(): Money
    {
        if ($this->moneyBalance === null) {
            $this->moneyBalance = new Money($this->attributes['balance']);
        }
        
        return $this->moneyBalance;
    }
    
    public function setBalanceFromMoney(Money $money): self
    {
        $this->moneyBalance = $money;
        $this->attributes['balance'] = $money->getAmount();
        return $this;
    }

    public function hasSufficientBalance($amount): bool
    {
        // If amount is not a Money object, convert it
        if (!($amount instanceof Money)) {
            $amount = new Money($amount);
        }
        
        return $this->getBalanceAsMoney()->isGreaterThanOrEqual($amount);
    }

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

    public function credit($amount): self
    {
        $moneyAmount = $amount instanceof Money ? $amount : new Money($amount);
        
        $newBalance = $this->getBalanceAsMoney()->add($moneyAmount);
        $this->setBalanceFromMoney($newBalance);
        
        return $this;
    }
} 