<?php

namespace App\Service\Security;

use App\Entity\User;
use App\Security\LoginFormAuthenticator;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authenticator\InteractiveAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Event\AuthenticationTokenCreatedEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class Authenticate
{
    public function __construct(
        private readonly TokenStorageInterface    $tokenStorage,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoginFormAuthenticator   $authenticator,
    )
    {
    }

    final public function process(User $user, Request $request, array $attributes = []): ?Response
    {
        $firewallName = "main";

        /** @see AuthenticatorManager::authenticateUser() */

        $passport = new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier(), function () use ($user) {
                return $user;
            })
        );

        $token = $this->authenticator->createToken($passport, $firewallName);
        $token = $this->eventDispatcher->dispatch(
            new AuthenticationTokenCreatedEvent($token, $passport)
        )->getAuthenticatedToken();

        $token->setAttributes($attributes);

        /** @see AuthenticatorManager::handleAuthenticationSuccess() */

        $this->tokenStorage->setToken($token);
        $response = $this->authenticator->onAuthenticationSuccess($request, $token, $firewallName);

        if ($this->authenticator instanceof InteractiveAuthenticatorInterface && $this->authenticator->isInteractive()) {
            $loginEvent = new InteractiveLoginEvent($request, $token);
            $this->eventDispatcher->dispatch($loginEvent, SecurityEvents::INTERACTIVE_LOGIN);
        }

        $this->eventDispatcher->dispatch(
            $loginSuccessEvent = new LoginSuccessEvent(
                $this->authenticator,
                $passport,
                $token,
                $request,
                $response,
                $firewallName
            )
        );

        return $loginSuccessEvent->getResponse();
    }
}
