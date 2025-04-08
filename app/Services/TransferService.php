<?php

namespace App\Services;

use App\Interfaces\Services\TransferServiceInterface;
use App\Interfaces\Services\NotificationServiceInterface;
use App\Interfaces\Services\AuthorizationServiceInterface;
use App\Interfaces\Models\UserModelInterface;
use App\Interfaces\Models\WalletModelInterface;
use App\Interfaces\Models\TransactionModelInterface;
use App\Interfaces\Models\TransactionStatusModelInterface;
use App\Entities\Money;

class TransferService implements TransferServiceInterface
{
    const TYPE_MERCHANT = 'merchant';
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const CURRENCY = 'BRL';

    protected $userModel;
    protected $walletModel;
    protected $transactionModel;
    protected $transactionStatusModel;
    protected $notificationService;
    protected $authorizationService;

    protected $db;

    public function __construct(
        UserModelInterface $userModel,
        WalletModelInterface $walletModel,
        TransactionModelInterface $transactionModel,
        TransactionStatusModelInterface $transactionStatusModel,
        NotificationServiceInterface $notificationService,
        AuthorizationServiceInterface $authorizationService
    ) {
        $this->userModel = $userModel;
        $this->walletModel = $walletModel;
        $this->transactionModel = $transactionModel;
        $this->transactionStatusModel = $transactionStatusModel;
        $this->notificationService = $notificationService;
        $this->authorizationService = $authorizationService;
        $this->db = \Config\Database::connect();
    }

    public function transfer(int $payerId, int $payeeId, float $amount): array
    {
        $this->db->transBegin();

        try {
            $moneyAmount = new Money($amount);
            
            $validationResult = $this->validateTransfer($payerId, $payeeId, $moneyAmount);
            if (isset($validationResult['error'])) {
                $this->db->transRollback();
                return $validationResult;
            }
            
            $payer = $validationResult['payer'];
            $payee = $validationResult['payee'];
            $payerWallet = $validationResult['payerWallet'];
            
            $transactionResult = $this->processTransaction($payerId, $payeeId, $moneyAmount);
            if (isset($transactionResult['error'])) {
                $this->db->transRollback();
                return $transactionResult;
            }
            
            $transactionId = $transactionResult['transactionId'];
            
            $this->db->transCommit();
            $this->sendSuccessNotification($payeeId, $moneyAmount, $payer->name);
            
            return [
                'message' => 'Transaction successful. Transaction ID: ' . $transactionId, 
                'code' => 200
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error in transfer: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'code' => 500];
        }
    }
    
    private function validateTransfer(int $payerId, int $payeeId, Money $amount): array
    {
        $payer = $this->userModel->getUserById($payerId);
        $payee = $this->userModel->getUserById($payeeId);

        if (!$payer || !$payee) {
            return ['error' => 'Payer or Payee not found.', 'code' => 404];
        }

        if ($payerId == $payeeId) {
            return ['error' => 'You cannot send money to yourself.', 'code' => 403];
        }

        $payerWallet = $this->walletModel->getWalletByUserId($payerId);
        if (!$payerWallet) {
            return ['error' => 'Payer Wallet not found.', 'code' => 404];
        }
        
        if ($payer->isMerchant()) {
            return ['error' => 'Merchants cannot send money.', 'code' => 403];
        }

        if (!$payerWallet->hasSufficientBalance($amount)) {
            return [
                'error' => 'Insufficient balance. Your balance is: ' . $payerWallet->balance . ' ' . self::CURRENCY, 
                'code' => 403
            ];
        }
        
        return [
            'payer' => $payer,
            'payee' => $payee,
            'payerWallet' => $payerWallet
        ];
    }
    
    private function processTransaction(int $payerId, int $payeeId, Money $amount): array
    {
        $statusId = $this->transactionStatusModel->getStatusId(self::STATUS_PENDING);
        $transactionId = $this->transactionModel->saveTransaction($payerId, $payeeId, $amount, $statusId);

        if (!$transactionId) {
            return ['error' => 'Transaction failed.', 'code' => 500];
        }

        $walletUpdate = $this->walletModel->updateWalletBalances($payerId, $payeeId, $amount);
        if (!$walletUpdate) {
            $statusId = $this->transactionStatusModel->getStatusId(self::STATUS_FAILED);
            $this->transactionModel->updateTransactionStatus($transactionId, $statusId);
            return ['error' => 'Transaction failed when updating wallet balances.', 'code' => 500];
        }

        $statusId = $this->transactionStatusModel->getStatusId(self::STATUS_COMPLETED);
        $this->transactionModel->updateTransactionStatus($transactionId, $statusId);
        
        return ['transactionId' => $transactionId];
    }
    
    private function sendSuccessNotification(int $payeeId, Money $amount, string $payerName): void
    {
        $this->notificationService->sendNotification(
            $payeeId,
            "You received " . $amount->format() . " " . self::CURRENCY . " from " . $payerName . "."
        );
    }
}
