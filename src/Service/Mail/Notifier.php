<?php

namespace App\Service\Mail;

use App\Entity\User;
use App\Security\EmailVerifier;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Security;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Twig\Environment;

class Notifier
{
    const CONTACT_FROM = "XXX";
    const CONTACT_TO = "XXX";

    public function __construct(
        private readonly VerifyEmailHelperInterface $verifyEmailHelper,
        private readonly Environment                $renderer,
        private readonly ParameterBagInterface      $parameterBag,
        private readonly LoggerInterface            $logger,
        private readonly Security                   $security,
        private readonly MailerInterface            $mailer,
    )
    {
    }

    private function send(TemplatedEmail $templatedEmail): void
    {
        try {
            $this->mailer->send($templatedEmail);
            $this->logger->info("[NOTIFY] " . $templatedEmail->getSubject() . " " .
                $this->security->getUser()?->getEmail() ?? "anonymous user"
            );
        } catch (\Throwable $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }

    final public function notifyRegistration(User $user): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            EmailVerifier::EMAIL_VERIFICATION_ROUTE_NAME,
            $user->getId(),
            $user->getEmail(),
            ["id" => $user->getId()]
        );

        $this->send(
            (new TemplatedEmail())
                ->from(new Address($this->parameterBag->get("app.mail_contact_from"), self::CONTACT_FROM))
                ->to($user->getEmail())
                ->subject("Votre lien d'activation")
                ->html($this->renderer->render("mail/credentials/confirmationEmail.html.twig", [
                    "name" => $user->getFirstname() . " " . $user->getLastname(),
                    "signedUrl" => $signatureComponents->getSignedUrl(),
                    "expiresAt" => $signatureComponents->getExpiresAt(),
                    "user" => $user,
                    "title" => "Votre lien d'activation sur la plateforme"
                ]))
        );
    }

    final public function passwordChanged(User $user): void
    {
        $this->send(
            (new TemplatedEmail())
                ->from(new Address($this->parameterBag->get("app.mail_contact_from"), self::CONTACT_FROM))
                ->to($user->getEmail())
                ->subject("Changement de mot de passe")
                ->html($this->renderer->render("mail/credentials/confirmPasswordChanged.html.twig", ["user" => $user]))
        );
    }

    final public function notifyPasswordResetRequest(User $user): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            EmailVerifier::EMAIL_VERIFICATION_ROUTE_NAME,
            $user->getId(),
            $user->getEmail(),
            ["id" => $user->getId()]
        );

        $this->send(
            (new TemplatedEmail())
                ->from(new Address($this->parameterBag->get("app.mail_contact_from"), self::CONTACT_FROM))
                ->to($user->getEmail())
                ->subject("Demande de réinitialisation de mot de passe")
                ->html($this->renderer->render("mail/credentials/forgottenPassword.html.twig", [
                    "name" => $user->getFullname(),
                    "signedUrl" => $signatureComponents->getSignedUrl(),
                    "expiresAt" => $signatureComponents->getExpiresAt(),
                    "user" => $user,
                    "title" => "Votre demande de réinitialisation de mot de passe"
                ]))
        );
    }

    final public function notifyAccessRequest(User $user): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            EmailVerifier::EMAIL_VERIFICATION_ROUTE_NAME,
            $user->getId(),
            $user->getEmail(),
            ["id" => $user->getId()]
        );

        $this->send(
            (new TemplatedEmail())
                ->from(new Address($this->parameterBag->get("app.mail_contact_from"), self::CONTACT_FROM))
                ->to($user->getEmail())
                ->subject("Votre accès à la plateforme")
                ->html($this->renderer->render("mail/credentials/access.html.twig", [
                    "name" => $user->getFullname(),
                    "signedUrl" => $signatureComponents->getSignedUrl(),
                    "expiresAt" => $signatureComponents->getExpiresAt(),
                    "user" => $user,
                    "title" => "Votre accès à la plateforme"
                ]))
        );
    }

    final public function sendErrorToWebmaster(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        $this->send(
            (new TemplatedEmail())
                ->from(new Address($this->parameterBag->get("app.mail_contact_from"), self::CONTACT_FROM))
                ->to(new Address($this->parameterBag->get("app.mail_webmaster")))
                ->subject("erreur 500")
                ->html($this->renderer->render("mail/security/error.html.twig", [
                    "errorLocationPath" => $request->getUri(),
                    "errorLocationUrl" => $request->getBaseUrl() . $request->getUri(),
                    "message" => $exception->getMessage(),
                    "stackstrace" => $exception->getTraceAsString()
                ]))
        );
    }
}
