<?php

namespace App\Service\User;

use App\Entity\User;
use App\Form\UserType;
use App\Helper\ApiMessages;
use App\Service\Mail\Notifier;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

class UserPersister
{
    public function __construct(
        private readonly EntityManagerInterface         $em,
        private readonly LoggerInterface                $logger,
        private readonly UserPasswordHasherInterface    $passwordHasher,
        private readonly RequestStack                   $requestStack,
        private readonly Notifier                       $notifier,
        private readonly FormFactoryInterface           $formFactory,
        private readonly RouterInterface                $router,
        private readonly Environment                    $twig,
        private readonly Security                       $security,
    )
    {
    }

    final public function persist(?User $user, Request $request): Response
    {
        if (
            ($isNew = $user === null)
            && "user_edit" === $request->attributes->get("_route")
        ) {
            $this->requestStack->getSession()->getFlashBag()->add(
                ApiMessages::STATUS_WARNING,
                "Cet utilisateur n'existe pas"
            );
            $response = new RedirectResponse($this->router->generate("home"));
        } else {
            $user = $user ?? new User();
            $form = $this->formFactory->create(UserType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                !($isValid = $form->isValid())
                && (string)$form->getErrors() !== ""
                && $this->requestStack->getSession()->getFlashBag()->add(
                    ApiMessages::STATUS_WARNING,
                    (string)$form->getErrors()
                );

                if ($request->attributes->get("_route") == "registration") {
                    if ($form->get("agreeToTerms")->getData() === true) {
                        $user->agreeToTerms();
                    }
                };
                
                if (!$this->security->getUser()) {
                    $user->setRoles([User::ROLE_USER]);
                }

                $isValid
                && $this->save($user, $form)
                && ($response = new RedirectResponse($this->router->generate("home")));
            }
        }

        return $response
            ?? new Response($this->twig->render("registration/userForm.html.twig", [
                "form" => $form->createView(),
                "terms" => $isNew ? "Ajouter" : "Éditer"
            ]));
    }

    final public function profilePersist(User $user, Request $request)
    {
  
        $form = $this->formFactory->create(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            !($isValid = $form->isValid())
            && (string)$form->getErrors() !== ""
            && $this->requestStack->getSession()->getFlashBag()->add(
                ApiMessages::STATUS_WARNING,
                (string)$form->getErrors()
            );
            
            if (!$this->security->getUser()) {
                $user->setRoles([User::ROLE_USER]);
            }

            $isValid
            && $this->save($user, $form);
        }

        return $response
            ?? $form->createView();
    }

    final public function save(User $user, $form): bool
    {
        $result = false;
        $isNew = ($user->getId() === null);

        try {
            $isNew
            && ($randPassword = $this->passwordHasher->hashPassword($user, bin2hex(random_bytes(16))))
            && $user->setPassword($randPassword);

            $this->em->persist($user);
            $this->em->flush();
            $this->requestStack->getSession()->getFlashBag()->add(
                ApiMessages::STATUS_SUCCESS,
                "L'utilisateur " . $user->getFullname() . " a bien été " . ($isNew ? "créé. Un email vous a été envoyé pour finaliser l'inscription" : "édité")
            );
            $isNew && $this->notifier->notifyRegistration($user);
            $result = true;
        } catch (UniqueConstraintViolationException $exception) {
            $this->validateUniqueValues($form, $exception->getQuery()->getParams()[0]);
            $this->logger->error($exception->getQuery()->getParams()[0] . " already taken");
            $this->requestStack->getSession()->getFlashBag()->add(
                ApiMessages::STATUS_WARNING,
                $exception->getQuery()->getParams()[0] . " est déjà en cours d'utilisation par un autre utilisateur."
            );
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->debug($exception->getTraceAsString());
            $this->requestStack->getSession()->getFlashBag()->add(
                ApiMessages::STATUS_WARNING,
                ApiMessages::DEFAULT_ERROR_MESSAGE
            );
        }

        return $result;
    }

    final public function validateUniqueValues($form, $value): bool
    {
        $result = true;

        if ($form->get("email")->getData() == $value) {
            $form->get("email")->addError(
                new FormError(
                    User::EMAIL_ALREADY_USED
                )
            );
        }

        if ($form->get("pseudo")->getData() == $value) {
            $form->get("pseudo")->addError(
                new FormError(
                    User::PSEUDO_ALREADY_USED
                )
            );
        }

        return $result;
    }
}
