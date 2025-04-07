<?php

namespace Tests\Services;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\TransferService;
use App\Interfaces\Services\AuthorizationServiceInterface;
use App\Interfaces\Services\NotificationServiceInterface;
use App\Interfaces\Models\UserModelInterface;
use App\Interfaces\Models\WalletModelInterface;
use App\Interfaces\Models\TransactionModelInterface;
use App\Interfaces\Models\TransactionStatusModelInterface;
use App\Entities\User;
use App\Entities\Wallet;

class TransferServiceTest extends CIUnitTestCase
{
    protected $transferService;
    protected $userModel;
    protected $walletModel;
    protected $transactionModel;
    protected $transactionStatusModel;
    protected $authorizationService;
    protected $notificationService;

    public function setUp(): void
    {
        parent::setUp();
        $this->userModel = $this->createMock(UserModelInterface::class);
        $this->walletModel = $this->createMock(WalletModelInterface::class);
        $this->transactionModel = $this->createMock(TransactionModelInterface::class);
        $this->transactionStatusModel = $this->createMock(TransactionStatusModelInterface::class);
        $this->authorizationService = $this->createMock(AuthorizationServiceInterface::class);
        $this->notificationService = $this->createMock(NotificationServiceInterface::class);

        $this->transferService = new TransferService(
            $this->userModel,
            $this->walletModel,
            $this->transactionModel,
            $this->transactionStatusModel,
            $this->notificationService,
            $this->authorizationService
        );
    }

    public function testTransferSuccess()
    {
        // Mock for payer and payee
        $payer = $this->createMock(User::class);
        $payer->method('isMerchant')->willReturn(false);
        $payer->method('__get')->willReturnMap([
            ['id_user', 1],
            ['name', 'John Doe']
        ]);
        
        $payee = $this->createMock(User::class);
        $payee->method('__get')->willReturnMap([
            ['id_user', 2]
        ]);

        // Mock for wallet
        $wallet = $this->createMock(Wallet::class);
        $wallet->method('hasSufficientBalance')->willReturn(true);
        $wallet->method('__get')->willReturnMap([
            ['balance', 1000]
        ]);

        // Setting up mock returns
        $this->userModel->method('getUserById')
            ->willReturnMap([
                [1, $payer],
                [2, $payee]
            ]);

        $this->walletModel->method('getWalletByUserId')
            ->willReturn($wallet);

        $this->authorizationService->method('checkAuthorization')
            ->willReturn(true);

        $this->transactionStatusModel->method('getStatusId')
            ->willReturn(1);

        $this->transactionModel->method('saveTransaction')
            ->willReturn(1);

        $this->walletModel->method('updateWalletBalances')
            ->willReturn(true);

        $this->notificationService->method('sendNotification')
            ->willReturn(true);

        $result = $this->transferService->transfer(1, 2, 100);

        $this->assertEquals(['message' => 'Transaction successful. Transaction ID: 1', 'code' => 200], $result);
    }
    
    public function testTransferInsufficientBalance()
    {
        // Mocking User entities
        $payer = new User([
            'id_user' => 1,
            'type_name' => 'user'
        ]);
        
        $payee = new User([
            'id_user' => 2,
            'type_name' => 'user'
        ]);

        // Mocking Wallet entity with insufficient balance
        $wallet = new Wallet([
            'id_wallet' => 1,
            'user_id' => 1,
            'balance' => 50
        ]);

        $this->userModel->method('getUserById')
            ->willReturnOnConsecutiveCalls($payer, $payee);
    
        $this->walletModel->method('getWalletByUserId')
            ->willReturn($wallet);
    
        $this->authorizationService->method('checkAuthorization')
            ->willReturn(true);
    
        $result = $this->transferService->transfer(1, 2, 100);
    
        $this->assertEquals('Insufficient balance. Your balance is: 50 BRL', $result['error']);
        $this->assertEquals(403, $result['code']);
    }

    public function testTransferMerchant()
    {
        // Mocking Merchant User entity
        $merchant = new User([
            'id_user' => 1,
            'type_name' => 'merchant'
        ]);

        // Mocking Wallet entity
        $wallet = new Wallet([
            'id_wallet' => 1,
            'user_id' => 1,
            'balance' => 1000
        ]);
        
        $this->userModel->method('getUserById')
            ->willReturn($merchant);
        
        $this->walletModel->method('getWalletByUserId')
            ->willReturn($wallet);
        
        $this->authorizationService->method('checkAuthorization')
            ->willReturn(true);
        
        $result = $this->transferService->transfer(1, 2, 100);
        
        $this->assertEquals('Merchants cannot send money.', $result['error']);
        $this->assertEquals(403, $result['code']);
    }

    public function testTransferPayerNotFound()
    {
        // Mocking User entity
        $user = new User([
            'id_user' => 1,
            'type_name' => 'user'
        ]);
        
        $this->userModel->method('getUserById')
            ->willReturn($user);
        
        $this->walletModel->method('getWalletByUserId')
            ->willReturn(null);
        
        $this->authorizationService->method('checkAuthorization')
            ->willReturn(true);
        
        $result = $this->transferService->transfer(1, 2, 100);
        
        $this->assertEquals('Payer Wallet not found.', $result['error']);
        $this->assertEquals(404, $result['code']);
    }

    public function testTransferToSelf()
    {
        // Mocking User entity
        $user = new User([
            'id_user' => 1,
            'type_name' => 'user'
        ]);
        
        $this->userModel->method('getUserById')
            ->willReturn($user);
        
        $result = $this->transferService->transfer(1, 1, 100);
        
        $this->assertEquals('You cannot send money to yourself.', $result['error']);
        $this->assertEquals(403, $result['code']);
    }

    public function testTransferSaveTransactionFailure()
    {
        // Mock for payer and payee
        $payer = $this->createMock(User::class);
        $payer->method('isMerchant')->willReturn(false);
        $payer->method('__get')->willReturnMap([
            ['id_user', 1],
            ['name', 'John Doe']
        ]);
        
        $payee = $this->createMock(User::class);
        $payee->method('__get')->willReturnMap([
            ['id_user', 2]
        ]);

        // Mock for wallet
        $wallet = $this->createMock(Wallet::class);
        $wallet->method('hasSufficientBalance')->willReturn(true);
        $wallet->method('__get')->willReturnMap([
            ['balance', 1000]
        ]);

        // Setting up mock returns
        $this->userModel->method('getUserById')
            ->willReturnMap([
                [1, $payer],
                [2, $payee]
            ]);

        $this->walletModel->method('getWalletByUserId')
            ->willReturn($wallet);

        $this->transactionStatusModel->method('getStatusId')
            ->willReturn(1);

        // Simular falha ao salvar a transação
        $this->transactionModel->method('saveTransaction')
            ->willReturn(0);

        $result = $this->transferService->transfer(1, 2, 100);

        $this->assertEquals('Transaction failed.', $result['error']);
        $this->assertEquals(500, $result['code']);
    }

    public function testTransferUpdateWalletBalancesFailure()
    {
        // Mock for payer and payee
        $payer = $this->createMock(User::class);
        $payer->method('isMerchant')->willReturn(false);
        $payer->method('__get')->willReturnMap([
            ['id_user', 1],
            ['name', 'John Doe']
        ]);
        
        $payee = $this->createMock(User::class);
        $payee->method('__get')->willReturnMap([
            ['id_user', 2]
        ]);

        // Mock for wallet
        $wallet = $this->createMock(Wallet::class);
        $wallet->method('hasSufficientBalance')->willReturn(true);
        $wallet->method('__get')->willReturnMap([
            ['balance', 1000]
        ]);

        // Setting up mock returns
        $this->userModel->method('getUserById')
            ->willReturnMap([
                [1, $payer],
                [2, $payee]
            ]);

        $this->walletModel->method('getWalletByUserId')
            ->willReturn($wallet);

        $this->transactionStatusModel->method('getStatusId')
            ->willReturn(1);

        $this->transactionModel->method('saveTransaction')
            ->willReturn(1);

        // Simular falha ao atualizar os saldos
        $this->walletModel->method('updateWalletBalances')
            ->willReturn(false);

        $result = $this->transferService->transfer(1, 2, 100);

        $this->assertEquals('Transaction failed when updating wallet balances.', $result['error']);
        $this->assertEquals(500, $result['code']);
    }

    public function testTransferException()
    {
        // Mock for payer and payee
        $payer = $this->createMock(User::class);
        $payer->method('isMerchant')->willReturn(false);
        
        // Simular uma exceção ao tentar acessar uma propriedade inexistente
        $payer->method('__get')->willThrowException(new \Exception('Payer Wallet not found.'));

        $this->userModel->method('getUserById')
            ->willReturn($payer);

        $result = $this->transferService->transfer(1, 2, 100);

        $this->assertEquals('Payer Wallet not found.', $result['error']);
        $this->assertEquals(404, $result['code']);
    }
}
