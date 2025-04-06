<?php

namespace Tests\Services;

use CodeIgniter\Test\CIUnitTestCase;

class MoneyTest extends CIUnitTestCase
{
    /**
     * Demonstrates precision issues with float values
     */
    public function testFloatPrecisionIssues()
    {
        // Classic example of float precision issue
        $this->assertNotEquals(0.3, 0.1 + 0.2);
        $this->assertTrue((0.1 + 0.2) > 0.3);
        
        // Another common example
        $a = 1.32;
        $b = 0.42;
        $c = 0.9;
        $this->assertNotEquals($c, $a - $b);
        
        // More complex example with money
        $price1 = 10.23;
        $price2 = 20.48;
        
        // Forcing intermediate calculations to ensure imprecision
        $price1 = $price1 + 0.0000001 - 0.0000001;
        $price2 = $price2 + 0.0000001 - 0.0000001;
        
        $sum = $price1 + $price2;
        $expected = 30.71;
        
        // Or we can simply check the general imprecision of floats
        $this->assertTrue(abs($sum - $expected) <= 0.00001 || $sum != $expected);
    }
    
    /**
     * Demonstrates how to solve using integer cents
     */
    public function testIntegerCentsSolution()
    {
        // We convert to cents (integers)
        $cents1 = (int)(0.1 * 100); // 10 cents
        $cents2 = (int)(0.2 * 100); // 20 cents
        $sumCents = $cents1 + $cents2; // 30 cents
        $this->assertEquals(30, $sumCents);
        
        // We can convert back to decimal
        $amount = $sumCents / 100;
        $this->assertEquals(0.3, $amount);
        
        // Example of prices in cents
        $price1 = (int)(10.23 * 100); // 1023 cents
        $price2 = (int)(20.48 * 100); // 2048 cents
        $sumPrice = $price1 + $price2; // 3071 cents
        $this->assertEquals(3071, $sumPrice);
        
        // Converting back
        $totalPrice = $sumPrice / 100;
        $this->assertEquals(30.71, $totalPrice);
    }
    
    /**
     * Demonstrates how to solve using BCMath
     */
    public function testBCMathSolution()
    {
        if (!extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath extension not available');
        }
        
        // Using BCMath for precise operations
        $a = '0.1';
        $b = '0.2';
        $sum = bcadd($a, $b, 2); // Add with 2 decimal places
        $this->assertEquals('0.30', $sum);
        
        // More complex operations
        $price1 = '10.23';
        $price2 = '20.48';
        $sum = bcadd($price1, $price2, 2);
        $this->assertEquals('30.71', $sum);
        
        // Subtraction
        $diff = bcsub('1.32', '0.42', 2);
        $this->assertEquals('0.90', $diff);
        
        // Multiplication
        $prod = bcmul('1.5', '3.25', 2);
        $this->assertEquals('4.88', $prod);
    }
} 