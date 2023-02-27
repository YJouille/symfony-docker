<?php

namespace App\Security;

use App\Entity\User;
use App\Helper\ApiMessages;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = "login";

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack          $requestStack,
    )
    {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get("email", "");

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        $badges = [
            new CsrfTokenBadge("authenticate", $request->request->get("_csrf_token")),
        ];

        if (!empty($request->request->get("_remember_me"))) {
            $badges[] = new RememberMeBadge();
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get("password", "")),
            $badges
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate("home"));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $exception = new AuthenticationException(
            $exception instanceof BadCredentialsException
                ? "Le mot de passe saisi est incorrect"
                : nl2br($exception->getMessage()),
            $exception->getCode(),
            $exception
        );

        return parent::onAuthenticationFailure($request, $exception);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
