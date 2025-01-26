<?php
// src/DataFixtures/AppFixtures.php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Tworzenie Administratora
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword(
            $this->passwordHasher->hashPassword(
                $admin,
                'adminpassword'
            )
        );
        $manager->persist($admin);

        // Tworzenie Operatora
        $operator = new User();
        $operator->setUsername('operator');
        $operator->setEmail('operator@example.com');
        $operator->setRoles(['ROLE_OPERATOR']);
        $operator->setPassword(
            $this->passwordHasher->hashPassword(
                $operator,
                'operatorpassword'
            )
        );
        $manager->persist($operator);

        // Tworzenie Standardowego Użytkownika
        $user = new User();
        $user->setUsername('user');
        $user->setEmail('user@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                'userpassword'
            )
        );
        $manager->persist($user);

        $manager->flush();
    }
}
