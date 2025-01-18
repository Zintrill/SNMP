<?php

namespace App\Entity;

use App\Repository\SnmpVersionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SnmpVersionRepository::class)]
class SnmpVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $snmp_version_id = null;

    #[ORM\Column(length: 255)]
    private ?string $snmp = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSnmp(): ?string
    {
        return $this->snmp;
    }

    public function setSnmp(string $snmp): static
    {
        $this->snmp = $snmp;

        return $this;
    }
}
