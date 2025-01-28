<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UsersController extends AbstractController
{
    /**
     * Wyświetla listę użytkowników.
     * URL: /users
     * Nazwa trasy: app_users_list
     */
    #[Route('/users', name: 'app_users_list', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        return $this->render('users/index.html.twig', [
            'username' => $user ? $user->getUserIdentifier() : 'Guest',
            'users' => $users,
            'user_role' => $this->getUser() ? $this->getUser()->getRoles()[0] : 'guest',
        ]);
    }
    /**
     * Zwraca listę użytkowników w formacie JSON.
     * URL: /getUsers
     * Nazwa trasy: app_get_users
     */
    #[Route('/getUsers', name: 'app_get_users', methods: ['GET'])]
    public function getUsers(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        
        $roleLabels = [
            'ROLE_ADMIN' => 'Admin',
            'ROLE_OPERATOR' => 'Operator',
            'ROLE_USER' => 'User'
        ];
    
        $data = array_map(fn($user) => [
            'id' => $user->getId(),
            'fullname' => $user->getFullname(),
            'username' => $user->getUsername(),
            'password' => $user->getPassword(), // Hasło w bazie
            'role' => $roleLabels[$user->getRoles()[0]] ?? 'Unknown',
            'email' => $user->getEmail(),
        ], $users);
    
        return new JsonResponse($data);
    }

    /**
     * Dodaje nowego użytkownika.
     * URL: /addUser
     * Nazwa trasy: app_add_user
     */
    #[Route('/addUser', name: 'app_add_user', methods: ['POST'])]
    public function addUser(Request $request, UserRepository $userRepository, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $username = $request->request->get('username');
        $email = $request->request->get('email');
        $password = $request->request->get('userPassword');
        $fullname = $request->request->get('fullName');
        $roleValue = $request->request->get('userRole'); // Oczekuje np. 'ROLE_ADMIN'
    
        // Walidacja roli
        $validRoles = ['ROLE_ADMIN', 'ROLE_OPERATOR', 'ROLE_USER'];
        $role = in_array($roleValue, $validRoles) ? [$roleValue] : ['ROLE_USER'];
    
        // Walidacja czy użytkownik istnieje
        if ($userRepository->findOneBy(['username' => $username])) {
            return new JsonResponse(['status' => 'error', 'message' => 'Username is already taken'], 400);
        }
    
        if ($userRepository->findOneBy(['email' => $email])) {
            return new JsonResponse(['status' => 'error', 'message' => 'Email is already taken'], 400);
        }
    
        // Tworzenie nowego użytkownika
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setFullname($fullname);
        $user->setRoles($role); // Poprawnie przypisana rola
        $user->setPassword($passwordHasher->hashPassword($user, $password));
    
        $em->persist($user);
        $em->flush();
    
        return new JsonResponse(['status' => 'success']);
    }
    

    /**
     * Aktualizuje istniejącego użytkownika.
     * URL: /updateUser
     * Nazwa trasy: app_update_user
     */
    #[Route('/updateUser', name: 'app_update_user', methods: ['POST'])]
public function updateUser(Request $request, UserRepository $userRepository, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
{
    $userId = $request->request->get('userId');
    
    if (!$userId) {
        return new JsonResponse(['status' => 'error', 'message' => 'User ID is required'], 400);
    }

    $user = $userRepository->find($userId);

    if (!$user) {
        return new JsonResponse(['status' => 'error', 'message' => 'User not found'], 404);
    }

    $username = $request->request->get('username');
    $email = $request->request->get('email');
    $newPassword = $request->request->get('userPassword');
    $fullname = $request->request->get('fullName');
    $role = $request->request->get('userRole');

    // Sprawdzamy, czy użytkownik już istnieje z podanym username (ale ignorujemy aktualnie edytowanego)
    $existingUser = $userRepository->findOneBy(['username' => $username]);
    if ($existingUser && $existingUser->getId() !== $user->getId()) {
        return new JsonResponse(['status' => 'error', 'message' => 'Username is already taken'], 400);
    }

    $existingEmail = $userRepository->findOneBy(['email' => $email]);
    if ($existingEmail && $existingEmail->getId() !== $user->getId()) {
        return new JsonResponse(['status' => 'error', 'message' => 'Email is already taken'], 400);
    }

    // Aktualizujemy dane użytkownika
    $user->setUsername($username);
    $user->setEmail($email);
    $user->setFullname($fullname);
    $user->setRoles([$role]);

    if (!empty($newPassword)) {
        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
    }

    $em->flush();

    return new JsonResponse(['status' => 'success']);
}


    /**
     * Usuwa użytkownika.
     * URL: /deleteUser
     * Nazwa trasy: app_delete_user
     */
    #[Route('/deleteUser', name: 'app_delete_user', methods: ['POST'])]
    public function deleteUser(Request $request, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $userId = $request->request->get('userId');
        $user = $userRepository->find($userId);

        if (!$user) {
            return new JsonResponse(['status' => 'error', 'message' => 'User not found'], 404);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(['status' => 'success']);
    }

    /**
     * Pobiera użytkownika po ID.
     * URL: /getUserById
     * Nazwa trasy: app_get_user_by_id
     */
    #[Route('/getUserById', name: 'app_get_user_by_id', methods: ['GET'])]
    public function getUserById(Request $request, UserRepository $userRepository): JsonResponse
    {
        $userId = $request->query->get('id');
        $user = $userRepository->find($userId);

        if (!$user) {
            return new JsonResponse(['status' => 'error', 'message' => 'User not found'], 404);
        }

        $data = [
            'id' => $user->getId(),
            'fullname' => $user->getFullname(),
            'username' => $user->getUsername(),
            'role' => implode(', ', $user->getRoles()),
            'email' => $user->getEmail(),
        ];

        return new JsonResponse($data);
    }
}
