<?php

namespace App\Controller;

use App\Entity\Device;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard_index', methods: ['GET'])]
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        return $this->render('dashboard/index.html.twig', [
            'username' => $user ? $user->getUserIdentifier() : 'Guest',
        ]);
    }

    #[Route('/dashboard/getDeviceStatistics', name: 'app_dashboard_get_device_statistics', methods: ['GET'])]
    public function getDeviceStatistics(EntityManagerInterface $entityManager): JsonResponse
    {
        $devices = $entityManager->getRepository(Device::class)->findAll();
        
        $statusCountsAll = ['Online' => 0, 'Offline' => 0, 'Waiting' => 0];
        $statusCountsSwitches = ['Online' => 0, 'Offline' => 0, 'Waiting' => 0];

        foreach ($devices as $device) {
            $status = ucfirst(strtolower($device->getStatus())); // Upewniamy się, że statusy mają prawidłowy format

            if (array_key_exists($status, $statusCountsAll)) {
                $statusCountsAll[$status]++;
            }

            if ($device->getDeviceType()->getName() === 'Switch' && array_key_exists($status, $statusCountsSwitches)) {
                $statusCountsSwitches[$status]++;
            }
        }

        return new JsonResponse([
            'allDevices' => $statusCountsAll,
            'switches' => $statusCountsSwitches
        ]);
    }
}
