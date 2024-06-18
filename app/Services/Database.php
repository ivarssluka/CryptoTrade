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

            if (!in_array('users', $tables)) {
                $this->conn->executeQuery('
                    CREATE TABLE users (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        username VARCHAR(50) UNIQUE,
                        password VARCHAR(255),
                        balance FLOAT DEFAULT 1000.0
                    )
                ');
            }

            if (!in_array('wallets', $tables)) {
                $this->conn->executeQuery('
                    CREATE TABLE wallets (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        user_id INTEGER,
                        symbol VARCHAR(10),
                        amount FLOAT,
                        purchasePrice FLOAT,
                        FOREIGN KEY(user_id) REFERENCES users(id)
                    )
                ');
            } else {
                $this->conn->executeQuery('
                    CREATE UNIQUE INDEX IF NOT EXISTS wallets_user_id_symbol_uindex ON wallets (user_id, symbol)
                ');
            }

            if (!in_array('transactions', $tables)) {
                $this->conn->executeQuery('
                    CREATE TABLE transactions (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        user_id INTEGER,
                        type VARCHAR(10),
                        symbol VARCHAR(10),
                        amount FLOAT,
                        price FLOAT,
                        timestamp DATETIME,
                        FOREIGN KEY(user_id) REFERENCES users(id)
                    )
                ');
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
