<?php

namespace App\Controller;

use App\Entity\User;
use App\Helper\ApiMessages;
use App\Repository\UserRepository;
use App\Service\Entity\EntityActivate;
use App\Service\Entity\EntityArchive;
use App\Service\User\UserPersister;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    #[Route("/inscription", name: "registration")]
    final public function registration(?User $user, Request $request, UserPersister $userPersister): Response
    {
        if ($this->getUser()) {
            $response = $this->redirectToRoute("home");
        } else {
            $response = $userPersister->persist($user, $request);
        }

        return $response;
    }

    #[Route("/admin/user/archive", name: "user_archive", methods: "PUT")]
    final public function userArchive(
        Request $request,
        UserRepository $userRepository,
        EntityArchive $entityArchive,
    ): JsonResponse
    {
        return $this->json(
            $entityArchive->process($request, User::class, $userRepository, "user", "L'utilisateur")
        );
    }

    #[Route("/admin/user/activate", name: "user_activate", methods: "PUT")]
    final public function userActivate(
        Request $request,
        UserRepository $userRepository,
        EntityActivate $entityActivate,
    ): Response
    {
        return $this->json(
            $entityActivate->process($request, User::class, $userRepository, "user", "L'utilisateur")
        );
    }

    #[Route("/admin/utilisateur/ajouter", name: "user_add")]
    #[Route("/admin/utilisateur/editer/{idUser}", name: "user_edit")]
    #[ParamConverter("user", options: ["mapping" => ["idUser" => "id"]])]
    final public function userAddEdit(?User $user, Request $request, UserPersister $userPersister): Response
    {
        return $userPersister->persist($user, $request);
    }
}
