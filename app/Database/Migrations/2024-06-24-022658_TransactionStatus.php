<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TransactionStatus extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_transaction_status' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'status_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
        ]);
        $this->forge->addKey('id_transaction_status', true);
        $this->forge->createTable('transaction_status');
    }

    public function down()
    {
        $this->forge->dropTable('transaction_status');
    }
}
