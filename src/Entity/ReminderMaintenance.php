<?php

namespace App\Entity;

use App\Repository\ReminderMaintenanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReminderMaintenanceRepository::class)]
class ReminderMaintenance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $expectedDate = null;

    #[ORM\Column(nullable: true)]
    private ?int $intervalPersonnalValue = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $intervalPersonnalUnit = null;

    #[ORM\ManyToOne(inversedBy: 'reminderMaintenances')]
    private ?Equipment $equipment = null;

    #[ORM\ManyToOne(inversedBy: 'reminderMaintenances')]
    private ?TypeMaintenance $typeMaintenance = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExpectedDate(): ?\DateTime
    {
        return $this->expectedDate;
    }

    public function setExpectedDate(\DateTime $expectedDate): static
    {
        $this->expectedDate = $expectedDate;

        return $this;
    }

    public function getIntervalPersonnalValue(): ?int
    {
        return $this->intervalPersonnalValue;
    }

    public function setIntervalPersonnalValue(?int $intervalPersonnalValue): static
    {
        $this->intervalPersonnalValue = $intervalPersonnalValue;

        return $this;
    }

    public function getIntervalPersonnalUnit(): ?string
    {
        return $this->intervalPersonnalUnit;
    }

    public function setIntervalPersonnalUnit(?string $intervalPersonnalUnit): static
    {
        $this->intervalPersonnalUnit = $intervalPersonnalUnit;

        return $this;
    }

    public function getEquipment(): ?Equipment
    {
        return $this->equipment;
    }

    public function setEquipment(?Equipment $equipment): static
    {
        $this->equipment = $equipment;

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
