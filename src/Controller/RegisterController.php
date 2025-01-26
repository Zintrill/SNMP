<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        Security $security
    ): Response {
        // Jeśli ktoś jest już zalogowany, np. przekieruj go na "dashboard"
        // (możesz to usunąć, jeżeli nie jest potrzebne)
        if ($security->getUser()) {
            return $this->redirectToRoute('app_dashboard_index');
        }

        if ($request->isMethod('POST')) {
            $fullname         = $request->request->get('fullname');
            $username         = $request->request->get('username');
            $email            = $request->request->get('email');
            $password         = $request->request->get('password');
            $confirmPassword  = $request->request->get('confirm_password');

            // Walidacja zgodności haseł
            if ($password !== $confirmPassword) {
                $this->addFlash('error', 'Passwords do not match.');
                return $this->redirectToRoute('app_register');
            }

            // Sprawdzenie, czy użytkownik o takiej nazwie już istnieje
            $existingUser = $entityManager->getRepository(User::class)->findOneBy([
                'username' => $username
            ]);
            if ($existingUser) {
                $this->addFlash('error', 'Username already exists.');
                return $this->redirectToRoute('app_register');
            }

            // Tworzymy nowego użytkownika
            $user = new User();
            $user->setFullname($fullname);
            $user->setUsername($username);
            $user->setEmail($email);

            // Haszowanie hasła
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            // Nadajemy rolę (przynajmniej ROLE_USER)
            $user->setRoles(['ROLE_USER']);

            // Zapis do bazy
            $entityManager->persist($user);
            $entityManager->flush();

            // ⚠️ Usuwamy automatyczne logowanie:
            // $security->login($user);

            // Komunikat i przekierowanie do logowania
            $this->addFlash('success', 'Rejestracja zakończona sukcesem! Teraz możesz się zalogować.');
            return $this->redirectToRoute('app_login');
        }

        // Jeśli GET – wyświetlamy formularz rejestracji
        return $this->render('security/register.html.twig');
    }
}
