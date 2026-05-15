<?php

namespace App\Entity;

use App\Repository\EquipmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
class Equipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $brand = null;

    #[ORM\Column(length: 255)]
    private ?string $model = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $purchaseDate = null;

    #[ORM\ManyToOne(inversedBy: 'equipment')]
    private ?Rider $rider = null;

    #[ORM\ManyToOne(inversedBy: 'equipment')]
    private ?TypeEquipment $typeEquipment = null;

    #[ORM\ManyToOne(inversedBy: 'equipment')]
    private ?TypeSaddle $typeSaddle = null;

    /**
     * @var Collection<int, EquipmentMaintenance>
     */
    #[ORM\ManyToMany(targetEntity: EquipmentMaintenance::class, mappedBy: 'equipement')]
    private Collection $equipmentMaintenances;

    /**
     * @var Collection<int, Discipline>
     */
    #[ORM\ManyToMany(targetEntity: Discipline::class, inversedBy: 'equipments')]
    private Collection $discipline;

    /**
     * @var Collection<int, ReminderMaintenance>
     */
    #[ORM\OneToMany(targetEntity: ReminderMaintenance::class, mappedBy: 'equipment')]
    private Collection $reminderMaintenances;

    public function __construct()
    {
        $this->equipmentMaintenances = new ArrayCollection();
        $this->discipline = new ArrayCollection();
        $this->reminderMaintenances = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getPurchaseDate(): ?\DateTime
    {
        return $this->purchaseDate;
    }

    public function setPurchaseDate(?\DateTime $purchaseDate): static
    {
        $this->purchaseDate = $purchaseDate;

        return $this;
    }

    public function getRider(): ?Rider
    {
        return $this->rider;
    }

    public function setRider(?Rider $rider): static
    {
        $this->rider = $rider;

        return $this;
    }

    public function getTypeEquipment(): ?TypeEquipment
    {
        return $this->typeEquipment;
    }

    public function setTypeEquipment(?TypeEquipment $typeEquipment): static
    {
        $this->typeEquipment = $typeEquipment;

        return $this;
    }

    public function getTypeSaddle(): ?TypeSaddle
    {
        return $this->typeSaddle;
    }

    public function setTypeSaddle(?TypeSaddle $typeSaddle): static
    {
        $this->typeSaddle = $typeSaddle;

        return $this;
    }

    /**
     * @return Collection<int, EquipmentMaintenance>
     */
    public function getEquipmentMaintenances(): Collection
    {
        return $this->equipmentMaintenances;
    }

    public function addEquipmentMaintenance(EquipmentMaintenance $equipmentMaintenance): static
    {
        if (!$this->equipmentMaintenances->contains($equipmentMaintenance)) {
            $this->equipmentMaintenances->add($equipmentMaintenance);
            $equipmentMaintenance->addEquipement($this);
        }

        return $this;
    }

    public function removeEquipmentMaintenance(EquipmentMaintenance $equipmentMaintenance): static
    {
        if ($this->equipmentMaintenances->removeElement($equipmentMaintenance)) {
            $equipmentMaintenance->removeEquipement($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Discipline>
     */
    public function getDiscipline(): Collection
    {
        return $this->discipline;
    }

    public function addDiscipline(Discipline $discipline): static
    {
        if (!$this->discipline->contains($discipline)) {
            $this->discipline->add($discipline);
        }

        return $this;
    }

    public function removeDiscipline(Discipline $discipline): static
    {
        $this->discipline->removeElement($discipline);

        return $this;
    }

    /**
     * @return Collection<int, ReminderMaintenance>
     */
    public function getReminderMaintenances(): Collection
    {
        return $this->reminderMaintenances;
    }

    public function addReminderMaintenance(ReminderMaintenance $reminderMaintenance): static
    {
        if (!$this->reminderMaintenances->contains($reminderMaintenance)) {
            $this->reminderMaintenances->add($reminderMaintenance);
            $reminderMaintenance->setEquipment($this);
        }

        return $this;
    }

    public function removeReminderMaintenance(ReminderMaintenance $reminderMaintenance): static
    {
        if ($this->reminderMaintenances->removeElement($reminderMaintenance)) {
            // set the owning side to null (unless already changed)
            if ($reminderMaintenance->getEquipment() === $this) {
                $reminderMaintenance->setEquipment(null);
            }
        }

        return $this;
    }
}
