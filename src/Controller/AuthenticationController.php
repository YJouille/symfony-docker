<?php

namespace App\Controller;

use App\Service\Security\LoginService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthenticationController extends AbstractController
{
    #[Route("/login", name: "login_redirect")]
    final public function loginRedirect(): Response
    {
        return $this->redirectToRoute("login");
    }

    #[Route("/connexion", name: "login")]
    final public function login(LoginService $loginService): Response
    {
        return $loginService->authenticate($this->getUser());
    }

    #[Route("/logout", name: "logout_redirect")]
    final public function logoutRedirect(): Response
    {
        return $this->redirectToRoute("logout");
    }

    #[Route("/deconnexion", name: "logout")]
    final public function logout(): void
    {
        throw new \LogicException("This method can be blank - it will be intercepted by the logout key on your firewall.");
    }
}
