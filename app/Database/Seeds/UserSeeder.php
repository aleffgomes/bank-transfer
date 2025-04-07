<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder as BaseSeeder;
use Faker\Factory;

class UserSeeder extends BaseSeeder
{
    /**
     * @var \CodeIgniter\Database\BaseConnection
     */
    protected $db;

    public function run()
    {
        $this->db = \Config\Database::connect();
        $faker = Factory::create();

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

        if ($this->db->table('users')->countAllResults() > 0) {
            log_message('info', 'Tabela "users" já populada. Pulando UserSeeder.');
            return;
        }
        
        $this->db->table('users')->insertBatch($users);
        log_message('info', 'UserSeeder executado com sucesso.');
    }
}
