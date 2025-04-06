<?php

namespace Tests\Services;

use CodeIgniter\Test\CIUnitTestCase;
use App\Entities\Money;

class MoneyEntityTest extends CIUnitTestCase
{
    public function testCreateMoney()
    {
        $money1 = new Money(100.50);
        $this->assertEquals(10050, $money1->getCents());
        $this->assertEquals(100.50, $money1->getAmount());
        
        $money2 = new Money(100);
        $this->assertEquals(100, $money2->getCents());
        $this->assertEquals(1.00, $money2->getAmount());
        
        $money3 = new Money("99.99");
        $this->assertEquals(9999, $money3->getCents());
        $this->assertEquals(99.99, $money3->getAmount());
        
        $money4 = Money::fromCents(1234);
        $this->assertEquals(1234, $money4->getCents());
        $this->assertEquals(12.34, $money4->getAmount());
    }
    
    public function testMoneyAddition()
    {
        $money1 = new Money(10.50);
        $money2 = new Money(5.25);
        
        $sum = $money1->add($money2);
        $this->assertEquals(15.75, $sum->getAmount());
        $this->assertEquals(1575, $sum->getCents());
        
        $sum2 = $money1->add(2.50);
        $this->assertEquals(13.00, $sum2->getAmount());
        $this->assertEquals(1300, $sum2->getCents());
        
        $sum3 = $money1->add("1.75");
        $this->assertEquals(12.25, $sum3->getAmount());
        $this->assertEquals(1225, $sum3->getCents());
    }
    
    public function testMoneySubtraction()
    {
        $money1 = new Money(20.00);
        $money2 = new Money(5.50);
        
        $diff = $money1->subtract($money2);
        $this->assertEquals(14.50, $diff->getAmount());
        $this->assertEquals(1450, $diff->getCents());
        
        $diff2 = $money1->subtract(10.75);
        $this->assertEquals(9.25, $diff2->getAmount());
        $this->assertEquals(925, $diff2->getCents());
    }
    
    public function testIsGreaterThanOrEqual()
    {
        $money1 = new Money(100.00);
        $money2 = new Money(50.00);
        $money3 = new Money(100.00);
        
        $this->assertTrue($money1->isGreaterThanOrEqual($money2));
        $this->assertTrue($money1->isGreaterThanOrEqual($money3));
        $this->assertFalse($money2->isGreaterThanOrEqual($money1));
    }
    
    public function testMoneyFormatting()
    {
        $money = new Money(1234.56);
        
        $this->assertEquals("1,234.56", $money->format());
        $this->assertEquals("1.234,56", $money->format(2, ',', '.'));
        $this->assertEquals("1234.56", $money->format(2, '.', ''));
    }
    
    public function testMoneyStringConversion()
    {
        $money = new Money(99.99);
        $this->assertEquals("99.99", (string)$money);
    }
    
    public function testMoneyPrecision()
    {
        $money1 = new Money(0.1);
        $money2 = new Money(0.2);
        
        $sum = $money1->add($money2);
        $this->assertEquals(0.3, $sum->getAmount());
        $this->assertEquals(30, $sum->getCents());
        
        $money3 = new Money(0.01);
        $money4 = new Money(0.02);
        
        $sum2 = $money3->add($money4);
        $this->assertEquals(0.03, $sum2->getAmount());
        $this->assertEquals(3, $sum2->getCents());
        
        $price1 = new Money(10.23);
        $price2 = new Money(20.48);
        
        $totalPrice = $price1->add($price2);
        $this->assertEquals(30.71, $totalPrice->getAmount());
        $this->assertEquals(3071, $totalPrice->getCents());
    }
    
    public function testInvalidArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Money([]);
    }
} 