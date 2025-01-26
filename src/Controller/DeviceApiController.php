<?php
// src/Controller/DeviceApiController.php

namespace App\Controller;

use App\Entity\Device;
use App\Entity\DeviceType;
use App\Entity\SnmpVersion;
use App\Repository\DeviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
#[Route('/')]
class DeviceApiController extends AbstractController
{
    private DeviceRepository $deviceRepository;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    // Konstruktor z wstrzyknięciem zależności
    public function __construct(DeviceRepository $deviceRepository, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->deviceRepository = $deviceRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Pobiera wszystkie urządzenia.
     * URL: GET /getDevices
     */
    #[Route('getDevices', name: 'get_devices', methods: ['GET'])]
    public function getDevices(): JsonResponse
    {
        $devices = $this->deviceRepository->findAll();

        $devicesArray = [];
        foreach ($devices as $device) {
            $devicesArray[] = [
                'id'                => $device->getId(),
                'device_name'       => $device->getDeviceName(),
                'device_type_id'    => $device->getDeviceType()->getId(),
                'device_type_name'  => $device->getDeviceType()->getName(),
                'address_ip'        => $device->getAddressIp(),
                'mac_address'       => $device->getMacAddress(),
                'status'            => $device->getStatus(),
                'snmp_version_id'   => $device->getSnmpVersion()->getId(),
                'snmp_version_name' => $device->getSnmpVersion()->getVersion(),
                'username'          => $device->getUsername(),
                'password'          => $device->getPassword(),
                'description'       => $device->getDescription(),
                'last_polled'       => $device->getLastPolled() ? $device->getLastPolled()->format('Y-m-d H:i:s') : null,
            ];
        }

        return $this->json($devicesArray);
    }

    /**
     * Dodaje nowe urządzenie.
     * URL: POST /addDevice
     */
    #[Route('addDevice', name: 'add_device', methods: ['POST'])]
    public function addDevice(Request $request): JsonResponse
    {
        // Pobieranie danych z żądania (FormData)
        $deviceName    = $request->request->get('deviceName');
        $deviceTypeId  = $request->request->get('deviceType'); // Expecting deviceType as ID
        $deviceAddr    = $request->request->get('deviceAddress');
        $snmpVersionId = $request->request->get('snmpVersion'); // Expecting snmpVersion as ID
        $userName      = $request->request->get('userName', '');
        $password      = $request->request->get('password', '');
        $description   = $request->request->get('description', '');
        $macAddress    = $request->request->get('macAddress', null); // Pobieranie MAC Address

        // Walidacja danych
        if (empty($deviceName) || empty($deviceTypeId) || empty($deviceAddr) || empty($snmpVersionId)) {
            return $this->json(['status' => 'error', 'message' => 'Wszystkie pola są wymagane.'], 400);
        }

        // Sprawdzenie unikalności nazwy urządzenia
        if ($this->deviceRepository->isDeviceNameTaken($deviceName)) {
            return $this->json(['status' => 'error', 'message' => 'Nazwa urządzenia jest już zajęta.'], 400);
        }

        // Sprawdzenie unikalności adresu IP
        if ($this->deviceRepository->isAddressIpTaken($deviceAddr)) {
            return $this->json(['status' => 'error', 'message' => 'Adres IP jest już zajęty.'], 400);
        }

        // Sprawdzenie unikalności MAC Address, jeśli jest dostarczony
        if ($macAddress && $this->deviceRepository->isMacAddressTaken($macAddress)) {
            return $this->json(['status' => 'error', 'message' => 'MAC Address jest już zajęty.'], 400);
        }

        // Sprawdzenie poprawności formatu adresu IP
        if (!$this->isValidIpAddress($deviceAddr)) {
            return $this->json(['status' => 'error', 'message' => 'Niepoprawny format adresu IP.'], 400);
        }

        // Sprawdzenie poprawności formatu MAC Address, jeśli jest dostarczony
        if ($macAddress && !$this->isValidMacAddress($macAddress)) {
            return $this->json(['status' => 'error', 'message' => 'Niepoprawny format MAC Address.'], 400);
        }

        // Pobranie obiektu DeviceType
        $deviceType = $this->entityManager->getRepository(DeviceType::class)->find($deviceTypeId);
        if (!$deviceType) {
            return $this->json(['status' => 'error', 'message' => 'Wybrany typ urządzenia nie istnieje.'], 400);
        }

        // Pobranie obiektu SnmpVersion
        $snmpVersion = $this->entityManager->getRepository(SnmpVersion::class)->find($snmpVersionId);
        if (!$snmpVersion) {
            return $this->json(['status' => 'error', 'message' => 'Wybrana wersja SNMP nie istnieje.'], 400);
        }

        // Tworzenie nowego obiektu Device
        $device = new Device();
        $device->setDeviceName($deviceName);
        $device->setDeviceType($deviceType);
        $device->setAddressIp($deviceAddr);
        $device->setSnmpVersion($snmpVersion);
        $device->setUserName($userName);
        $device->setPassword($password);
        $device->setDescription($description);
        $device->setStatus('offline'); // Domyślny status
        $device->setLastPolled(new \DateTime()); // Ustawienie aktualnego czasu
        if ($macAddress) {
            $device->setMacAddress($macAddress);
        }

        try {
            $this->entityManager->persist($device);
            $this->entityManager->flush();

            return $this->json(['status' => 'success']);
        } catch (\Exception $e) {
            // Logowanie błędu
            $this->logger->error($e->getMessage());

            return $this->json(['status' => 'error', 'message' => 'Wystąpił błąd podczas dodawania urządzenia.'], 500);
        }
    }

    /**
     * Aktualizuje istniejące urządzenie.
     * URL: POST /updateDevice
     */
    #[Route('updateDevice', name: 'update_device', methods: ['POST'])]
    public function updateDevice(Request $request): JsonResponse
    {
        $deviceId      = $request->request->get('deviceId');
        $deviceName    = $request->request->get('deviceName');
        $deviceTypeId  = $request->request->get('deviceType'); // Expecting deviceType as ID
        $deviceAddr    = $request->request->get('deviceAddress');
        $snmpVersionId = $request->request->get('snmpVersion'); // Expecting snmpVersion as ID
        $userName      = $request->request->get('userName', '');
        $password      = $request->request->get('password', '');
        $description   = $request->request->get('description', '');
        $macAddress    = $request->request->get('macAddress', null); // Pobieranie MAC Address

        // Sprawdzenie, czy urządzenie istnieje
        $device = $this->deviceRepository->find($deviceId);
        if (!$device) {
            return $this->json(['status' => 'error', 'message' => 'Urządzenie nie zostało znalezione.'], 404);
        }

        // Sprawdzenie unikalności nazwy, jeśli została zmieniona
        if (strtolower($device->getDeviceName()) !== strtolower($deviceName)) {
            if ($this->deviceRepository->isDeviceNameTaken($deviceName)) {
                return $this->json(['status' => 'error', 'message' => 'Nazwa urządzenia jest już zajęta.'], 400);
            }
        }

        // Sprawdzenie unikalności IP, jeśli zostało zmienione
        if ($device->getAddressIp() !== $deviceAddr) {
            if ($this->deviceRepository->isAddressIpTaken($deviceAddr)) {
                return $this->json(['status' => 'error', 'message' => 'Adres IP jest już zajęty.'], 400);
            }
        }

        // Sprawdzenie unikalności MAC Address, jeśli zostało zmienione
        if ($macAddress && $device->getMacAddress() !== $macAddress) {
            if ($this->deviceRepository->isMacAddressTaken($macAddress)) {
                return $this->json(['status' => 'error', 'message' => 'MAC Address jest już zajęty.'], 400);
            }
        }

        // Sprawdzenie poprawności formatu adresu IP
        if (!$this->isValidIpAddress($deviceAddr)) {
            return $this->json(['status' => 'error', 'message' => 'Niepoprawny format adresu IP.'], 400);
        }

        // Sprawdzenie poprawności formatu MAC Address, jeśli jest dostarczony
        if ($macAddress && !$this->isValidMacAddress($macAddress)) {
            return $this->json(['status' => 'error', 'message' => 'Niepoprawny format MAC Address.'], 400);
        }

        // Pobranie obiektu DeviceType
        $deviceType = $this->entityManager->getRepository(DeviceType::class)->find($deviceTypeId);
        if (!$deviceType) {
            return $this->json(['status' => 'error', 'message' => 'Wybrany typ urządzenia nie istnieje.'], 400);
        }

        // Pobranie obiektu SnmpVersion
        $snmpVersion = $this->entityManager->getRepository(SnmpVersion::class)->find($snmpVersionId);
        if (!$snmpVersion) {
            return $this->json(['status' => 'error', 'message' => 'Wybrana wersja SNMP nie istnieje.'], 400);
        }

        // Aktualizacja danych urządzenia
        $device->setDeviceName($deviceName);
        $device->setDeviceType($deviceType);
        $device->setAddressIp($deviceAddr);
        $device->setSnmpVersion($snmpVersion);
        $device->setUserName($userName);
        $device->setPassword($password);
        $device->setDescription($description);
        $device->setLastPolled(new \DateTime());
        if ($macAddress) {
            $device->setMacAddress($macAddress);
        } else {
            $device->setMacAddress(null);
        }

        try {
            $this->entityManager->flush();

            return $this->json(['status' => 'success', 'message' => 'Urządzenie zostało zaktualizowane pomyślnie.']);
        } catch (\Exception $e) {
            // Logowanie błędu
            $this->logger->error($e->getMessage());

            return $this->json(['status' => 'error', 'message' => 'Wystąpił błąd podczas aktualizacji urządzenia.'], 500);
        }
    }

    /**
     * Usuwa urządzenie.
     * URL: POST /deleteDevice
     */
    #[Route('deleteDevice', name: 'delete_device', methods: ['POST'])]
    public function deleteDevice(Request $request): JsonResponse
    {
        $deviceId = $request->request->get('deviceId');

        // Sprawdzenie, czy urządzenie istnieje
        $device = $this->deviceRepository->find($deviceId);
        if (!$device) {
            return $this->json(['status' => 'error', 'message' => 'Urządzenie nie zostało znalezione.'], 404);
        }

        try {
            // Usuwanie urządzenia
            $this->entityManager->remove($device);
            $this->entityManager->flush();

            return $this->json(['status' => 'success', 'message' => 'Urządzenie zostało usunięte pomyślnie.']);
        } catch (\Exception $e) {
            // Logowanie błędu
            $this->logger->error($e->getMessage());

            return $this->json(['status' => 'error', 'message' => 'Wystąpił błąd podczas usuwania urządzenia.'], 500);
        }
    }

    /**
     * Sprawdza, czy nazwa urządzenia jest już zajęta.
     * URL: GET /checkDeviceName?deviceName=XYZ
     */
    #[Route('checkDeviceName', name: 'check_device_name', methods: ['GET'])]
    public function checkDeviceName(Request $request): JsonResponse
    {
        $deviceName = $request->query->get('deviceName');

        if (!$deviceName) {
            return $this->json(['isTaken' => false]);
        }

        $isTaken = $this->deviceRepository->isDeviceNameTaken($deviceName);

        return $this->json(['isTaken' => $isTaken]);
    }

    /**
     * Sprawdza, czy adres IP jest już zajęty.
     * URL: GET /checkAddressIp?addressIp=192.168.1.1
     */
    #[Route('checkAddressIp', name: 'check_address_ip', methods: ['GET'])]
    public function checkAddressIp(Request $request): JsonResponse
    {
        $addressIp = $request->query->get('addressIp');

        if (!$addressIp) {
            return $this->json(['isTaken' => false]);
        }

        $isTaken = $this->deviceRepository->isAddressIpTaken($addressIp);

        return $this->json(['isTaken' => $isTaken]);
    }

    /**
     * Sprawdza, czy MAC Address jest już zajęty.
     * URL: GET /checkMacAddress?macAddress=XX:XX:XX:XX:XX:XX
     */
    #[Route('checkMacAddress', name: 'check_mac_address', methods: ['GET'])]
    public function checkMacAddress(Request $request): JsonResponse
    {
        $macAddress = $request->query->get('macAddress');

        if (!$macAddress) {
            return $this->json(['isTaken' => false]);
        }

        // Upewnij się, że format MAC Address jest poprawny
        if (!$this->isValidMacAddress($macAddress)) {
            return $this->json(['isTaken' => false, 'invalidFormat' => true]);
        }

        $isTaken = $this->deviceRepository->isMacAddressTaken($macAddress);

        return $this->json(['isTaken' => $isTaken]);
    }

    /**
     * Pobiera urządzenie po ID.
     * URL: GET /getDeviceById?id=1
     */
    #[Route('getDeviceById', name: 'get_device_by_id', methods: ['GET'])]
    public function getDeviceById(Request $request): JsonResponse
    {
        $deviceId = $request->query->get('id');

        if (!$deviceId) {
            return $this->json(['status' => 'error', 'message' => 'ID urządzenia jest wymagane.'], 400);
        }

        $device = $this->deviceRepository->find($deviceId);

        if (!$device) {
            return $this->json(['status' => 'error', 'message' => 'Urządzenie nie zostało znalezione.'], 404);
        }

        $deviceData = [
            'id'                => $device->getId(),
            'device_name'       => $device->getDeviceName(),
            'device_type_id'    => $device->getDeviceType()->getId(),
            'device_type_name'  => $device->getDeviceType()->getName(),
            'address_ip'        => $device->getAddressIp(),
            'mac_address'       => $device->getMacAddress(),
            'status'            => $device->getStatus(),
            'snmp_version_id'   => $device->getSnmpVersion()->getId(),
            'snmp_version_name' => $device->getSnmpVersion()->getVersion(),
            'username'          => $device->getUsername(),
            'password'          => $device->getPassword(),
            'description'       => $device->getDescription(),
            'last_polled'       => $device->getLastPolled() ? $device->getLastPolled()->format('Y-m-d H:i:s') : null,
        ];

        return $this->json($deviceData);
    }

    /**
     * Pobiera listę typów urządzeń.
     * URL: GET /getDeviceTypes
     */
    #[Route('getDeviceTypes', name: 'get_device_types', methods: ['GET'])]
    public function getDeviceTypes(): JsonResponse
    {
        $types = $this->deviceRepository->getDeviceTypes();

        return $this->json($types);
    }

    /**
     * Pobiera listę wersji SNMP.
     * URL: GET /getSnmpVersions
     */
    #[Route('getSnmpVersions', name: 'get_snmp_versions', methods: ['GET'])]
    public function getSnmpVersions(): JsonResponse
    {
        $versions = $this->deviceRepository->getSnmpVersions();

        return $this->json($versions);
    }

    /**
     * Metoda pomocnicza do walidacji adresu IP.
     */
    private function isValidIpAddress(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Metoda pomocnicza do walidacji MAC Address.
     */
    private function isValidMacAddress(string $mac): bool
    {
        return preg_match('/^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/', $mac) === 1;
    }

    // Możesz dodać inne metody pomocnicze tutaj...
}
