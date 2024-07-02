<?php

namespace CryptoTrade\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;

class WalletController
{
    private $twig;
    private $walletService;

    public function __construct($container)
    {
        $this->twig = $container['twig'];
        $this->walletService = $container['walletService'];
    }

    public function overview()
    {
        $session = new Session();
        $user = $session->get('user');

        if (!$user) {
            return new RedirectResponse('/login');
        }

        $walletOverview = $this->walletService->getWalletOverview();

        return new Response($this->twig->render('wallet.twig', [
            'user' => $user,
            'walletOverview' => $walletOverview
        ]));
    }

    public function transactionHistory()
    {
        $session = new Session();
        $user = $session->get('user');

        if (!$user) {
            return new RedirectResponse('/login');
        }

        $transactionHistory = $this->walletService->getTransactionHistory();

        return new Response($this->twig->render('transactions.twig', [
            'user' => $user,
            'transactionHistory' => $transactionHistory
        ]));
    }

    public function addBalance(Request $request): RedirectResponse
    {
        $session = new Session();
        $user = $session->get('user');

        if (!$user) {
            return new RedirectResponse('/login');
        }

        $amount = (float)$request->request->get('amount');
        $user->addBalance($amount);
        $this->walletService->saveWallet();

        return new RedirectResponse('/wallet');
    }

    public function withdrawBalance(Request $request)
    {
        $session = new Session();
        $user = $session->get('user');

        if (!$user) {
            return new RedirectResponse('/login');
        }

        $amount = (float)$request->request->get('amount');
        if ($user->getBalance() < $amount) {
            return new Response('Insufficient balance.', 400);
        }

        $user->subtractBalance($amount);
        $this->walletService->saveWallet();

        return new RedirectResponse('/wallet');
    }
}
