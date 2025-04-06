<?php

namespace Tests\Services;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\TransactionModel;
use App\Models\TransactionStatusModel;
use PHPUnit\Framework\MockObject\MockObject;
use App\Entities\Transaction;
use App\Entities\Money;

class TransactionModelTest extends CIUnitTestCase
{
    protected TransactionModel|MockObject $transactionModel;
    protected TransactionStatusModel|MockObject $transactionStatusModel;

    public function setUp(): void
    {
        parent::setUp();
        $this->transactionModel = $this->createMock(TransactionModel::class);
        $this->transactionStatusModel = $this->createMock(TransactionStatusModel::class);
    }

    public function testSaveTransaction()
    {
        // Test data
        $payerId = 1;
        $payeeId = 2;
        $amount = 100.50;
        $statusId = 1;
        $expectedTransactionId = 123;

        // Setting up the mock
        $this->transactionModel->expects($this->once())
            ->method('saveTransaction')
            ->with($payerId, $payeeId, $amount, $statusId)
            ->willReturn($expectedTransactionId);
        
        // Executing the method we're testing
        $transactionId = $this->transactionModel->saveTransaction($payerId, $payeeId, $amount, $statusId);
        
        // Verifying the result
        $this->assertIsInt($transactionId);
        $this->assertEquals($expectedTransactionId, $transactionId);
    }

    public function testUpdateTransactionStatus()
    {
        // Test data
        $transactionId = 123;
        $newStatusId = 2;
        
        // Setting up the mock
        $this->transactionModel->expects($this->once())
            ->method('updateTransactionStatus')
            ->with($transactionId, $newStatusId)
            ->willReturn(true);
        
        // Executing the method we're testing
        $result = $this->transactionModel->updateTransactionStatus($transactionId, $newStatusId);
        
        // Verifying the result
        $this->assertTrue($result);
    }
    
    public function testTransactionPrecisionWithLargeAmounts()
    {
        // Test data
        $payerId = 1;
        $payeeId = 2;
        $amount = 9999999.99; // Almost 10 million
        $statusId = 1;
        $expectedTransactionId = 456;
        
        // Setting up the mock for saveTransaction
        $this->transactionModel->expects($this->once())
            ->method('saveTransaction')
            ->with($payerId, $payeeId, $amount, $statusId)
            ->willReturn($expectedTransactionId);
        
        // Setting up the mock for find
        $transaction = [
            'transaction_id' => $expectedTransactionId,
            'payer_id' => $payerId,
            'payee_id' => $payeeId,
            'amount' => (string)$amount,
            'status_id' => $statusId
        ];
        
        $this->transactionModel->expects($this->once())
            ->method('find')
            ->with($expectedTransactionId)
            ->willReturn($transaction);
        
        // Executing the methods we're testing
        $transactionId = $this->transactionModel->saveTransaction($payerId, $payeeId, $amount, $statusId);
        $savedTransaction = $this->transactionModel->find($transactionId);
        
        // Verifying the results
        $this->assertEquals($expectedTransactionId, $transactionId);
        $this->assertEquals($amount, (float)$savedTransaction['amount']);
    }
    
    public function testTransactionPrecisionWithSmallAmounts()
    {
        // Test data
        $payerId = 1;
        $payeeId = 2;
        $amount = 0.01; // 1 cent
        $statusId = 1;
        $expectedTransactionId = 789;
        
        // Setting up the mock for saveTransaction
        $this->transactionModel->expects($this->once())
            ->method('saveTransaction')
            ->with($payerId, $payeeId, $amount, $statusId)
            ->willReturn($expectedTransactionId);
        
        // Setting up the mock for find
        $transaction = [
            'transaction_id' => $expectedTransactionId,
            'payer_id' => $payerId,
            'payee_id' => $payeeId,
            'amount' => (string)$amount,
            'status_id' => $statusId
        ];
        
        $this->transactionModel->expects($this->once())
            ->method('find')
            ->with($expectedTransactionId)
            ->willReturn($transaction);
        
        // Executing the methods we're testing
        $transactionId = $this->transactionModel->saveTransaction($payerId, $payeeId, $amount, $statusId);
        $savedTransaction = $this->transactionModel->find($transactionId);
        
        // Verifying the results
        $this->assertEquals($expectedTransactionId, $transactionId);
        $this->assertEquals($amount, (float)$savedTransaction['amount']);
    }
    
    public function testSaveTransactionWithMoneyClass()
    {
        // Test data
        $payerId = 1;
        $payeeId = 2;
        $amount = new Money(100.50);
        $statusId = 1;
        $expectedTransactionId = 123;

        // Setting up the mock
        $this->transactionModel->expects($this->once())
            ->method('saveTransaction')
            ->with($payerId, $payeeId, $amount, $statusId)
            ->willReturn($expectedTransactionId);
        
        // Executing the method we're testing
        $transactionId = $this->transactionModel->saveTransaction($payerId, $payeeId, $amount, $statusId);
        
        // Verifying the result
        $this->assertIsInt($transactionId);
        $this->assertEquals($expectedTransactionId, $transactionId);
    }
} 