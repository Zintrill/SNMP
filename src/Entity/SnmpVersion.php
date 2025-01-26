<?php
// src/Entity/SnmpVersion.php

namespace App\Entity;

use App\Repository\SnmpVersionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SnmpVersionRepository::class)]
#[ORM\Table(name: "snmp_version")]
class SnmpVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "version", type: "string", length: 50, unique: true)]
    private string $version;

    // Gettery i settery...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }
}
