<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    const EMAIL_VERIFICATION_ROUTE_NAME = "security_confirmation_email";

    public function __construct(
        private readonly VerifyEmailHelperInterface $verifyEmailHelper,
        private readonly MailerInterface            $mailer
    )
    {
    }

    public function sendEmailConfirmation(TemplatedEmail $email): void
    {
        $this->mailer->send($email);
    }

    public function handleEmailConfirmation(User $user, string $urlSignatured): void
    {
        $this->verifyEmailHelper->validateEmailConfirmation(
            $urlSignatured,
            $user->getId(),
            $user->getEmail()
        );
    }
}
