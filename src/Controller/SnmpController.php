<?php

namespace App\Controller;

use App\Entity\Device;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/snmp')]
class SnmpController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Strona główna SNMP Overview
     * Trasa: /snmp
     */
    #[Route('/', name: 'app_snmp_index', methods: ['GET'])]
    public function index(): Response
    {
        // Upewnij się, że użytkownik ma przynajmniej rolę ROLE_USER
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        return $this->render('snmp/index.html.twig', [
            'username' => $user ? $user->getUserIdentifier() : 'Guest',
        ]);
    }

    /**
     * API do pobierania statusów urządzeń
     * Trasa: /snmp/getDeviceStatuses
     */
    #[Route('/getDeviceStatuses', name: 'app_snmp_get_device_statuses', methods: ['GET'])]
    public function getDeviceStatuses(): JsonResponse
    {
        // Upewnij się, że użytkownik ma przynajmniej rolę ROLE_USER
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Pobierz wszystkie urządzenia
        $devices = $this->entityManager->getRepository(Device::class)->findAll();
        $data = [];

        // Sprawdzenie, czy użytkownik ma rolę admina lub operatora
        $isAdminOrOperator = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_OPERATOR');

        foreach ($devices as $device) {
            $deviceData = [
                'id' => $device->getId(),
                'device_name' => $device->getDeviceName(),
                'type' => $device->getDeviceType()->getName(),
                'type_id' => $device->getDeviceType()->getId(),
                'address_ip' => $device->getAddressIp(),
                'snmp_version' => $device->getSnmpVersion()->getVersion(),
                'snmp_version_id' => $device->getSnmpVersion()->getId(),
                'username' => $device->getUserName() ?? 'N/A',
                'description' => $device->getDescription() ?? 'N/A',
                'status' => $device->getStatus(),
                'mac_address' => $device->getMacAddress(),
                'uptime' => 'N/A', // Dodaj odpowiednie pole, jeśli jest dostępne
            ];

            // Dodaj hasło tylko dla administratorów i operatorów
            if ($isAdminOrOperator) {
                $deviceData['password'] = $device->getPassword();
            }

            $data[] = $deviceData;
        }

        return new JsonResponse($data);
    }

    /**
     * Wyświetlanie szczegółów urządzenia
     * Trasa: /snmp/edit/{deviceId}
     */
    #[Route('/edit/{deviceId}', name: 'app_snmp_edit', requirements: ['deviceId' => '\d+'], methods: ['GET'])]
    public function edit(int $deviceId): Response
    {
        // Sprawdzenie, czy użytkownik ma rolę ROLE_ADMIN lub ROLE_OPERATOR
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_OPERATOR')) {
            throw $this->createAccessDeniedException('Access denied. You do not have the required roles.');
        }

        // Pobranie urządzenia na podstawie ID
        $device = $this->entityManager->getRepository(Device::class)->find($deviceId);
        if (!$device) {
            throw $this->createNotFoundException('Brak takiego urządzenia');
        }

        return $this->render('snmp/edit.html.twig', [
            'deviceId' => $deviceId,
            'device' => $device,
        ]);
    }

    #[Route('/ping/{deviceId}', name: 'app_snmp_ping_device', methods: ['POST'])]
    public function pingDevice(int $deviceId): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
    
        $device = $this->entityManager->getRepository(Device::class)->find($deviceId);
        if (!$device) {
            return new JsonResponse(['error' => 'Urządzenie nie zostało znalezione.'], Response::HTTP_NOT_FOUND);
        }
    
        $ip = $device->getAddressIp();
        if (!$ip) {
            return new JsonResponse(['error' => 'Adres IP urządzenia jest nieprawidłowy.'], Response::HTTP_BAD_REQUEST);
        }
    
           // Wykrywanie systemu operacyjnego
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        // Wykonanie ping w zależności od systemu (więcej prób dla większej wiarygodności)
        $pingCommand = $isWindows
            ? sprintf('ping -n 4 %s', escapeshellarg($ip))  // Windows: -n liczba prób
            : sprintf('ping -c 4 %s', escapeshellarg($ip)); // Linux/macOS: -c liczba prób

        $pingResult = shell_exec($pingCommand);

        // Analiza wyniku pingowania (poszukiwanie "0% packet loss" w odpowiedzi)
        $isOnline = $isWindows
            ? (strpos($pingResult, 'Received = 4') !== false)  // Windows sprawdza ilość odebranych pakietów
            : (strpos($pingResult, '0% packet loss') !== false); // Linux/macOS sprawdza "0% packet loss"

        $status = $isOnline ? 'Online' : 'Offline';

        // 🔹 Aktualizacja statusu w bazie danych
        $device->setStatus($status);
        $this->entityManager->persist($device);
        $this->entityManager->flush();

        return new JsonResponse([
            'deviceId' => $deviceId,
            'status' => $status,
            'pingResult' => $pingResult
        ]);
    }

    

    
}
