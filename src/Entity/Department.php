<?php

namespace App\Entity;

use App\Entity\Traits\TableReferenceTrait;
use App\Repository\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
class Department
{
    
    use TableReferenceTrait;
    
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $numberDepartment = null;

    #[ORM\ManyToOne(inversedBy: 'departments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Region $region = null;

    /**
     * @var Collection<int, Ranch>
     */
    #[ORM\OneToMany(targetEntity: Ranch::class, mappedBy: 'department')]
    private Collection $ranches;

    public function __construct()
    {
        $this->ranches = new ArrayCollection();
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

    public function getNumberDepartment(): ?int
    {
        return $this->numberDepartment;
    }

    public function setNumberDepartment(int $numberDepartment): static
    {
        $this->numberDepartment = $numberDepartment;

        return $this;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): static
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return Collection<int, Ranch>
     */
    public function getRanches(): Collection
    {
        return $this->ranches;
    }

    public function addRanch(Ranch $ranch): static
    {
        if (!$this->ranches->contains($ranch)) {
            $this->ranches->add($ranch);
            $ranch->setDepartment($this);
        }

        return $this;
    }

    public function removeRanch(Ranch $ranch): static
    {
        if ($this->ranches->removeElement($ranch)) {
            // set the owning side to null (unless already changed)
            if ($ranch->getDepartment() === $this) {
                $ranch->setDepartment(null);
            }
        }

        return $this;
    }
}
