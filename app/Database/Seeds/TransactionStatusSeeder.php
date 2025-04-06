<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TransactionStatusSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['status_name' => 'pending'],
            ['status_name' => 'completed'],
            ['status_name' => 'failed']
        ];

        if ($this->db->table('transaction_status')->countAll() > 0) return;
        $this->db->table('transaction_status')->insertBatch($data);
    }
}
