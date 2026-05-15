<?php

namespace App\Entity;

use App\Repository\CompetitionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetitionRepository::class)]
class Competition
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $startDate = null;

    #[ORM\Column(length: 255)]
    private ?string $endDate = null;

    #[ORM\Column(length: 255)]
    private ?string $location = null;

    #[ORM\ManyToOne(inversedBy: 'competitions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?StatusCompetition $statusCompetition = null;

    /**
     * @var Collection<int, CompetitionRegistration>
     */
    #[ORM\OneToMany(targetEntity: CompetitionRegistration::class, mappedBy: 'competition')]
    private Collection $competitionRegistrations;

    public function __construct()
    {
        $this->competitionRegistrations = new ArrayCollection();
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

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function setEndDate(string $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getStatusCompetition(): ?StatusCompetition
    {
        return $this->statusCompetition;
    }

    public function setStatusCompetition(?StatusCompetition $statusCompetition): static
    {
        $this->statusCompetition = $statusCompetition;

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
            $competitionRegistration->setCompetition($this);
        }

        return $this;
    }

    public function removeCompetitionRegistration(CompetitionRegistration $competitionRegistration): static
    {
        if ($this->competitionRegistrations->removeElement($competitionRegistration)) {
            // set the owning side to null (unless already changed)
            if ($competitionRegistration->getCompetition() === $this) {
                $competitionRegistration->setCompetition(null);
            }
        }

        return $this;
    }

    
}
