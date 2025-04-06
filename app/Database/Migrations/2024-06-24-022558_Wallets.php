<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Wallets extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_wallet' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
            ],
            'balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_wallet', true);
        $this->forge->addForeignKey('user_id', 'users', 'id_user', 'CASCADE', 'CASCADE');
        $this->forge->createTable('wallets');
    }

    public function down()
    {
        $this->forge->dropTable('wallets');
    }
}
