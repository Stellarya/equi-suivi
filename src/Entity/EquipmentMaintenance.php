<?php

namespace App\Entity;

use App\Repository\EquipmentMaintenanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentMaintenanceRepository::class)]
class EquipmentMaintenance
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $maintenanceDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(inversedBy: 'equipmentMaintenances')]
    private ?TypeMaintenance $typeMaintenance = null;

    /**
     * @var Collection<int, Equipment>
     */
    #[ORM\ManyToMany(targetEntity: Equipment::class, inversedBy: 'equipmentMaintenances')]
    private Collection $equipement;

    public function __construct()
    {
        $this->equipement = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Equipment>
     */
    public function getEquipement(): Collection
    {
        return $this->equipement;
    }

    public function addEquipement(Equipment $equipement): static
    {
        if (!$this->equipement->contains($equipement)) {
            $this->equipement->add($equipement);
        }

        return $this;
    }

    public function removeEquipement(Equipment $equipement): static
    {
        $this->equipement->removeElement($equipement);

        return $this;
    }
}
