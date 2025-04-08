<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Interfaces\Models\WalletModelInterface;
use App\Entities\Wallet;
use App\Entities\Money;

class WalletModel extends Model implements WalletModelInterface
{
    protected $table = 'wallets';
    protected $primaryKey = 'id_wallet';
    protected $allowedFields = ['user_id', 'balance'];
    protected $returnType = 'App\Entities\Wallet';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    // Configurações de tempo e formato
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';

    public function getWalletByUserId(int $userId): ?Wallet
    {
        return $this->where('user_id', $userId)->first();
    }

    public function updateWalletBalances(int $payerId, int $payeeId, $amount): bool
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $builder = $db->table($this->table);
            
            $payerWalletData = $builder->select('*')->where('user_id', $payerId)
                ->get(1, 0, 'FOR UPDATE')
                ->getRowArray();

            $payeeWalletData = $builder->select('*')->where('user_id', $payeeId)
                ->get(1, 0, 'FOR UPDATE')
                ->getRowArray();

            if (!$payerWalletData || !$payeeWalletData) {
                $db->transRollback();
                return false;
            }

            $payerWallet = new Wallet($payerWalletData);
            $payeeWallet = new Wallet($payeeWalletData);
            
            $moneyAmount = $amount instanceof Money ? $amount : new Money($amount);

            if (!$payerWallet->hasSufficientBalance($moneyAmount)) {
                $db->transRollback();
                return false;
            }

            $payerWallet->debit($moneyAmount);
            $payeeWallet->credit($moneyAmount);

            $builder->where('user_id', $payerId)->update(['balance' => $payerWallet->balance]);
            $builder->where('user_id', $payeeId)->update(['balance' => $payeeWallet->balance]);

            $db->transCommit();
            return true;
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error updating balance: ' . $e->getMessage());
            return false;
        }
    }
}
