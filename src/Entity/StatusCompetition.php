<?php

namespace App\Entity;

use App\Entity\Traits\TableReferenceTrait;
use App\Repository\StatusCompetitionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatusCompetitionRepository::class)]
class StatusCompetition
{
    use TableReferenceTrait;
    
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    /**
     * @var Collection<int, Competition>
     */
    #[ORM\OneToMany(targetEntity: Competition::class, mappedBy: 'statusCompetition')]
    private Collection $competitions;

    /**
     * @var Collection<int, CompetitionRegistration>
     */
    #[ORM\OneToMany(targetEntity: CompetitionRegistration::class, mappedBy: 'statusRegistration')]
    private Collection $competitionRegistrations;

    public function __construct()
    {
        $this->competitionRegistrations = new ArrayCollection();
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
     * @return Collection<int, Competition>
     */
    public function getCompetitions(): Collection
    {
        return $this->competitions;
    }

    public function addCompetition(Competition $competition): static
    {
        if (!$this->competitions->contains($competition)) {
            $this->competitions->add($competition);
            $competition->setStatusCompetition($this);
        }

        return $this;
    }

    public function removeCompetition(Competition $competition): static
    {
        if ($this->competitions->removeElement($competition)) {
            // set the owning side to null (unless already changed)
            if ($competition->getStatusCompetition() === $this) {
                $competition->setStatusCompetition(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CompetitionRegistration>
     */
    public function getCompetitionRegistrations(): Collection
    {
        return $this->competitionRegistrations;
    }

    public function addCompetitionRegistration(CompetitionRegistration $competitionRegistration): static
    {
        if (!$this->competitionRegistrations->contains($competitionRegistration)) {
            $this->competitionRegistrations->add($competitionRegistration);
            $competitionRegistration->setStatusRegistration($this);
        }

        return $this;
    }

    public function removeCompetitionRegistration(CompetitionRegistration $competitionRegistration): static
    {
        if ($this->competitionRegistrations->removeElement($competitionRegistration)) {
            // set the owning side to null (unless already changed)
            if ($competitionRegistration->getStatusRegistration() === $this) {
                $competitionRegistration->setStatusRegistration(null);
            }
        }

        return $this;
    }
}
