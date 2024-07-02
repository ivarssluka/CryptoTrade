<?php

namespace CryptoTrade\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use CryptoTrade\Services\User\RegisterUserService;
use CryptoTrade\Services\User\LoginUserService;

class UserController
{
    private $twig;
    private $registerUserService;
    private $loginUserService;
    private $session;

    public function __construct($container)
    {
        $this->twig = $container['twig'];
        $this->registerUserService = $container['registerUserService'];
        $this->loginUserService = $container['loginUserService'];
        $this->session = $container['session'];
    }

    public function showRegisterForm(Request $request, $vars)
    {
        return new Response($this->twig->render('register.twig'));
    }

    public function register(Request $request, $vars)
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $this->registerUserService->registerUser($username, $password);
        return new RedirectResponse('/login');
    }

    public function showLoginForm(Request $request, $vars)
    {
        return new Response($this->twig->render('login.twig'));
    }

    public function login(Request $request, $vars)
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $user = $this->loginUserService->loginUser($username, $password);

        if ($user->getBalance() == 0) {
            $user->addBalance(1000);
            $this->registerUserService->save($user);
        }

        $this->session->set('user', $user);
        return new RedirectResponse('/home');
    }

    public function logout(Request $request, $vars)
    {
        $this->session->invalidate();
        return new RedirectResponse('/welcome');
    }
}
