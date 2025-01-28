<?php

namespace App\Controller;

use App\Entity\Device;
use App\Entity\DeviceType;
use App\Entity\SnmpVersion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


#[Route('/configuration')]
class ConfigurationController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_configuration_index', methods: ['GET'])]
    public function index(): Response
    {
        // Upewnij się, że użytkownik ma przynajmniej rolę ROLE_USER
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        // Pobierz wszystkie typy urządzeń, wersje SNMP i urządzenia
        $deviceTypes = $this->entityManager->getRepository(DeviceType::class)->findAll();
        $snmpVersions = $this->entityManager->getRepository(SnmpVersion::class)->findAll();
        $devices = $this->entityManager->getRepository(Device::class)->findAll();

        // Renderuj szablon z danymi
        return $this->render('configuration/index.html.twig', [
            'username' => $user ? $user->getUserIdentifier() : 'Guest',
            'deviceTypes' => $deviceTypes,
            'snmpVersions' => $snmpVersions,
            'devices' => $devices,
        ]);
    }

    #[Route('/getDevices', name: 'app_configuration_get_devices', methods: ['GET'])]
    public function getDevices(): JsonResponse
    {
        // Upewnij się, że użytkownik ma przynajmniej rolę ROLE_USER
        $this->denyAccessUnlessGranted('ROLE_USER');

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


    #[Route('/getDeviceTypes', name: 'app_configuration_get_device_types', methods: ['GET'])]
    public function getDeviceTypes(): JsonResponse
    {
        // Upewnij się, że użytkownik ma przynajmniej rolę ROLE_USER
        $this->denyAccessUnlessGranted('ROLE_USER');

        $deviceTypes = $this->entityManager->getRepository(DeviceType::class)->findAll();
        $data = [];

        foreach ($deviceTypes as $type) {
            $data[] = [
                'type_id' => $type->getId(),
                'type' => $type->getName(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/getSnmpVersions', name: 'app_configuration_get_snmp_versions', methods: ['GET'])]
    public function getSnmpVersions(): JsonResponse
    {
        // Upewnij się, że użytkownik ma przynajmniej rolę ROLE_USER
        $this->denyAccessUnlessGranted('ROLE_USER');

        $snmpVersions = $this->entityManager->getRepository(SnmpVersion::class)->findAll();
        $data = [];

        foreach ($snmpVersions as $version) {
            $data[] = [
                'snmp_version_id' => $version->getId(),
                'snmp' => $version->getVersion(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/addDevice', name: 'app_configuration_add_device', methods: ['POST'])]
    public function addDevice(Request $request): JsonResponse
    {
        // Sprawdzenie, czy użytkownik ma rolę ROLE_ADMIN lub ROLE_OPERATOR
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_OPERATOR')) {
            throw $this->createAccessDeniedException('Access denied. You do not have the required roles.');
        }

        $deviceName = trim($request->request->get('deviceName'));
        $deviceTypeId = $request->request->get('deviceType');
        $addressIp = trim($request->request->get('deviceAddress'));
        $snmpVersionId = $request->request->get('snmpVersion');
        $userName = trim($request->request->get('userName'));
        $password = trim($request->request->get('password'));
        $description = trim($request->request->get('description'));

        $errors = [];

        // Walidacja pól
        if (empty($deviceName)) {
            $errors['deviceName'] = 'Device name is required.';
        } else {
            $existingDevice = $this->entityManager->getRepository(Device::class)->findOneBy(['deviceName' => $deviceName]);
            if ($existingDevice) {
                $errors['deviceName'] = 'Device name is already taken.';
            }
        }

        if (empty($deviceTypeId)) {
            $errors['deviceType'] = 'Device type is required.';
        } else {
            $deviceType = $this->entityManager->getRepository(DeviceType::class)->find($deviceTypeId);
            if (!$deviceType) {
                $errors['deviceType'] = 'Invalid device type selected.';
            }
        }

        if (empty($addressIp)) {
            $errors['deviceAddress'] = 'Address IP is required.';
        } else {
            $existingIp = $this->entityManager->getRepository(Device::class)->findOneBy(['addressIp' => $addressIp]);
            if ($existingIp) {
                $errors['deviceAddress'] = 'Address IP is already in use.';
            }
        }

        if (empty($snmpVersionId)) {
            $errors['snmpVersion'] = 'SNMP version is required.';
        } else {
            $snmpVersion = $this->entityManager->getRepository(SnmpVersion::class)->find($snmpVersionId);
            if (!$snmpVersion) {
                $errors['snmpVersion'] = 'Invalid SNMP version selected.';
            }
        }

        // Dodatkowa walidacja, np. dla ICMP
        if (!empty($snmpVersionId) && isset($snmpVersion) && $snmpVersion->getId() === 4) {
            // Jeśli SNMP version to ICMP, userName i password mogą być puste
        } else {
            if (empty($userName)) {
                $errors['userName'] = 'Username is required for selected SNMP version.';
            }
            if (empty($password)) {
                $errors['password'] = 'Password is required for selected SNMP version.';
            }
        }

        // Jeśli są błędy, zwróć je w formacie JSON
        if (!empty($errors)) {
            return new JsonResponse(['status' => 'error', 'errors' => $errors], 400);
        }

        // Jeśli nie ma błędów, kontynuuj tworzenie urządzenia
        // Ustawienie domyślnej wartości dla status
        $status = 'waiting'; // Dostosuj do swoich potrzeb

        if (isset($snmpVersion) && $snmpVersion->getId() === 4) { // ICMP
            $userName = null;
            $password = null;
        }

        $device = new Device();
        $device->setDeviceName($deviceName);
        $device->setDeviceType($deviceType);
        $device->setAddressIp($addressIp);
        $device->setSnmpVersion($snmpVersion);
        $device->setUserName($userName);
        $device->setPassword($password);
        $device->setDescription($description);
        $device->setStatus($status);

        $this->entityManager->persist($device);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'success', 'message' => 'Device added successfully.']);
    }

    #[Route('/updateDevice', name: 'app_configuration_update_device', methods: ['POST'])]
    public function updateDevice(Request $request): JsonResponse
    {
        // Sprawdzenie, czy użytkownik ma rolę ROLE_ADMIN lub ROLE_OPERATOR
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_OPERATOR')) {
            throw $this->createAccessDeniedException('Access denied. You do not have the required roles.');
        }

        $deviceId = $request->request->get('deviceId');
        $deviceName = trim($request->request->get('deviceName'));
        $deviceTypeId = $request->request->get('deviceType');
        $addressIp = trim($request->request->get('deviceAddress'));
        $snmpVersionId = $request->request->get('snmpVersion');
        $userName = trim($request->request->get('userName'));
        $password = trim($request->request->get('password'));
        $description = trim($request->request->get('description'));

        // Sprawdzenie, czy urządzenie istnieje
        $device = $this->entityManager->getRepository(Device::class)->find($deviceId);
        if (!$device) {
            return new JsonResponse(['status' => 'error', 'message' => 'Device not found.'], 404);
        }

        // Walidacja danych
        if (empty($deviceName) || empty($deviceTypeId) || empty($addressIp) || empty($snmpVersionId)) {
            return new JsonResponse(['status' => 'error', 'message' => 'All required fields must be filled.'], 400);
        }

        // Sprawdzenie unikalności nazwy urządzenia, jeśli została zmieniona
        if (strtolower($device->getDeviceName()) !== strtolower($deviceName)) {
            $existingDevice = $this->entityManager->getRepository(Device::class)->findOneBy(['deviceName' => $deviceName]);
            if ($existingDevice) {
                return new JsonResponse(['status' => 'error', 'message' => 'Device name is already taken.'], 400);
            }
        }

        // Sprawdzenie unikalności adresu IP, jeśli został zmieniony
        if ($device->getAddressIp() !== $addressIp) {
            $existingIp = $this->entityManager->getRepository(Device::class)->findOneBy(['addressIp' => $addressIp]);
            if ($existingIp) {
                return new JsonResponse(['status' => 'error', 'message' => 'Address IP is already taken.'], 400);
            }
        }

        // Pobranie obiektów DeviceType i SnmpVersion
        $deviceType = $this->entityManager->getRepository(DeviceType::class)->find($deviceTypeId);
        $snmpVersion = $this->entityManager->getRepository(SnmpVersion::class)->find($snmpVersionId);

        if (!$deviceType || !$snmpVersion) {
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid Device Type or SNMP Version.'], 400);
        }

        // Jeśli wersja SNMP to ICMP (ID=4), ustaw username i password na null
        if ($snmpVersion->getId() === 4) { // Upewnij się, że ID 4 to ICMP
            $userName = null;
            $password = null;
        }

        // Aktualizacja danych urządzenia
        $device->setDeviceName($deviceName);
        $device->setDeviceType($deviceType);
        $device->setAddressIp($addressIp);
        $device->setSnmpVersion($snmpVersion);
        $device->setUserName($userName);
        $device->setPassword($password);
        $device->setDescription($description);

        $this->entityManager->flush();

        return new JsonResponse(['status' => 'success', 'message' => 'Device updated successfully.']);
    }

    #[Route('/deleteDevice', name: 'app_configuration_delete_device', methods: ['POST'])]
    public function deleteDevice(Request $request): JsonResponse
    {
        // Sprawdzenie, czy użytkownik ma rolę ROLE_ADMIN lub ROLE_OPERATOR
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_OPERATOR')) {
            throw $this->createAccessDeniedException('Access denied. You do not have the required roles.');
        }

        $deviceId = $request->request->get('deviceId');

        // Sprawdzenie, czy urządzenie istnieje
        $device = $this->entityManager->getRepository(Device::class)->find($deviceId);
        if (!$device) {
            return new JsonResponse(['status' => 'error', 'message' => 'Device not found.'], 404);
        }

        // Usunięcie urządzenia
        $this->entityManager->remove($device);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'success', 'message' => 'Device deleted successfully.']);
    }

    #[Route('/checkDeviceName', name: 'app_configuration_check_device_name', methods: ['GET'])]
    public function checkDeviceName(Request $request): JsonResponse
    {
        // Upewnij się, że użytkownik ma przynajmniej rolę ROLE_USER
        $this->denyAccessUnlessGranted('ROLE_USER');

        $deviceName = trim($request->query->get('deviceName'));

        $existingDevice = $this->entityManager->getRepository(Device::class)->findOneBy(['deviceName' => $deviceName]);

        return new JsonResponse(['isTaken' => $existingDevice ? true : false]);
    }

    #[Route('/checkAddressIp', name: 'app_configuration_check_address_ip', methods: ['GET'])]
    public function checkAddressIp(Request $request): JsonResponse
    {
        // Upewnij się, że użytkownik ma przynajmniej rolę ROLE_USER
        $this->denyAccessUnlessGranted('ROLE_USER');

        $addressIp = trim($request->query->get('addressIp'));

        $existingIp = $this->entityManager->getRepository(Device::class)->findOneBy(['addressIp' => $addressIp]);

        return new JsonResponse(['isTaken' => $existingIp ? true : false]);
    }

    #[Route('/getDeviceById', name: 'app_configuration_get_device_by_id', methods: ['GET'])]
    public function getDeviceById(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Sprawdzenie, czy użytkownik ma rolę ROLE_ADMIN lub ROLE_OPERATOR
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_OPERATOR')) {
            throw $this->createAccessDeniedException('Access denied. You do not have the required roles.');
        }

        $deviceId = $request->query->get('id');

        if (!$deviceId) {
            return new JsonResponse(['status' => 'error', 'message' => 'No device ID provided.'], 400);
        }

        $device = $entityManager->getRepository(Device::class)->find($deviceId);

        if (!$device) {
            return new JsonResponse(['status' => 'error', 'message' => 'Device not found.'], 404);
        }

        // Przygotowanie danych urządzenia
        $deviceData = [
            'id' => $device->getId(),
            'device_name' => $device->getDeviceName(),
            'type_id' => $device->getDeviceType()->getId(),
            'address_ip' => $device->getAddressIp(),
            'snmp_version_id' => $device->getSnmpVersion()->getId(),
            'username' => $device->getUserName(),
            'password' => $device->getPassword(),
            'description' => $device->getDescription(),
            'mac_address' => $device->getMacAddress(),
            'status' => $device->getStatus(),
        ];

        return new JsonResponse(['status' => 'success', 'device' => $deviceData]);
    }
}
