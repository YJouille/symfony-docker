<?php

namespace App\Tests\Trait;

use App\Entity\User;
use Symfony\Component\Panther\Client;

trait LoginTrait
{
    private static ?User $loggedUser = null;

    private array $loginSettings = [
        "admin" => [
            "email" => "admin0@admin0.com",
            "password" => "admin0",
            "hashedPassword" => '$2y$13$Ur1LuS3y6.nea7p5SroHneOMUpkvXV1bnj5BGmscUEuFxlCLQGY7u',
            "firstname" => "admin0",
            "lastname" => "admin0",
            "pseudo" => "admin0",
        ],
        "user" => [
            "email" => "user1@user1.com",
            "password" => "user1",
            "hashedPassword" => '$2y$13$GXZZCpDMwPyTz74jeJwlt.2NHpJ5oBS1X2Py8YrnxIZZcQL8rCsiC',
            "firstname" => "user1",
            "lastname" => "user1",
            "pseudo" => "user1",
        ],
    ];

    private function connectUser(User $user, string $role): Client
    {
        $client = self::getCustomClient();
        $client->request("GET",  self::getUrlFromRoute("logout"));
        self::assertPageTitleContains("Connexion");

        ($crawler = $client->waitFor("#inputEmail", 5))
            ->filter("#inputEmail")
            ->sendKeys($user->getEmail());
        $crawler->filter("#inputPassword")
            ->sendKeys($user->getPlainPassword() ?? $this->loginSettings[$role]["password"]);
        $crawler->filter("button[type=submit]")
            ->click();

        self::$loggedUser = $user;

        return $client;
    }

    private function createUserAdmin(?User $admin = null): User
    {
        ($em = self::getEntityManager())->persist(
            $admin = ($admin ?? new User())
                ->setFirstname($this->loginSettings["admin"]["firstname"])
                ->setLastname($this->loginSettings["admin"]["lastname"])
                ->setPseudo($this->loginSettings["admin"]["pseudo"])
                ->setEmail($this->loginSettings["admin"]["email"])
                ->setPlainPassword($this->loginSettings["admin"]["password"])
                ->setPassword($this->loginSettings["admin"]["hashedPassword"])
                ->setPasswordChanged(true)
                ->setEmailVerified(true)
                ->setRoles([User::ROLE_ADMIN, "ROLE_DEBUG"])
                ->setCreatedAt(new \DateTime())
                ->setAgreedTermsAt(new \DateTime())
        );
        $em->flush();

        return $admin;
    }

    private function findUserAdmin(): ?User
    {
        return self::getEntityManager()->getRepository(User::class)->findOneBy([
            "email" => $this->loginSettings["admin"]["email"]
        ]);
    }

    private function getUserAdmin(): ?User
    {
        return $this->createUserAdmin(
            $this->findUserAdmin()
        );
    }

    private function authenticateAdmin(?User $user = null): Client
    {
        return $this->connectUser(
            $user ?? $this->getUserAdmin(),
            "admin"
        );
    }

    private function createUserDefault(?User $user = null): User
    {
        ($em = self::getEntityManager())->persist(
            $user ?? ($user = new User())
                ->setFirstname($this->loginSettings["user"]["firstname"])
                ->setLastname($this->loginSettings["user"]["lastname"])
                ->setPseudo($this->loginSettings["user"]["pseudo"])
                ->setEmail($this->loginSettings["user"]["email"])
                ->setPlainPassword($this->loginSettings["user"]["password"])
                ->setPassword($this->loginSettings["user"]["hashedPassword"])
                ->setPasswordChanged(true)
                ->setEmailVerified(true)
                ->setRoles([User::ROLE_USER])
                ->setCreatedAt(new \DateTime())
                ->setAgreedTermsAt(new \DateTime())
        );
        $em->flush();

        return $user;
    }

    private function findUserDefault(): ?User
    {
        return self::getEntityManager()->getRepository(User::class)->findOneBy([
            "email" => $this->loginSettings["user"]["email"]
        ]);
    }

    private function getUserDefault(): ?User
    {
        return $this->createUserDefault(
            $this->findUserDefault()
        );
    }

    private function authenticateDefault(?User $user = null): Client
    {
        return $this->connectUser(
            $user ?? $this->getUserDefault(),
            "user"
        );
    }
}
