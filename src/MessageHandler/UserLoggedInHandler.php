<?php

namespace App\MessageHandler;

use App\Message\UserLoggedInMessage;
use App\Entity\LoginLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UserLoggedInHandler
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(UserLoggedInMessage $message)
    {
        $log = new LoginLog($message->getUsername());
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
