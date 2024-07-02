<?php

namespace CryptoTrade\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use CryptoTrade\Api\CoinMarketCapApi;
use CryptoTrade\Services\Wallet\WalletService;

class CryptoController
{
    private $twig;
    private $apiClient;
    private $walletService;

    public function __construct($container)
    {
        $this->twig = $container['twig'];
        $this->apiClient = new CoinMarketCapApi();
        $this->walletService = $container['walletService'];
    }

    public function buyCrypto(Request $request, $vars)
    {
        $symbol = $request->request->get('symbol');
        $amount = (float)$request->request->get('amount');
        $crypto = $this->apiClient->getCryptoBySymbol($symbol);
        $session = new Session();
        $user = $session->get('user');

        if ($crypto) {
            if ($this->walletService->purchaseCrypto($crypto, $amount)) {
                $session->getFlashBag()->add('success', 'Purchase successful.');
                return new RedirectResponse('/wallet');
            }
            $session->getFlashBag()->add('error', 'Insufficient balance.');
            return new RedirectResponse('/wallet');
        }
        return new Response('Cryptocurrency not found.', 404);
    }

    public function sellCrypto(Request $request, $vars)
    {
        $symbol = $request->request->get('symbol');
        $amount = (float)$request->request->get('amount');
        $crypto = $this->apiClient->getCryptoBySymbol($symbol);
        $session = new Session();
        $user = $session->get('user');

        if ($crypto) {
            if ($this->walletService->sellCrypto($crypto, $amount)) {
                $session->getFlashBag()->add('success', 'Sell successful.');
                return new RedirectResponse('/wallet');
            }
            $session->getFlashBag()->add('error', 'Insufficient cryptocurrency amount.');
            return new RedirectResponse('/wallet');
        }
        return new Response('Cryptocurrency not found.', 404);
    }
}
