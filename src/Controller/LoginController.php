<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use App\Message\UserLoggedInMessage;
use Symfony\Component\Messenger\MessageBusInterface;
class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
public function index(AuthenticationUtils $authenticationUtils, Request $request, MessageBusInterface $messageBus): Response
{
    if ($this->getUser()) {
        // Wysłanie wiadomości do RabbitMQ
        $messageBus->dispatch(new UserLoggedInMessage($this->getUser()->getUserIdentifier()));

        return $this->redirectToRoute('app_dashboard_index');
    }

    $error = $authenticationUtils->getLastAuthenticationError();
    $lastUsername = $authenticationUtils->getLastUsername();

    return $this->render('security/login.html.twig', [
        'last_username' => $lastUsername,
        'error' => $error,
    ]);
}


    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Ta metoda może być pusta - wylogowaniem zajmuje się firewall w security.yaml
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
