<?php

namespace App\EventListener;

use App\Exception\ForbiddenException;
use App\Helper\ApiMessages;
use App\Service\Mail\Notifier;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly LoggerInterface $logger,
        private readonly Notifier $notifier,
    )
    {}

    final public function onKernelException(ExceptionEvent $event): void
    {
        $notify = true;
        $exception = $event->getThrowable();

        if ($exception instanceof NotFoundHttpException) {
            $this->logger->error($exception->getMessage());
            $this->logger->debug($exception->getTraceAsString());
            $this->requestStack->getSession()->getFlashBag()->add(
                ApiMessages::STATUS_WARNING,
                "Cette page n'existe pas"
            );
            $event->setResponse(new RedirectResponse("/"));
            $event->getResponse()?->send();
            $notify = false;
        } else if ($exception instanceof AccessDeniedHttpException) {
            $this->logger->error($exception->getMessage());
            $this->logger->debug($exception->getTraceAsString());
            $this->requestStack->getSession()->getFlashBag()->add(
                ApiMessages::STATUS_DANGER,
                ForbiddenException::ACCESS_DENIED_EXCEPTION_MESSAGE
            );
            $event->setResponse(new RedirectResponse("/"));
            $event->getResponse()?->send();
            $notify = false;
        } else if ($exception instanceof MethodNotAllowedHttpException) {
            $this->logger->error($exception->getMessage());
            $this->logger->debug($exception->getTraceAsString());
            $this->requestStack->getSession()->getFlashBag()->add(
                ApiMessages::STATUS_DANGER,
                ForbiddenException::ACCESS_DENIED_EXCEPTION_MESSAGE
            );
            $event->setResponse(new RedirectResponse("/"));
            $event->getResponse()?->send();
            $notify = false;
        } else if ($exception instanceof SessionNotFoundException) {
            $this->logger->error($exception->getMessage());
            $this->logger->debug($exception->getTraceAsString());
            $this->requestStack->getSession()->getFlashBag()->add(
                ApiMessages::STATUS_DANGER,
                ForbiddenException::ACCESS_DENIED_EXCEPTION_MESSAGE
            );
            $event->setResponse(new RedirectResponse("/"));
            $event->getResponse()?->send();
            $notify = false;
        }

        // this spams the logs with email template
        // $notify && $this->notifier->sendErrorToWebmaster($event);
    }
}
