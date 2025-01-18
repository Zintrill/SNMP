<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard_index')]
    public function index(): Response
    {
        // Przykładowe dane – np. pobieranie loginu zalogowanego użytkownika
        $username = 'JohnDoe'; // lub pobranie z tokena, np. $this->getUser()->getUsername()
        
        return $this->render('dashboard/index.html.twig', [
            'username' => $username,
            // Ewentualnie przekazanie innych danych (np. statystyk, danych do wykresów, itd.)
        ]);
    }
}
