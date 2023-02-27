<?php

namespace App\Service\Security;

use App\Entity\User;
use App\Exception\RedirectException;
use App\Form\ResetPasswordType;
use App\Helper\ApiMessages;
use App\Service\Mail\Notifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;

class PasswordService
{
    public const USER_UNKNOWN = "Cet utilisateur n'existe pas";
    public const FORM_EXPIRED = "Le formulaire a expiré, veuillez recharger votre page";
    public const RESET_PWD_MAILSENT = "Un email de réinitialisation du mot de passe vous a été envoyé.";
    public const RESET_PWD_MAIL_TITLE = "Demande de réinitialisation de mot de passe";
    public const SET_PWD_PAGE_TITLE = "Changement de votre mot de passe";
    public const APP_WELCOME = "Bienvenue sur la plateforme";

    public function __construct(
        private readonly UserPasswordHasherInterface    $hasher,
        private readonly EntityManagerInterface         $em,
        private readonly Notifier                       $notifier,
        private readonly FormFactoryInterface           $formFactory,
        private readonly RequestStack                   $requestStack,
        private readonly Environment                    $twig,
        private readonly RouterInterface                $router,
        private readonly LoggerInterface                $logger,
        private readonly CsrfTokenManagerInterface      $tokenManager,
    )
    {
    }

    final public function processForgottenPasswordRequest(Request $request): array
    {
        try {
            $content = json_decode($request->getContent(), true);
            $user = $this->em->getRepository(User::class)->findOneBy(["email" => $content["email"]]);

            ! $user instanceof User
            && throw new NotFoundHttpException(self::USER_UNKNOWN);

            ! $this->tokenManager->isTokenValid(new CsrfToken("forgotten-password", $content["token"]))
            && throw new BadRequestException(self::FORM_EXPIRED);

            $user->setEmailVerified(false);
            $user->setPasswordChanged(false);
            $this->em->flush();
            $this->notifier->notifyPasswordResetRequest($user);
            $result = ApiMessages::makeContent(
                ApiMessages::STATUS_SUCCESS,
                self::RESET_PWD_MAILSENT,
            );
        } catch (NotFoundHttpException|BadRequestException $exception) {
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

    final public function processResetForm(Request $request, User $user): Response
    {
        try {
            $user->hasPasswordChanged()
            && throw new RedirectException($this->router->generate("home"));

            $form = $this->formFactory->create(ResetPasswordType::class);
            $form->handleRequest($request);

            if (
                $form->isSubmitted() && $form->isValid()
                && $this->reset($user, $request->get("reset_password")["password"]["first"])
            ) {
                $this->requestStack->getSession()->getFlashBag()->add(
                    ApiMessages::STATUS_SUCCESS,
                    self::APP_WELCOME
                );
                $response = new RedirectResponse($this->router->generate("home"));
            }
        } catch (RedirectException $exception) {
            ! empty(($message = $exception->getMessage()))
            && $this->requestStack->getSession()->getFlashBag()->add(ApiMessages::STATUS_WARNING, $message);
            $this->logger->notice($exception->getMessage());
            $response = $exception->getRedirectResponse();
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->debug($exception->getTraceAsString());
            $this->requestStack->getSession()->getFlashBag()->add(
                ApiMessages::STATUS_DANGER,
                ApiMessages::DEFAULT_ERROR_MESSAGE
            );
        }

        return $response
            ?? new Response($this->twig->render("security/resetPassword.html.twig", [
                "form" => $form->createView()
            ]));
    }

    private function reset(User $user, string $newPassword): bool
    {
        $user
            ->setPassword($this->hasher->hashPassword($user, $newPassword))
            ->setPasswordChanged(true);
        $this->notifier->passwordChanged($user);
        $this->em->persist($user);
        $this->em->flush();

        return true;
    }
}
