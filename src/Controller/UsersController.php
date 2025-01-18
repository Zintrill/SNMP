<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends AbstractController
{
    /**
     * Wyświetla listę użytkowników.
     * URL: /users
     * Nazwa trasy: app_users_list
     */
    #[Route('/users', name: 'app_users_list')]
    public function index(): Response
    {
        // Przykładowe dane; w praktyce pobierasz użytkowników z bazy danych.
        $users = [
            [
                'id' => 1,
                'fullName' => 'Alice Brown',
                'username' => 'alice',
                'password' => 'secret', // Hasło nie powinno być wyświetlane jawnie!
                'role' => 'Administrator',
                'email' => 'alice@example.com'
            ],
            [
                'id' => 2,
                'fullName' => 'Bob Smith',
                'username' => 'bob',
                'password' => 'secret',
                'role' => 'Technician',
                'email' => 'bob@example.com'
            ],
            // ... inne rekordy
        ];

        return $this->render('users/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * Wyświetla formularz dodawania nowego użytkownika oraz przetwarza dane przesłane metodą POST.
     * URL: /users/add
     * Nazwa trasy: app_user_add
     */
    #[Route('/users/add', name: 'app_user_add')]
    public function add(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Tutaj dodaj logikę przetwarzania formularza, np. walidację i zapis do bazy.
            // Po zapisaniu użytkownika przekieruj do listy użytkowników.
            return $this->redirectToRoute('app_users_list');
        }

        // Jeśli metoda GET – wyświetl formularz dodawania
        return $this->render('users/add.html.twig');
    }

    /**
     * Wyświetla szczegóły danego użytkownika.
     * URL: /users/{id}
     * Nazwa trasy: app_users_show
     * Wymaganie: id musi być liczbą
     */
    #[Route('/users/{id}', name: 'app_users_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        // W rzeczywistości pobierasz użytkownika z bazy danych.
        // Dla przykładu:
        $user = [
            'id' => $id,
            'fullName' => 'Example User',
            'username' => 'example',
            'password' => 'secret', // hasło oczywiście nie powinno być wyświetlane
            'role' => 'Operator',
            'email' => 'example@example.com'
        ];

        return $this->render('users/show.html.twig', [
            'user' => $user,
        ]);
    }
}
