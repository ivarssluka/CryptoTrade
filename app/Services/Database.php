<?php

namespace CryptoTrade\Services;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class Database
{
    private Connection $connection;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $databasePath = __DIR__ . '/../../storage/database.sqlite';
        $params = [
            'url' => 'sqlite:///' . $databasePath,
        ];
        $this->connection = DriverManager::getConnection($params);
    }

    public function setupDatabase()
    {
        try {
            $schemaManager = $this->connection->createSchemaManager();
            $tables = $schemaManager->listTableNames();
            if (in_array('users', $tables) === false) {
                $this->connection->executeQuery('
                    CREATE TABLE users (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        username VARCHAR(50) NOT NULL UNIQUE,
                        password VARCHAR(255) NOT NULL,
                        balance FLOAT NOT NULL DEFAULT 1000.0
                    )
                ');
            }

            if (in_array('wallets', $tables) === false) {
                $this->connection->executeQuery('
                    CREATE TABLE wallets (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        user_id INTEGER NOT NULL,
                        symbol VARCHAR(10) NOT NULL,
                        amount FLOAT NOT NULL,
                        purchasePrice FLOAT NOT NULL,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                    )
                ');
            }

            if (in_array('transactions', $tables) === false) {
                $this->connection->executeQuery('
                    CREATE TABLE transactions (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        user_id INTEGER NOT NULL,
                        type VARCHAR(10) NOT NULL,
                        symbol VARCHAR(10) NOT NULL,
                        amount FLOAT NOT NULL,
                        price FLOAT NOT NULL,
                        timestamp DATETIME NOT NULL,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
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
        return $this->connection;
    }
}
