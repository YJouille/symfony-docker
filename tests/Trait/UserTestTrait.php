<?php

namespace App\Tests\Trait;

use App\Entity\User;

trait UserTestTrait
{
    private function persistDatabaseUser(User $user): User
    {
        ($em = self::getEntityManager())->persist($user);

        $em->flush();

        return $user;
    }

    private function resetDatabaseUser(): void
    {
        try {
            ($em = self::getEntityManager())->clear();
            ($entities = ($em = self::getEntityManager())->getRepository(User::class)->findAll())
            && array_map(static fn($entity) => $em->remove($entity), $entities);

            $em->flush();
        } catch (\Throwable $e) {}
    }
}
