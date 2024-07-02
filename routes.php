<?php

return [
    ['GET', '/', ['CryptoTrade\Controllers\WelcomeController', 'index']],
    ['GET', '/home', ['CryptoTrade\Controllers\HomeController', 'index']],
    ['GET', '/register', ['CryptoTrade\Controllers\UserController', 'showRegisterForm']],
    ['POST', '/register', ['CryptoTrade\Controllers\UserController', 'register']],
    ['GET', '/login', ['CryptoTrade\Controllers\UserController', 'showLoginForm']],
    ['POST', '/login', ['CryptoTrade\Controllers\UserController', 'login']],
    ['GET', '/logout', ['CryptoTrade\Controllers\UserController', 'logout']],
    ['GET', '/wallet', ['CryptoTrade\Controllers\WalletController', 'overview']],
    ['GET', '/transactions', ['CryptoTrade\Controllers\WalletController', 'transactionHistory']],
    ['POST', '/crypto/search', ['CryptoTrade\Controllers\HomeController', 'searchCrypto']],
    ['POST', '/crypto/buy', ['CryptoTrade\Controllers\CryptoController', 'buyCrypto']],
    ['POST', '/crypto/sell', ['CryptoTrade\Controllers\CryptoController', 'sellCrypto']],
    ['POST', '/wallet/add', ['CryptoTrade\Controllers\WalletController', 'addBalance']],
    ['POST', '/wallet/withdraw', ['CryptoTrade\Controllers\WalletController', 'withdrawBalance']],
    ['GET', '/welcome', ['CryptoTrade\Controllers\WelcomeController', 'showWelcome']]
];