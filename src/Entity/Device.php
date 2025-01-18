<?php

namespace App\Entity;

use App\Repository\DeviceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeviceRepository::class)]
class Device
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $device_name = null;

    #[ORM\Column]
    private ?int $type_id = null;

    #[ORM\Column(length: 255)]
    private ?string $address_ip = null;

    #[ORM\Column]
    private ?int $snmp_version_id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getDeviceName(): ?string
    {
        return $this->device_name;
    }

    public function setDeviceName(string $device_name): static
    {
        $this->device_name = $device_name;

        return $this;
    }

    public function getTypeId(): ?int
    {
        return $this->type_id;
    }

    public function setTypeId(int $type_id): static
    {
        $this->type_id = $type_id;

        return $this;
    }

    public function getAddressIp(): ?string
    {
        return $this->address_ip;
    }

    public function setAddressIp(string $address_ip): static
    {
        $this->address_ip = $address_ip;

        return $this;
    }

    public function getSnmpVersionId(): ?int
    {
        return $this->snmp_version_id;
    }

    public function setSnmpVersionId(int $snmp_version_id): static
    {
        $this->snmp_version_id = $snmp_version_id;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
