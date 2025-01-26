<?php
// src/Entity/DeviceType.php

namespace App\Entity;

use App\Repository\DeviceTypeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeviceTypeRepository::class)]
#[ORM\Table(name: "device_type")]
class DeviceType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "name", type: "string", length: 100, unique: true)]
    private string $name;

    // Gettery i settery...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
