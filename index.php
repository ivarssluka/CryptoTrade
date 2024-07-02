<?php

require 'vendor/autoload.php';

use Dotenv\Dotenv;
use FastRoute\RouteCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Pimple\Container;
use CryptoTrade\Services\User\RegisterUserService;
use CryptoTrade\Services\User\LoginUserService;
use CryptoTrade\Repositories\UserRepository;
use CryptoTrade\Repositories\WalletRepository;
use CryptoTrade\Repositories\TransactionRepository;
use CryptoTrade\Services\Database;
use CryptoTrade\Services\Wallet\WalletService;
use CryptoTrade\Services\Wallet\PurchaseCryptoService;
use CryptoTrade\Services\Wallet\SellCryptoService;
use CryptoTrade\Services\Wallet\WalletOverviewService;
use CryptoTrade\Services\Transactions\TransactionService;
use CryptoTrade\Api\CoinMarketCapApi;
use Symfony\Component\HttpFoundation\Session\Session;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$loader = new FilesystemLoader(__DIR__ . '/app/views');
$twig = new Environment($loader);

$container = new Container();

$container['twig'] = $twig;
$container['request'] = function() {
    return Request::createFromGlobals();
};

$session = new Session();
if (!$session->isStarted()) {
    $session->start();
}
$container['session'] = $session;

$container['database'] = function() {
    $database = new Database();
    $database->setupDatabase();
    return $database;
};

$container['walletRepository'] = function($c) {
    $connection = $c['database']->getConnection();
    return new WalletRepository($connection);
};

$container['userRepository'] = function($c) {
    $connection = $c['database']->getConnection();
    $walletRepository = $c['walletRepository'];
    return new UserRepository($connection, $walletRepository);
};

$container['transactionRepository'] = function($c) {
    $connection = $c['database']->getConnection();
    return new TransactionRepository($connection);
};

$container['registerUserService'] = function($c) {
    return new RegisterUserService($c['userRepository']);
};

$container['loginUserService'] = function($c) {
    return new LoginUserService($c['userRepository']);
};

$container['purchaseCryptoService'] = function($c) {
    return new PurchaseCryptoService($c['transactionRepository']);
};

$container['sellCryptoService'] = function($c) {
    return new SellCryptoService($c['transactionRepository']);
};

$container['apiClient'] = function($c) {
    return new CoinMarketCapApi();
};

$container['walletOverviewService'] = function($c) {
    return new WalletOverviewService($c['apiClient']);
};

$container['transactionService'] = function($c) {
    return new TransactionService($c['transactionRepository']);
};

$container['walletService'] = function($c) {
    $session = $c['session'];
    $user = $session->get('user');

    return new WalletService(
        $c['userRepository'],
        $c['purchaseCryptoService'],
        $c['sellCryptoService'],
        $c['walletOverviewService'],
        $c['transactionService'],
        $user
    );
};

$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
    $r->addRoute('GET', '/', ['CryptoTrade\Controllers\WelcomeController', 'index']);
    $r->addRoute('GET', '/home', ['CryptoTrade\Controllers\HomeController', 'index']);
    $r->addRoute('GET', '/register', ['CryptoTrade\Controllers\UserController', 'showRegisterForm']);
    $r->addRoute('POST', '/register', ['CryptoTrade\Controllers\UserController', 'register']);
    $r->addRoute('GET', '/login', ['CryptoTrade\Controllers\UserController', 'showLoginForm']);
    $r->addRoute('POST', '/login', ['CryptoTrade\Controllers\UserController', 'login']);
    $r->addRoute('GET', '/logout', ['CryptoTrade\Controllers\UserController', 'logout']);
    $r->addRoute('GET', '/wallet', ['CryptoTrade\Controllers\WalletController', 'overview']);
    $r->addRoute('GET', '/transactions', ['CryptoTrade\Controllers\WalletController', 'transactionHistory']);
    $r->addRoute('POST', '/crypto/search', ['CryptoTrade\Controllers\HomeController', 'searchCrypto']);
    $r->addRoute('POST', '/crypto/buy', ['CryptoTrade\Controllers\CryptoController', 'buyCrypto']);
    $r->addRoute('POST', '/crypto/sell', ['CryptoTrade\Controllers\CryptoController', 'sellCrypto']);
    $r->addRoute('POST', '/wallet/add', ['CryptoTrade\Controllers\WalletController', 'addBalance']);
    $r->addRoute('POST', '/wallet/withdraw', ['CryptoTrade\Controllers\WalletController', 'withdrawBalance']);
    $r->addRoute('GET', '/welcome', ['CryptoTrade\Controllers\WelcomeController', 'showWelcome']);
});

$request = $container['request'];
$httpMethod = $request->getMethod();
$uri = $request->getPathInfo();

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        $response = new Response('404 Not Found', 404);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $response = new Response('405 Method Not Allowed', 405);
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        [$class, $method] = $handler;
        $controller = new $class($container);
        $response = $controller->$method($request, $vars);
        break;
}

$response->send();
