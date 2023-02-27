<?php

namespace App\EventListener;

use App\Exception\RedirectException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class RedirectExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        if ($event->getThrowable() instanceof RedirectException) {
            $event->setResponse($event->getThrowable()->getRedirectResponse());
        }
    }
}
