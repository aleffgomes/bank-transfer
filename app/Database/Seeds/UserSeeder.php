<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create();

        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $users[] = [
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'cpf_cnpj' => '1234567' . $faker->randomNumber(4, true),
                'user_type_id' => 1,
            ];
        }

        for ($i = 1; $i <= 5; $i++) {
            $users[] = [
                'name' => $faker->company,
                'email' => $faker->unique()->companyEmail,
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'cpf_cnpj' => '1234567' . $faker->randomNumber(7, true),
                'user_type_id' => 2, 
            ];
        }

        if ($this->db->table('users')->countAll() > 0) return;
        $this->db->table('users')->insertBatch($users);
    }
}
