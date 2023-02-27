<?php

namespace App\Service\Security;

use App\Helper\ApiMessages;
use App\Security\UserChecker;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

class LoginService
{
    public function __construct(
        private readonly UserChecker $checker,
        private readonly AuthenticationUtils $authenticationUtils,
        private readonly Environment $twig,
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack,
    )
    {
    }

    final public function authenticate(?UserInterface $user): Response
    {
        try {
            (
                $user !== null
                && $this->checker->checkPreAuth($user)
            )
            || throw new NotFoundHttpException();

            $response = new RedirectResponse($this->router->generate("home"));
        } catch (\Throwable $e) {
            ($error = $this->authenticationUtils->getLastAuthenticationError()) instanceof AuthenticationException
            && $this->requestStack->getSession()->getFlashBag()->add(
                ApiMessages::STATUS_DANGER,
                $error->getMessage()
            );

            $response = new Response($this->twig->render("security/login.html.twig", [
                "last_username" => $this->authenticationUtils->getLastUsername()
            ]));
        }

        return $response;
    }
}
