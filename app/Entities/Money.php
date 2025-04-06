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
            // If it's already an integer, we assume it's in cents
            $this->cents = $amount;
        } elseif (is_float($amount)) {
            // Convert float to cents
            $this->cents = (int)round($amount * 100);
        } elseif (is_string($amount)) {
            // Convert string to cents
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
     * Multiplies this Money object by a factor
     *
     * @param float|int $factor
     * @return Money A new Money object with the result
     */
    public function multiply($factor): Money
    {
        return self::fromCents((int)round($this->cents * $factor));
    }
    
    /**
     * Divides this Money object by a divisor
     *
     * @param float|int $divisor
     * @return Money A new Money object with the result
     */
    public function divide($divisor): Money
    {
        if ($divisor == 0) {
            throw new \InvalidArgumentException('Cannot divide by zero');
        }
        return self::fromCents((int)round($this->cents / $divisor));
    }
    
    /**
     * Checks if this value is greater than another
     *
     * @param Money|float|int|string $amount
     * @return bool
     */
    public function isGreaterThan($amount): bool
    {
        $amountCents = $amount instanceof Money ? $amount->getCents() : (new Money($amount))->getCents();
        return $this->cents > $amountCents;
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
     * Checks if this value is less than another
     *
     * @param Money|float|int|string $amount
     * @return bool
     */
    public function isLessThan($amount): bool
    {
        $amountCents = $amount instanceof Money ? $amount->getCents() : (new Money($amount))->getCents();
        return $this->cents < $amountCents;
    }
    
    /**
     * Checks if this value is less than or equal to another
     *
     * @param Money|float|int|string $amount
     * @return bool
     */
    public function isLessThanOrEqual($amount): bool
    {
        $amountCents = $amount instanceof Money ? $amount->getCents() : (new Money($amount))->getCents();
        return $this->cents <= $amountCents;
    }
    
    /**
     * Checks if this value is equal to another
     *
     * @param Money|float|int|string $amount
     * @return bool
     */
    public function isEqualTo($amount): bool
    {
        $amountCents = $amount instanceof Money ? $amount->getCents() : (new Money($amount))->getCents();
        return $this->cents === $amountCents;
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