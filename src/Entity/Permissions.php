<?php

namespace App\Entity;

use App\Repository\PermissionsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PermissionsRepository::class)]
class Permissions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $permission_id = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $role = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPermissionId(): ?int
    {
        return $this->permission_id;
    }

    public function setPermissionId(int $permission_id): static
    {
        $this->permission_id = $permission_id;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;

        return $this;
    }
}
