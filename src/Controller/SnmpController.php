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

    // 🔹 Szybsze i dokładniejsze pingowanie (1 pakiet, timeout 1 sekunda)
    $pingCommand = $isWindows
        ? sprintf('ping -n 1 -w 1000 %s 2>&1', escapeshellarg($ip))  // Windows: -n 1, -w 1000 ms
        : sprintf('ping -c 1 -W 1 %s 2>&1', escapeshellarg($ip));   // Linux/macOS: -c 1, -W 1 sek.

    $pingResult = shell_exec($pingCommand);

    if (!$pingResult) {
        return new JsonResponse(['error' => 'Nie można wykonać pingowania. Może być blokowane przez system lub firewall.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    // 🔹 Dokładniejsza analiza wyniku pingowania
    $isOnline = false;
    if ($isWindows) {
        // Windows: Sprawdzamy czy jest "Received = 1"
        $isOnline = preg_match('/Received = 1/', $pingResult);
    } else {
        // Linux/macOS: Sprawdzamy czy jest "1 received" i brak "100% packet loss"
        $isOnline = preg_match('/1 received/', $pingResult) && !preg_match('/100% packet loss/', $pingResult);
    }

    $status = $isOnline ? 'Online' : 'Offline';

    // 🔹 Aktualizacja statusu w bazie danych
    $device->setStatus($status);
    $this->entityManager->persist($device);
    $this->entityManager->flush();

    return new JsonResponse([
        'deviceId' => $deviceId,
        'status' => $status,
        'pingResult' => nl2br($pingResult) // 🔹 Debugowanie w UI
    ]);
}

}
