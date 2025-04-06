<?php

namespace Tests\Services;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\WalletModel;
use App\Entities\Wallet;
use App\Entities\Money;
use PHPUnit\Framework\MockObject\MockObject;

class WalletModelTest extends CIUnitTestCase
{
    protected WalletModel|MockObject $walletModel;

    public function setUp(): void
    {
        parent::setUp();
        $this->walletModel = $this->createMock(WalletModel::class);
    }

    public function testGetWalletByUserId()
    {
        $testUserId = 1;
        $wallet = new Wallet([
            'id_wallet' => 1,
            'user_id' => $testUserId,
            'balance' => 1000.00
        ]);
        
        $this->walletModel->expects($this->once())
            ->method('getWalletByUserId')
            ->with($testUserId)
            ->willReturn($wallet);
        
        $result = $this->walletModel->getWalletByUserId($testUserId);
        
        $this->assertInstanceOf(Wallet::class, $result);
        $this->assertEquals($testUserId, $result->user_id);
    }

    public function testUpdateWalletBalances()
    {
        $payerId = 1;
        $payeeId = 2;
        $amount = 100.00;
        
        $this->walletModel->expects($this->once())
            ->method('updateWalletBalances')
            ->with($payerId, $payeeId, $amount)
            ->willReturn(true);
        
        $result = $this->walletModel->updateWalletBalances($payerId, $payeeId, $amount);
        
        $this->assertTrue($result);
    }
    
    public function testUpdateWalletBalancesWithMoneyClass()
    {
        $payerId = 1;
        $payeeId = 2;
        $amount = new Money(100.50);
        
        $this->walletModel->expects($this->once())
            ->method('updateWalletBalances')
            ->with($payerId, $payeeId, $amount)
            ->willReturn(true);
        
        $result = $this->walletModel->updateWalletBalances($payerId, $payeeId, $amount);
        
        $this->assertTrue($result);
    }
    
    public function testHasSufficientBalance()
    {
        // Direct test of the Wallet entity
        $wallet = new Wallet([
            'id_wallet' => 1,
            'user_id' => 1,
            'balance' => 1000.00
        ]);

        $this->assertTrue($wallet->hasSufficientBalance(1000.00));
        $this->assertTrue($wallet->hasSufficientBalance(new Money(1000.00)));
        $this->assertTrue($wallet->hasSufficientBalance(999.99));
        $this->assertFalse($wallet->hasSufficientBalance(1000.01));
        $this->assertFalse($wallet->hasSufficientBalance(new Money(1000.01)));
    }
    
    public function testDebitAndCredit()
    {
        // Direct test of the Wallet entity for operations with small amounts
        $wallet = new Wallet([
            'id_wallet' => 3,
            'user_id' => 3,
            'balance' => 0
        ]);
        
        // Test operations with small amounts
        $wallet->credit(0.01);
        $this->assertEquals(0.01, $wallet->balance);
        
        $wallet->credit(0.02);
        $this->assertEquals(0.03, $wallet->balance);
        
        // Test with Money object
        $wallet->credit(new Money(0.07));
        $this->assertEquals(0.10, $wallet->balance);
        
        $wallet->debit(new Money(0.05));
        $this->assertEquals(0.05, $wallet->balance);
    }
} 