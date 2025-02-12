<?php
// src/Repository/SnmpVersionRepository.php

namespace App\Repository;

use App\Entity\SnmpVersion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SnmpVersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SnmpVersion::class);
    }

    // Dodaj tutaj dodatkowe metody, jeśli potrzebujesz
}
