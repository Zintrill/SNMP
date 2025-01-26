<?php
// src/Repository/DeviceRepository.php

namespace App\Repository;

use App\Entity\Device;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DeviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Device::class);
    }

    /**
     * Sprawdza, czy nazwa urządzenia jest już zajęta.
     */
    public function isDeviceNameTaken(string $deviceName): bool
    {
        return (bool) $this->createQueryBuilder('d')
            ->select('count(d.id)')
            ->where('LOWER(d.deviceName) = LOWER(:name)')
            ->setParameter('name', $deviceName)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Sprawdza, czy adres IP jest już zajęty.
     */
    public function isAddressIpTaken(string $addressIp): bool
    {
        return (bool) $this->createQueryBuilder('d')
            ->select('count(d.id)')
            ->where('d.addressIp = :ip')
            ->setParameter('ip', $addressIp)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Sprawdza, czy MAC Address jest już zajęty.
     */
    public function isMacAddressTaken(string $macAddress): bool
    {
        return (bool) $this->createQueryBuilder('d')
            ->select('count(d.id)')
            ->where('d.macAddress = :mac')
            ->setParameter('mac', $macAddress)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
