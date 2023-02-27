<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Color;
use App\Entity\Contact;
use App\Entity\Image;
use App\Entity\Product;
use App\Entity\Review;
use App\Entity\Size;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    /**
     * @var Generator
     */
    private Generator $faker;

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    )
    {
        $this->faker = Factory::create("fr_FR");
    }

    final public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 5; $i++) {
            $role = $i % 2 === 0 ? "admin" : "user";
            $manager->persist(($user = new User())
                ->setFirstname($role . $i)
                ->setLastname($role . $i)
                ->setPseudo($role . $i)
                ->setEmail($role . $i . "@" . $role . $i . ".com")
                ->setPassword($this->hasher->hashPassword($user, $role . $i))
                ->setPasswordChanged(true)
                ->setEmailVerified(true)
                ->setRoles([$i % 2 === 0 ? User::ROLE_ADMIN : User::ROLE_USER])
                ->setCreatedAt(new \DateTime())
                ->setAgreedTermsAt(new \DateTime())
            );
        }

        $manager->flush();
    }
}
