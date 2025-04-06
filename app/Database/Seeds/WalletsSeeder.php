<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class WalletsSeeder extends Seeder
{
    public function run()
    {
        $wallets = []; 
        for ($i = 1; $i <= 10; $i++) {
            $wallets[] = [
                'user_id' => $i,
                'balance' => 1000.00,
            ];
        }

        if ($this->db->table('wallets')->countAll() > 0) return;
        $this->db->table('wallets')->insertBatch($wallets);
    }
}
