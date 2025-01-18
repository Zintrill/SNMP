<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SnmpController extends AbstractController
{
    /**
     * Statyczna trasa: /snmp
     */
    #[Route('/snmp', name: 'app_snmp_index')]
    public function index(): Response
    {
        // np. pobierasz listę urządzeń z bazy:
        // $devices = $this->getDoctrine()->getRepository(Device::class)->findAll();

        return $this->render('snmp/index.html.twig', [
            // 'devices' => $devices,
        ]);
    }

    /**
     * Dynamiczna trasa: /snmp/edit/{deviceId} -> np. edycja danego urządzenia
     */
    #[Route('/snmp/edit/{deviceId}', name: 'app_snmp_edit', requirements: ['deviceId' => '\d+'])]
    public function edit(int $deviceId): Response
    {
        // $device = $this->getDoctrine()->getRepository(Device::class)->find($deviceId);
        // if (!$device) { throw $this->createNotFoundException('Brak takiego urządzenia'); }

        return $this->render('snmp/edit.html.twig', [
            'deviceId' => $deviceId,
            // 'device' => $device,
        ]);
    }
}
