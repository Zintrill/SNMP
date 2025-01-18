<?php

namespace App\Entity;

use App\Repository\DeviceStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeviceStatusRepository::class)]
class DeviceStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $device_id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mac_address = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getDeviceId(): ?int
    {
        return $this->device_id;
    }

    public function setDeviceId(?int $device_id): static
    {
        $this->device_id = $device_id;

        return $this;
    }

    public function getMacAddress(): ?string
    {
        return $this->mac_address;
    }

    public function setMacAddress(?string $mac_address): static
    {
        $this->mac_address = $mac_address;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
