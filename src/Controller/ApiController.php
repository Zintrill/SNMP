<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * Zwraca JSON ze statusem urządzeń.
     * Przykładowe dane: Online, Offline, Waiting.
     */
    #[Route('/getDeviceStatuses', name: 'get_device_statuses', methods: ['GET'])]
    public function getDeviceStatuses(): Response
    {
        // Przykładowe dane – w rzeczywistości pobierasz z bazy lub innego źródła
        $data = [
            ['status' => 'Online', 'count' => 10],
            ['status' => 'Offline', 'count' => 5],
            ['status' => 'Waiting', 'count' => 2],
        ];

        return $this->json($data);
    }

    /**
     * Zwraca JSON z listą obsługiwanych wersji SNMP.
     */
    #[Route('/getSnmpVersions', name: 'get_snmp_versions', methods: ['GET'])]
    public function getSnmpVersions(): Response
    {
        $data = [
            ['version' => 'SNMPv1'],
            ['version' => 'SNMPv2c'],
            ['version' => 'SNMPv3'],
        ];

        return $this->json($data);
    }

    /**
     * Zwraca JSON z listą typów urządzeń.
     */
    #[Route('/getDeviceTypes', name: 'get_device_types', methods: ['GET'])]
    public function getDeviceTypes(): Response
    {
        $data = [
            ['type' => 'Router'],
            ['type' => 'Switch'],
            ['type' => 'PC'],
            ['type' => 'Printer'],
            ['type' => 'Phone'],
            ['type' => 'TV'],
        ];

        return $this->json($data);
    }
}
