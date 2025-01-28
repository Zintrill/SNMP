<?php
// src/DataFixtures/StatusFixtures.php

namespace App\DataFixtures;

use App\Entity\Status;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public const STATUS_WAITING = 'Waiting';
    public const STATUS_ONLINE = 'Online';
    public const STATUS_OFFLINE = 'Offline';

    public function load(ObjectManager $manager): void
    {
        $statuses = [
            self::STATUS_WAITING,
            self::STATUS_ONLINE,
            self::STATUS_OFFLINE,
        ];

        foreach ($statuses as $statusName) {
            // Sprawdzenie, czy status już istnieje, aby uniknąć duplikatów
            $existingStatus = $manager->getRepository(Status::class)->findOneBy(['name' => $statusName]);
            if (!$existingStatus) {
                $status = new Status();
                $status->setName($statusName);
                $manager->persist($status);
            }
        }

        $manager->flush();
    }
}
