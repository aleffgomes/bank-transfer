<?php

namespace Tests\Services;

use CodeIgniter\Test\CIUnitTestCase;
use App\Entities\Wallet;

class WalletServiceTest extends CIUnitTestCase
{
    public function testHasSufficientBalance()
    {
        $wallet = new Wallet([
            'id_wallet' => 1,
            'user_id' => 1,
            'balance' => 1000.00
        ]);

        $this->assertTrue($wallet->hasSufficientBalance(1000.00));
        $this->assertTrue($wallet->hasSufficientBalance(999.99));
        $this->assertFalse($wallet->hasSufficientBalance(1000.01));
    }

    public function testDebit()
    {
        $wallet = new Wallet([
            'id_wallet' => 1,
            'user_id' => 1,
            'balance' => 1000.00
        ]);

        $wallet->debit(500.00);
        $this->assertEquals(500.00, $wallet->balance);

        $wallet->debit(250.00);
        $this->assertEquals(250.00, $wallet->balance);
    }

    public function testDebitInsufficientBalance()
    {
        $wallet = new Wallet([
            'id_wallet' => 1,
            'user_id' => 1,
            'balance' => 100.00
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient balance');
        $wallet->debit(200.00);
    }

    public function testCredit()
    {
        $wallet = new Wallet([
            'id_wallet' => 1,
            'user_id' => 1,
            'balance' => 1000.00
        ]);

        $wallet->credit(500.00);
        $this->assertEquals(1500.00, $wallet->balance);

        $wallet->credit(250.50);
        $this->assertEquals(1750.50, $wallet->balance);
    }

    public function testFloatPrecisionIssues()
    {
        $wallet = new Wallet([
            'id_wallet' => 1,
            'user_id' => 1,
            'balance' => 0.00
        ]);

        // Demonstrating precision issues with floats
        $wallet->credit(0.1);
        $wallet->credit(0.2);
        
        // 0.1 + 0.2 may not be exactly 0.3 with floats
        $this->assertNotEquals(0.3, 0.1 + 0.2); // This shows the precision problem
        
        // The solution using integers in cents would not have this problem
        $inCents1 = 10; // 0.1 in cents
        $inCents2 = 20; // 0.2 in cents
        $this->assertEquals(30, $inCents1 + $inCents2); // Precise sum in cents
    }
} 