<?php

namespace App\Entity;

use App\Entity\Traits\TableReferenceTrait;
use App\Repository\TypeTestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeTestRepository::class)]
class TypeTest
{

    use TableReferenceTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    /**
     * @var Collection<int, DressageTest>
     */
    #[ORM\OneToMany(targetEntity: DressageTest::class, mappedBy: 'typeTest')]
    private Collection $dressageTests;

    /**
     * @var Collection<int, CompetitionEntry>
     */
    #[ORM\OneToMany(targetEntity: CompetitionEntry::class, mappedBy: 'typeTest')]
    private Collection $competitionEntries;

    public function __construct()
    {
        $this->dressageTests = new ArrayCollection();
        $this->competitionEntries = new ArrayCollection();
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

    /**
     * @return Collection<int, DressageTest>
     */
    public function getDressageTests(): Collection
    {
        return $this->dressageTests;
    }

    public function addDressageTest(DressageTest $dressageTest): static
    {
        if (!$this->dressageTests->contains($dressageTest)) {
            $this->dressageTests->add($dressageTest);
            $dressageTest->setTypeTest($this);
        }

        return $this;
    }

    public function removeDressageTest(DressageTest $dressageTest): static
    {
        if ($this->dressageTests->removeElement($dressageTest)) {
            // set the owning side to null (unless already changed)
            if ($dressageTest->getTypeTest() === $this) {
                $dressageTest->setTypeTest(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getLibelle() ?? '';
    }

    /**
     * @return Collection<int, CompetitionEntry>
     */
    public function getCompetitionEntries(): Collection
    {
        return $this->competitionEntries;
    }

    public function addCompetitionEntry(CompetitionEntry $competitionEntry): static
    {
        if (!$this->competitionEntries->contains($competitionEntry)) {
            $this->competitionEntries->add($competitionEntry);
            $competitionEntry->setTypeTest($this);
        }

        return $this;
    }

    public function removeCompetitionEntry(CompetitionEntry $competitionEntry): static
    {
        if ($this->competitionEntries->removeElement($competitionEntry)) {
            // set the owning side to null (unless already changed)
            if ($competitionEntry->getTypeTest() === $this) {
                $competitionEntry->setTypeTest(null);
            }
        }

        return $this;
    }
}
