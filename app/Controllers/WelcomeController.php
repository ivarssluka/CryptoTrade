<?php

namespace CryptoTrade\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;

class WelcomeController
{
    private $twig;

    public function __construct($container)
    {
        $this->twig = $container['twig'];
    }

    public function index(): RedirectResponse
    {
        return new RedirectResponse('/welcome');
    }

    public function showWelcome()
    {
        $session = new Session();
        $user = $session->get('user');

        if ($user) {
            return new RedirectResponse('/home');
        }

        return new Response($this->twig->render('welcome.twig'));
    }
}
