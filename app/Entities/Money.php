<?php

namespace App\Entities;

class Money
{
    private int $cents;
    
    public function __construct($amount = 0)
    {
        if (is_int($amount)) {
            $this->cents = $amount;
        } elseif (is_float($amount)) {
            $this->cents = (int)round($amount * 100);
        } elseif (is_string($amount)) {
            $this->cents = (int)round((float)$amount * 100);
        } else {
            throw new \InvalidArgumentException('Amount must be a number or string');
        }
    }
    
    public static function fromCents(int $cents): Money
    {
        $money = new self(0);
        $money->cents = $cents;
        return $money;
    }
    
    public function getCents(): int
    {
        return $this->cents;
    }
    
    public function getAmount(): float
    {
        return $this->cents / 100;
    }
    
    public function format(int $decimals = 2, string $decPoint = '.', string $thousandsSep = ','): string
    {
        return number_format($this->getAmount(), $decimals, $decPoint, $thousandsSep);
    }
    
    public function add($amount): Money
    {
        $amountCents = $amount instanceof Money ? $amount->getCents() : (new Money($amount))->getCents();
        return self::fromCents($this->cents + $amountCents);
    }
    
    public function subtract($amount): Money
    {
        $amountCents = $amount instanceof Money ? $amount->getCents() : (new Money($amount))->getCents();
        return self::fromCents($this->cents - $amountCents);
    }
    
    public function isGreaterThanOrEqual($amount): bool
    {
        $amountCents = $amount instanceof Money ? $amount->getCents() : (new Money($amount))->getCents();
        return $this->cents >= $amountCents;
    }
    
    public function __toString(): string
    {
        return (string)$this->getAmount();
    }
} 