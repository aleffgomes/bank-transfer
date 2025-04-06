<?php

namespace App\Services;

use App\Interfaces\Services\TransferServiceInterface;
use App\Interfaces\Services\NotificationServiceInterface;
use App\Interfaces\Services\AuthorizationServiceInterface;
use App\Interfaces\Models\UserModelInterface;
use App\Interfaces\Models\WalletModelInterface;
use App\Interfaces\Models\TransactionModelInterface;
use App\Interfaces\Models\TransactionStatusModelInterface;

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

    /**
     * Transfer money from one user to another.
     *
     * @param int $payerId
     * @param int $payeeId
     * @param float $amount
     * @return array
     */
    public function transfer(int $payerId, int $payeeId, float $amount): array
    {
        $this->db->transBegin();

        try {
            // Convert amount to Money object for precise calculations
            $moneyAmount = new \App\Entities\Money($amount);
            
            $payer = $this->userModel->getUserById($payerId);
            $payee = $this->userModel->getUserById($payeeId);

            if (!$payer || !$payee) {
                $this->db->transRollback();
                return ['error' => 'Payer or Payee not found.', 'code' => 404];
            }

            if ($payerId == $payeeId) {
                $this->db->transRollback();
                return ['error' => 'You cannot send money to yourself.', 'code' => 403];
            }

            $payerWallet = $this->walletModel->getWalletByUserId($payerId);

            if (!$payerWallet) {
                $this->db->transRollback();
                return ['error' => 'Payer Wallet not found.', 'code' => 404];
            }
            
            if ($payer->isMerchant()) {
                $this->db->transRollback();
                return ['error' => 'Merchants cannot send money.', 'code' => 403];
            }

            if (!$payerWallet->hasSufficientBalance($moneyAmount)) {
                $this->db->transRollback();
                return [
                    'error' => 'Insufficient balance. Your balance is: ' . $payerWallet->balance . ' ' . self::CURRENCY, 
                    'code' => 403
                ];
            }

            $statusId = $this->transactionStatusModel->getStatusId(self::STATUS_PENDING);
            $transactionId = $this->transactionModel->saveTransaction($payerId, $payeeId, $moneyAmount, $statusId);

            if (!$transactionId) {
                $this->db->transRollback();
                return ['error' => 'Transaction failed.', 'code' => 500];
            }

            $walletUpdate = $this->walletModel->updateWalletBalances($payerId, $payeeId, $moneyAmount);

            if (!$walletUpdate) {
                $statusId = $this->transactionStatusModel->getStatusId(self::STATUS_FAILED);
                $this->transactionModel->updateTransactionStatus($transactionId, $statusId);
                $this->db->transRollback();
                return ['error' => 'Transaction failed when updating wallet balances.', 'code' => 500];
            }

            $statusId = $this->transactionStatusModel->getStatusId(self::STATUS_COMPLETED);
            $this->transactionModel->updateTransactionStatus($transactionId, $statusId);

            $this->db->transCommit();

            // Send notification to payee
            $this->notificationService->sendNotification(
                $payeeId,
                "You received " . $moneyAmount->format() . " " . self::CURRENCY . " from " . $payer->name . "."
            );

            return ['message' => 'Transaction successful. Transaction ID: ' . $transactionId, 'code' => 200];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error in transfer: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'code' => 500];
        }
    }
}
