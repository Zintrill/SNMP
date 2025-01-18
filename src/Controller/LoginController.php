<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(SessionInterface $session): Response
    {
        // Tymczasowo nie pobieramy flash messages,
        // więc inicjalizujemy $messages jako pustą tablicę.
        $messages = [];
        
        return $this->render('login/index.html.twig', [
            'messages' => $messages,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
