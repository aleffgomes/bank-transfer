<?php

namespace App\Entities;

/**
 * Class Money
 * 
 * A class to handle monetary values with precision,
 * avoiding float precision problems
 */
class Money
{
    /**
     * Value in cents (integer)
     *
     * @var int
     */
    private int $cents;
    
    /**
     * Constructor
     *
     * @param float|int|string $amount Monetary value
     */
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
    
    /**
     * Creates a Money object from a value in cents
     *
     * @param int $cents Value in cents
     * @return Money
     */
    public static function fromCents(int $cents): Money
    {
        $money = new self(0);
        $money->cents = $cents;
        return $money;
    }
    
    /**
     * Returns the value in cents
     *
     * @return int
     */
    public function getCents(): int
    {
        return $this->cents;
    }
    
    /**
     * Returns the value as a float
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->cents / 100;
    }
    
    /**
     * Returns the value formatted as a string
     *
     * @param int $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     * @return string
     */
    public function format(int $decimals = 2, string $decPoint = '.', string $thousandsSep = ','): string
    {
        return number_format($this->getAmount(), $decimals, $decPoint, $thousandsSep);
    }
    
    /**
     * Adds a value to this Money object
     *
     * @param Money|float|int|string $amount
     * @return Money A new Money object with the result
     */
    public function add($amount): Money
    {
        $amountCents = $amount instanceof Money ? $amount->getCents() : (new Money($amount))->getCents();
        return self::fromCents($this->cents + $amountCents);
    }
    
    /**
     * Subtracts a value from this Money object
     *
     * @param Money|float|int|string $amount
     * @return Money A new Money object with the result
     */
    public function subtract($amount): Money
    {
        $amountCents = $amount instanceof Money ? $amount->getCents() : (new Money($amount))->getCents();
        return self::fromCents($this->cents - $amountCents);
    }
    
    /**
     * Checks if this value is greater than or equal to another
     *
     * @param Money|float|int|string $amount
     * @return bool
     */
    public function isGreaterThanOrEqual($amount): bool
    {
        $amountCents = $amount instanceof Money ? $amount->getCents() : (new Money($amount))->getCents();
        return $this->cents >= $amountCents;
    }
    
    /**
     * Converts to string
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->getAmount();
    }
} 