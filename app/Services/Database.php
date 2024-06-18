<?php

namespace CryptoTrade\Services;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class Database
{
    private Connection $conn;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $databasePath = __DIR__ . '/../../storage/database.sqlite';
        $connectionParams = [
            'url' => 'sqlite:///' . $databasePath,
        ];
        $this->conn = DriverManager::getConnection($connectionParams);
    }

    public function setupDatabase()
    {
        try {
            $schemaManager = $this->conn->createSchemaManager();
            $tables = $schemaManager->listTableNames();
            if (in_array('wallet', $tables) === false) {
                $this->conn->executeQuery('
                    CREATE TABLE wallet (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        symbol VARCHAR(10) UNIQUE,
                        amount FLOAT,
                        purchasePrice FLOAT
                    )
                ');
            } else {
                $this->conn->executeQuery('
                    CREATE UNIQUE INDEX IF NOT EXISTS wallet_symbol_uindex ON wallet (symbol)
                ');
            }

            if (in_array('transactions', $tables) === false) {
                $this->conn->executeQuery('
                    CREATE TABLE transactions (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        type VARCHAR(10),
                        symbol VARCHAR(10),
                        amount FLOAT,
                        price FLOAT,
                        timestamp DATETIME
                    )
                ');
            }

            if (in_array('user_balance', $tables) === false) {
                $this->conn->executeQuery('
                    CREATE TABLE user_balance (
                        id INTEGER PRIMARY KEY,
                        balance FLOAT
                    )
                ');

                $balance = $this->conn->fetchOne('SELECT balance FROM user_balance WHERE id = 1');
                if ($balance === false) {
                    $this->conn->insert('user_balance', [
                        'id' => 1,
                        'balance' => 1000.0
                    ]);
                }
            }

            echo "Database setup completed.\n";
        } catch (Exception $e) {
            echo "An error occurred while setting up the database: " . $e->getMessage();
        }
    }

    public function getConnection(): Connection
    {
        return $this->conn;
    }
}
