<?php

namespace App\Entity;

use App\Repository\CompetitionRegistrationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetitionRegistrationRepository::class)]
class CompetitionRegistration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'competitionRegistrations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Competition $competition = null;

    #[ORM\ManyToOne(inversedBy: 'competitionRegistrations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Horse $horse = null;

    #[ORM\ManyToOne(inversedBy: 'competitionRegistrations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rider $rider = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $registrationDate = null;

    #[ORM\Column(nullable: true)]
    private ?int $bibNumber = null;

    #[ORM\ManyToOne(inversedBy: 'competitionRegistrations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?StatusCompetition $statusRegistration = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $note = null;

    /**
     * @var Collection<int, CompetitionEntry>
     */
    #[ORM\OneToMany(targetEntity: CompetitionEntry::class, mappedBy: 'competitionRegistration')]
    private Collection $competitionEntries;

    public function __construct()
    {
        $this->competitionEntries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompetition(): ?Competition
    {
        return $this->competition;
    }

    public function setCompetition(?Competition $competition): static
    {
        $this->competition = $competition;

        return $this;
    }

    public function getHorse(): ?Horse
    {
        return $this->horse;
    }

    public function setHorse(?Horse $horse): static
    {
        $this->horse = $horse;

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

    public function getRegistrationDate(): ?\DateTime
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(\DateTime $registrationDate): static
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    public function getBibNumber(): ?int
    {
        return $this->bibNumber;
    }

    public function setBibNumber(?int $bibNumber): static
    {
        $this->bibNumber = $bibNumber;

        return $this;
    }

    public function getStatusRegistration(): ?StatusCompetition
    {
        return $this->statusRegistration;
    }

    public function setStatusRegistration(?StatusCompetition $statusRegistration): static
    {
        $this->statusRegistration = $statusRegistration;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
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
            $competitionEntry->setCompetitionRegistration($this);
        }

        return $this;
    }

    public function removeCompetitionEntry(CompetitionEntry $competitionEntry): static
    {
        if ($this->competitionEntries->removeElement($competitionEntry)) {
            // set the owning side to null (unless already changed)
            if ($competitionEntry->getCompetitionRegistration() === $this) {
                $competitionEntry->setCompetitionRegistration(null);
            }
        }

        return $this;
    }
}
