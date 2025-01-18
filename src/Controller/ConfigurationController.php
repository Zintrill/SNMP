<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConfigurationController extends AbstractController
{
    /**
     * Strona główna konfiguracji – wyświetla listę konfiguracji.
     * URL: /configuration
     * Nazwa trasy: app_configuration_index
     */
    #[Route('/configuration', name: 'app_configuration_index')]
    public function index(): Response
    {
        // Przykładowe dane – w praktyce pobierasz je z bazy danych
        $devices = [
            ['id' => 1, 'name' => 'Device One', 'type' => 'Router', 'ip' => '192.168.1.1', 'snmpVersion' => 'SNMPv2c', 'status' => 'Online'],
            ['id' => 2, 'name' => 'Device Two', 'type' => 'Switch', 'ip' => '192.168.1.2', 'snmpVersion' => 'SNMPv3', 'status' => 'Offline'],
            // ... inne rekordy
        ];

        return $this->render('configuration/index.html.twig', [
            'devices' => $devices,
        ]);
    }

    /**
     * Formularz dodawania nowej konfiguracji.
     * URL: /configuration/add
     * Nazwa trasy: app_configuration_add
     */
    #[Route('/configuration/add', name: 'app_configuration_add')]
    public function add(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Przetwarzaj dane formularza, np. walidacja, zapis do bazy, itp.
            // Po zapisaniu, przekieruj na listę konfiguracji:
            return $this->redirectToRoute('app_configuration_index');
        }

        // Jeśli metoda GET – wyświetl formularz dodawania.
        // Możesz utworzyć specjalny szablon (np. configuration/add.html.twig) dla formularza,
        // lub, jeżeli chcesz, wyświetlić modal w widoku index. Tu zakładamy osobny szablon.
        return $this->render('configuration/add.html.twig');
    }
}
