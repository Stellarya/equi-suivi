<?php

namespace App\Entity;

use App\Repository\EquipmentMaintenanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentMaintenanceRepository::class)]
class EquipmentMaintenance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $maintenanceDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(inversedBy: 'equipmentMaintenances')]
    private ?TypeMaintenance $typeMaintenance = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMaintenanceDate(): ?\DateTime
    {
        return $this->maintenanceDate;
    }

    public function setMaintenanceDate(\DateTime $maintenanceDate): static
    {
        $this->maintenanceDate = $maintenanceDate;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getTypeMaintenance(): ?TypeMaintenance
    {
        return $this->typeMaintenance;
    }

    public function setTypeMaintenance(?TypeMaintenance $typeMaintenance): static
    {
        $this->typeMaintenance = $typeMaintenance;

        return $this;
    }
}
