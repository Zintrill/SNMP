<?php

namespace App\Entity;

use App\Repository\UserDeviceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserDeviceRepository::class)]
class UserDevice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $user_id = null;

    #[ORM\Column]
    private ?int $device_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getDeviceId(): ?int
    {
        return $this->device_id;
    }

    public function setDeviceId(int $device_id): static
    {
        $this->device_id = $device_id;

        return $this;
    }
}
