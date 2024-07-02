<?php

namespace CryptoTrade\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use CryptoTrade\Api\CoinMarketCapApi;

class HomeController
{
    private $twig;
    private $apiClient;

    public function __construct($container)
    {
        $this->twig = $container['twig'];
        $this->apiClient = new CoinMarketCapApi();
    }

    public function index()
    {
        $session = new Session();
        $user = $session->get('user');

        if (!$user) {
            return new RedirectResponse('/welcome');
        }

        $topCryptos = $this->apiClient->getTopCryptos(10);

        return new Response($this->twig->render('home.twig', [
            'user' => $user,
            'topCryptos' => $topCryptos
        ]));
    }

    public function searchCrypto(Request $request)
    {
        $symbol = $request->request->get('symbol');
        $crypto = $this->apiClient->getCryptoBySymbol($symbol);

        return new Response($this->twig->render('crypto.twig', ['crypto' => $crypto]));
    }
}
