<?php
// src/Entity/Device.php

namespace App\Entity;

use App\Repository\DeviceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeviceRepository::class)]
#[ORM\Table(name: "device")]
class Device
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "device_name", type: "string", length: 255)]
    private string $deviceName;

    #[ORM\ManyToOne(targetEntity: DeviceType::class)]
    #[ORM\JoinColumn(name: "device_type_id", referencedColumnName: "id", nullable: false)]
    private DeviceType $deviceType;

    #[ORM\Column(name: "address_ip", type: "string", length: 45)]
    private string $addressIp;

    #[ORM\ManyToOne(targetEntity: SnmpVersion::class)]
    #[ORM\JoinColumn(name: "snmp_version_id", referencedColumnName: "id", nullable: false)]
    private SnmpVersion $snmpVersion;

    #[ORM\Column(name: "user_name", type: "string", length: 100, nullable: true)]
    private ?string $userName = null;

    #[ORM\Column(name: "password", type: "string", length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(name: "description", type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: "status", type: "string", length: 20)]
    private string $status;


    #[ORM\Column(name: "mac_address", type: "string", length: 17, unique: true, nullable: true)]
    private ?string $macAddress = null;

    // Gettery i settery...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeviceName(): string
    {
        return $this->deviceName;
    }

    public function setDeviceName(string $deviceName): self
    {
        $this->deviceName = $deviceName;

        return $this;
    }

    public function getDeviceType(): DeviceType
    {
        return $this->deviceType;
    }

    public function setDeviceType(DeviceType $deviceType): self
    {
        $this->deviceType = $deviceType;

        return $this;
    }

    public function getAddressIp(): string
    {
        return $this->addressIp;
    }

    public function setAddressIp(string $addressIp): self
    {
        $this->addressIp = $addressIp;

        return $this;
    }

    public function getSnmpVersion(): SnmpVersion
    {
        return $this->snmpVersion;
    }

    public function setSnmpVersion(SnmpVersion $snmpVersion): self
    {
        $this->snmpVersion = $snmpVersion;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(?string $userName): self
    {
        $this->userName = $userName;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }


    public function getMacAddress(): ?string
    {
        return $this->macAddress;
    }

    public function setMacAddress(?string $macAddress): self
    {
        $this->macAddress = $macAddress;

        return $this;
    }
}
