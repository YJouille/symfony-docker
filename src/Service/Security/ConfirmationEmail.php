<?php

namespace App\Service\Security;

use App\Entity\User;
use App\Helper\ApiMessages;
use App\Security\EmailVerifier;
use App\Service\Mail\Notifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\InvalidSignatureException;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Twig\Environment;

class ConfirmationEmail
{
    public function __construct(
        private readonly RouterInterface            $router,
        private readonly RequestStack               $requestStack,
        private readonly ParameterBagInterface      $parameterBag,
        private readonly EmailVerifier              $emailVerifier,
        private readonly LoggerInterface            $logger,
        private readonly Environment                $renderer,
        private readonly VerifyEmailHelperInterface $verifyEmailHelper,
        private readonly EntityManagerInterface     $em,
        private readonly Authenticate               $authenticate,
        private readonly Notifier                   $notifier,
    )
    {
    }

    final public function processSendMailAccessRequest(Request $request): array
    {

        try {
            $content = json_decode($request->getContent(), true);
            $user = $this->em->getRepository(User::class)
                ->find($content["userId"]);

            ! $user instanceof User
            && throw new NotFoundHttpException("Cette adresse email n'a pas été trouvée");

            $user->setEmailVerified(false);
            $user->setPasswordChanged(false);
            $this->em->flush();
            $this->notifier->notifyAccessRequest($user);
            $result = ApiMessages::makeContent(
                ApiMessages::STATUS_SUCCESS,
                "Un email d'accès été envoyé à " . $user->getEmail(),
            );
        } catch (NotFoundHttpException $exception) {
            $this->logger->warning($exception->getMessage());
            $result = ApiMessages::makeContent(
                ApiMessages::STATUS_WARNING,
                $exception->getMessage(),
            );
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->debug($exception->getTraceAsString());
            $result = ApiMessages::makeDefaultErrorContent();
        }

        return $result;
    }

    final public function processConfirmationEmailRequest(Request $request): Response
    {
        
        try {
            $redirectRoute = "login";
            $user = $this->em->getRepository(User::class)
                ->find($request->query->get("id"));

            ! $user instanceof User
            && throw new ResourceNotFoundException("EmailConfirmation : Tentative d'accès avec un email erroné");

            if (! $user->hasEmailVerified() && $this->verifyEmail($user, $request)) {
                $redirectRoute = "security_set_password";
                $this->authenticate->process($user, $request);
                $this->requestStack->getSession()->getFlashBag()->add(
                    ApiMessages::STATUS_INFO,
                    "Veuillez modifier votre mot de passe pour activer votre compte"
                );
            }
        } catch (VerifyEmailExceptionInterface $exception) {
            $message = $exception instanceof InvalidSignatureException
                ? "Signature invalide"
                : "Email expiré";
            $this->logger->warning("$message :\n" . $exception->getMessage());
            $user
            && $this->resendEmailConfirmation($user)
            && $this->requestStack->getSession()->getFlashBag()->add(
                ApiMessages::STATUS_WARNING,
                "La validation de votre compte n'a pas pu aboutir, "
                . "un email vous a de nouveau été envoyé par mesure de sécurité"
            );
        } catch (ResourceNotFoundException $exception) {
            $this->logger->warning($exception->getMessage());
        } catch (\Exception $exception) {
            $this->logger->error(
                "Une erreur est survenue pendant l'envoi du mail de confirmation :\n"
                . $exception->getMessage()
            );
            $this->logger->debug($exception->getTraceAsString());
        }

        return new RedirectResponse($this->router->generate($redirectRoute));
    }

    private function verifyEmail(User $user, Request $request): bool
    {
        ! $user->hasEmailVerified()
        && $this->emailVerifier->handleEmailConfirmation($user, $request->getUri());

        $this->em->persist(
            $user->setEmailVerified(true)
        );
        $this->em->flush();

        return true;
    }

    private function resendEmailConfirmation(User $user): bool
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            EmailVerifier::EMAIL_VERIFICATION_ROUTE_NAME,
            $user->getId(),
            $user->getEmail()
        );

        $this->emailVerifier->sendEmailConfirmation(
            (new TemplatedEmail())
                ->from(new Address(
                    $this->parameterBag->get("app.mail_contact_from"),
                    Notifier::CONTACT_FROM
                ))
                ->to($user->getEmail())
                ->subject("Confirmation d'inscription")
                ->html($this->renderer->render("mail/credentials/confirmationEmail.html.twig", [
                    "name" => $user->getFullname(),
                    "signedUrl" => $signatureComponents->getSignedUrl(),
                    "expiresAt" => $signatureComponents->getExpiresAt(),
                    "user" => $user,
                    "title" => "Votre invitation pour rejoindre la communauté"
                ]))
        );

        $this->logger->info("Registration mail sent to user " . $user->getUserIdentifier());

        return true;
    }
}
