<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UserTypes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_user_type' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'type_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
        ]);
        $this->forge->addKey('id_user_type', true);
        $this->forge->createTable('user_types');
    }

    public function down()
    {
        $this->forge->dropTable('user_types');
    }
}
