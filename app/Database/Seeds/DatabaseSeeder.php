<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('UserTypesSeeder');
        $this->call('UserSeeder');
        $this->call('WalletsSeeder');
        $this->call('TransactionStatusSeeder');
    }
}
