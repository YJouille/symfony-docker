<?php

namespace App\Service\Entity;

use App\Exception\BusinessLogicException;
use App\Helper\ApiMessages;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class EntityActivate
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
        private readonly CsrfTokenManagerInterface $tokenManager,
    )
    {
    }

    final public function process(Request $request, $entityClass, $entityRepository, $entityCsrf, $entityMessage, $entityMaleGender = true): array
    {
        try {
            $content = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            ! ($entityValue = $entityRepository->find($content["id"] ?? null)) instanceof $entityClass
            && throw new NotFoundHttpException($entityMessage . " n'existe pas");

            ! $this->tokenManager->isTokenValid(
                new CsrfToken($entityCsrf . $entityValue->getId(), $content["token"])
            ) && throw new BadRequestException("Le formulaire a expiré, veuillez recharger votre page");

            (!empty($entityValue->getArchivedAt())
                && $this->removeArchived($entityValue)
            ) || throw new BusinessLogicException($entityMessage . " est déjà " . ($entityMaleGender ? "activé" : "activée"));

            $this->em->flush();
            $result = ApiMessages::makeContent(
                ApiMessages::STATUS_SUCCESS,
                $entityMessage . " a bien été "
                . ($entityMaleGender ? "activé" : "activée")
            );
        } catch (BusinessLogicException|NotFoundHttpException|BadRequestException $exception) {
            $this->logger->notice($exception->getMessage());
            $result = ApiMessages::makeContent(
                ApiMessages::STATUS_WARNING,
                $exception->getMessage(),
            );
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->debug($exception->getTraceAsString());
            $result = ApiMessages::makeContent(
                ApiMessages::STATUS_WARNING,
                $exception->getMessage(),
            );
        }

        return $result;
    }


    private function removeArchived($entityValue): bool
    {
        $entityValue->setArchivedAt(null);

        return true;
    }
}
