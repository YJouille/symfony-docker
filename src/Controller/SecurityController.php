<?php

namespace App\Controller;

use App\Exception\RedirectException;
use App\Service\Security\ConfirmationEmail;
use App\Service\Security\PasswordService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route("/security/confirmation-email", name: "security_confirmation_email", methods: "GET")]
    final public function securityConfirmationEmail(
        Request                $request,
        ConfirmationEmail      $confirmationEmail,
    ): Response
    {
        return $confirmationEmail->processConfirmationEmailRequest($request);
    }

    #[Route("/security/set-password", name: "security_set_password")]
    final public function securitySetPassword(Request $request, PasswordService $passwordService): Response
    {
        ! ($user = $this->getUser())
        && throw new RedirectException($this->getParameter("app.base_url"));

        return $passwordService->processResetForm($request, $user);
    }

    #[Route("/security/forgottenPassword", name: "security_forgotten_password", methods: ["POST"])]
    final public function securityForgottenPassword(Request $request, PasswordService $passwordService): JsonResponse
    {
        return $this->json(
            $passwordService->processForgottenPasswordRequest($request)
        );
    }

    #[Route("/security/mailAccess", name: "security_mail_access", methods: ["POST"])]
    final public function securityMailAccess(Request $request, ConfirmationEmail $confirmationEmail): JsonResponse
    {
        return $this->json(
            $confirmationEmail->processSendMailAccessRequest($request)
        );
    }
}
