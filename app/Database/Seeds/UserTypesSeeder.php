<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserTypesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['type_name' => 'common'],
            ['type_name' => 'merchant']
        ];

        if ($this->db->table('user_types')->countAll() > 0) return;
        $this->db->table('user_types')->insertBatch($data);
    }
}
