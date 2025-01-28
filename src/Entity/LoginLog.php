<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class LoginLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $username;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $loggedAt;

    public function __construct(string $username)
    {
        $this->username = $username;
        $this->loggedAt = new \DateTime();
    }
}
