<?php

require 'vendor/autoload.php';

use CryptoTrade\Services\CoinMarketCapApi;
use CryptoTrade\Services\CryptoCompareApi;
use CryptoTrade\Services\Database;
use CryptoTrade\Services\WalletService;
use CryptoTrade\Utils\TableRenderer;
use CryptoTrade\Models\User;
use Dotenv\Dotenv;
use Doctrine\DBAL\Connection;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiClient = new CoinMarketCapApi();
//$apiClient = new CryptoCompareApi();

$database = new Database();
$database->setupDatabase();

$conn = $database->getConnection();

function registerUser(Connection $conn): void
{
    $username = readline("Enter your username: ");
    $password = readline("Enter your password: ");
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    try {
        $conn->insert('users', [
            'username' => $username,
            'password' => $hashedPassword,
            'balance' => 1000.0, // Initial balance for new users
        ]);
        echo "User registered successfully.\n";
    } catch (Exception $e) {
        echo "An error occurred during registration: " . $e->getMessage() . "\n";
    }
}

function loginUser(Connection $conn): User
{
    while (true) {
        $username = readline("Enter your username: ");
        $password = readline("Enter your password: ");

        $userData = $conn->fetchAssociative('SELECT * FROM users WHERE username = ?', [$username]);
        if ($userData && password_verify($password, $userData['password'])) {
            return new User($userData['id'], $userData['username'], $userData['password'], $userData['balance']);
        } else {
            echo "Invalid username or password. Please try again.\n";
        }
    }
}

echo "\nWelcome to CryptoTrade!\n";
echo "1. Register\n";
echo "2. Login\n";
$choice = (int)readline("Enter your choice: ");
if ($choice === 1) {
    registerUser($conn);
}

$user = loginUser($conn);
$walletService = new WalletService($apiClient, $database, $user);

while (true) {
    echo "\nWhat would you like to do?\n";
    echo "1. Add balance\n";
    echo "2. Withdraw balance\n";
    echo "3. List top cryptocurrencies\n";
    echo "4. Search cryptocurrency by symbol\n";
    echo "5. Purchase cryptocurrency\n";
    echo "6. Sell cryptocurrency\n";
    echo "7. Display wallet state\n";
    echo "8. Display transaction history\n";
    echo "9. Exit\n";
    $choice = (int)readline("Enter your choice: ");
    switch ($choice) {
        case 1: // Add balance
            $amount = (float)readline("Enter the amount to add: ");
            $walletService->getUser()->addBalance($amount);
            $walletService->saveWallet();
            echo "Balance added successfully.\n";
            break;
        case 2: // Withdraw balance
            $amount = (float)readline("Enter the amount to withdraw: ");
            if ($walletService->getUser()->getBalance() < $amount) {
                echo "Insufficient balance.\n";
                break;
            }
            $walletService->getUser()->subtractBalance($amount);
            $walletService->saveWallet();
            echo "Balance withdrawn successfully.\n";
            break;
        case 3: // List top cryptocurrencies
            $cryptos = $apiClient->getTopCryptos();
            $rows = array_map(fn($crypto) => [
                $crypto->getId(),
                $crypto->getName(),
                $crypto->getSymbol(),
                $crypto->getPrice()
            ], $cryptos);
            TableRenderer::render(['ID', 'Name', 'Symbol', 'Price'], $rows);
            break;
        case 4: // Search cryptocurrency
            $symbol = readline("Enter the cryptocurrency symbol: ");
            $crypto = $apiClient->getCryptoBySymbol($symbol);
            if ($crypto) {
                TableRenderer::render(['ID', 'Name', 'Symbol', 'Price'], [[
                    $crypto->getId(),
                    $crypto->getName(),
                    $crypto->getSymbol(),
                    $crypto->getPrice()
                ]]);
            } else {
                echo "Cryptocurrency not found.\n";
            }
            break;
        case 5: // Purchase cryptocurrency
            $symbol = readline("Enter the cryptocurrency symbol: ");
            $crypto = $apiClient->getCryptoBySymbol($symbol);
            if ($crypto) {
                $amount = (float)readline("Enter the amount to purchase: ");
                if ($walletService->purchaseCrypto($crypto, $amount)) {
                    echo "Purchase successful.\n";
                } else {
                    echo "Insufficient balance.\n";
                }
            } else {
                echo "Cryptocurrency not found.\n";
            }
            break;
        case 6: // Sell cryptocurrency
            $symbol = readline("Enter the cryptocurrency symbol: ");
            $crypto = $apiClient->getCryptoBySymbol($symbol);
            if ($crypto) {
                $amount = (float)readline("Enter the amount to sell: ");
                if ($walletService->sellCrypto($crypto, $amount)) {
                    echo "Sell successful.\n";
                } else {
                    echo "Insufficient cryptocurrency amount.\n";
                }
            } else {
                echo "Cryptocurrency not found.\n";
            }
            break;
        case 7: // Display wallet state
            $walletOverview = $walletService->getWalletOverview();
            $headers = [
                'Symbol',
                'Amount',
                'Purchase Price $',
                'Current Price $',
                'Initial Value $',
                'Current Value $',
                'Profit/Loss $'];
            $rows = array_map(fn($item) => [
                $item['symbol'],
                $item['amount'],
                $item['purchasePrice'],
                $item['currentPrice'],
                $item['initialValue'],
                $item['currentValue'],
                $item['profitLoss']
            ], $walletOverview);
            TableRenderer::render($headers, $rows);
            echo "Balance: ". number_format($walletService->getUser()->getBalance(), 2) . "\n";
            break;
        case 8: // Display transaction history
            $transactions = $walletService->getTransactionHistory();
            $rows = array_map(fn($transaction) => [
                $transaction->getType(),
                $transaction->getSymbol(),
                $transaction->getAmount(),
                $transaction->getPrice(),
                $transaction->getTimestamp()
            ], $transactions);
            TableRenderer::render(['Type', 'Symbol', 'Amount', 'Price', 'Timestamp'], $rows);
            break;
        case 9:
            exit;
        default:
            echo "Invalid choice.\n";
            break;
    }
}
