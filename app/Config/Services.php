<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use App\Services\AuthorizationService;
use App\Services\NotificationService;
use App\Services\TransferService;
use App\Interfaces\Services\AuthorizationServiceInterface;
use App\Interfaces\Services\NotificationServiceInterface;
use App\Interfaces\Services\TransferServiceInterface;
use App\Interfaces\Models\UserModelInterface;
use App\Interfaces\Models\WalletModelInterface;
use App\Interfaces\Models\TransactionModelInterface;
use App\Interfaces\Models\TransactionStatusModelInterface;
use App\Models\UserModel;
use App\Models\WalletModel;
use App\Models\TransactionModel;
use App\Models\TransactionStatusModel;

class Services extends BaseService
{
    public static function authorizationService($getShared = true): AuthorizationServiceInterface
    {
        if ($getShared) return static::getSharedInstance('authorizationService');

        return new AuthorizationService(\Config\Services::curlrequest());
    }

    public static function notificationService($getShared = true): NotificationServiceInterface
    {
        if ($getShared) return static::getSharedInstance('notificationService');

        return new NotificationService(\Config\Services::curlrequest());
    }

    public static function transferService($getShared = true): TransferServiceInterface
    {
        if ($getShared) return static::getSharedInstance('transferService');

        return new TransferService(
            static::userModel(),
            static::walletModel(),
            static::transactionModel(),
            static::transactionStatusModel(),
            static::notificationService(),
            static::authorizationService()
        );
    }

    public static function userModel($getShared = true): UserModelInterface
    {
        if ($getShared) return static::getSharedInstance('userModel');

        return new UserModel();
    }

    public static function walletModel($getShared = true): WalletModelInterface
    {
        if ($getShared) return static::getSharedInstance('walletModel');

        return new WalletModel();
    }

    public static function transactionModel($getShared = true): TransactionModelInterface
    {
        if ($getShared) return static::getSharedInstance('transactionModel');

        return new TransactionModel();
    }

    public static function transactionStatusModel($getShared = true): TransactionStatusModelInterface
    {
        if ($getShared) return static::getSharedInstance('transactionStatusModel');

        return new TransactionStatusModel();
    }
}
