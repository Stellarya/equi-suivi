<?php

namespace App\Entity;

use App\Entity\Traits\TableReferenceTrait;
use App\Repository\DressageTestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DressageTestRepository::class)]
class DressageTest
{
    use TableReferenceTrait;
    
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Discipline $discipline = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'dressageTests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Level $level = null;

    #[ORM\ManyToOne(inversedBy: 'dressageTests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeTest $typeTest = null;

    /**
     * @var Collection<int, ProtocolFigure>
     */
    #[ORM\OneToMany(targetEntity: ProtocolFigure::class, mappedBy: 'dressageTest')]
    private Collection $protocolFigures;

    public function __construct()
    {
        $this->protocolFigures = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getDiscipline(): ?Discipline
    {
        return $this->discipline;
    }

    public function setDiscipline(?Discipline $discipline): static
    {
        $this->discipline = $discipline;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getTypeTest(): ?TypeTest
    {
        return $this->typeTest;
    }

    public function setTypeTest(?TypeTest $typeTest): static
    {
        $this->typeTest = $typeTest;

        return $this;
    }

    /**
     * @return Collection<int, ProtocolFigure>
     */
    public function getProtocolFigures(): Collection
    {
        return $this->protocolFigures;
    }

    public function addProtocolFigure(ProtocolFigure $protocolFigure): static
    {
        if (!$this->protocolFigures->contains($protocolFigure)) {
            $this->protocolFigures->add($protocolFigure);
            $protocolFigure->setDressageTest($this);
        }

        return $this;
    }

    public function removeProtocolFigure(ProtocolFigure $protocolFigure): static
    {
        if ($this->protocolFigures->removeElement($protocolFigure)) {
            // set the owning side to null (unless already changed)
            if ($protocolFigure->getDressageTest() === $this) {
                $protocolFigure->setDressageTest(null);
            }
        }

        return $this;
    }
    public function __toString(): string
    {
        return $this->getLibelle() ?? '';
    }

}
