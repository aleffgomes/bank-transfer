<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Exception;

class ResetDatabase extends BaseCommand
{
    protected $group       = 'custom';
    protected $name        = 'db:reset';
    protected $description = 'Reset the database by dropping all tables and recreating them.';

    public function run(array $params)
    {
        $this->dropTables();
        $this->recreateTables();
        CLI::write('Database reset completed.', 'green');
    }

    /**
     * Drop all tables in the connected database.
     *
     * @return void
     */
    protected function dropTables(): void
    {
        $database = \Config\Database::connect();
        $query = $database->table('information_schema.tables')
            ->where('table_schema', $database->getDatabase())
            ->get();

        foreach ($query->getResultArray() as $row) {
            $tableName = $row['TABLE_NAME'];
            $database->simpleQuery("SET FOREIGN_KEY_CHECKS = 0");
            $database->simpleQuery("DROP TABLE IF EXISTS `$tableName`");
        }
    }

    /**
     * Recreate all tables in the connected database.
     *
     * @return void
     */
    protected function recreateTables(): void
    {
        $migrate = \Config\Services::migrations();
        $migrate->setSilent(false);
        $migrate->latest();
    }
}
