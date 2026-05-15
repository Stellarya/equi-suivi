<?php

namespace App\Entity;

use App\Entity\Traits\TableReferenceTrait;
use App\Repository\DisciplineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DisciplineRepository::class)]
class Discipline
{
    use TableReferenceTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(inversedBy: 'disciplines')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeEquitation $typeEquitation = null;

    /**
     * @var Collection<int, CompetitionEntry>
     */
    #[ORM\OneToMany(targetEntity: CompetitionEntry::class, mappedBy: 'discipline')]
    private Collection $competitionEntries;

    public function __construct()
    {
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

    public function getTypeEquitation(): ?TypeEquitation
    {
        return $this->typeEquitation;
    }

    public function setTypeEquitation(?TypeEquitation $typeEquitation): static
    {
        $this->typeEquitation = $typeEquitation;

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
            $competitionEntry->setDiscipline($this);
        }

        return $this;
    }

    public function removeCompetitionEntry(CompetitionEntry $competitionEntry): static
    {
        if ($this->competitionEntries->removeElement($competitionEntry)) {
            // set the owning side to null (unless already changed)
            if ($competitionEntry->getDiscipline() === $this) {
                $competitionEntry->setDiscipline(null);
            }
        }

        return $this;
    }
}
