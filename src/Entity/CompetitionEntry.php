<?php

namespace App\Entity;

use App\Repository\CompetitionEntryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetitionEntryRepository::class)]
class CompetitionEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'competitionEntries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CompetitionRegistration $competitionRegistration = null;

    #[ORM\ManyToOne(inversedBy: 'competitionEntries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Discipline $discipline = null;

    #[ORM\ManyToOne(inversedBy: 'competitionEntries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'competitionEntries')]
    private ?Level $level = null;

    #[ORM\ManyToOne(inversedBy: 'competitionEntries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeTest $typeTest = null;

    #[ORM\ManyToOne(inversedBy: 'competitionEntries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DressageTest $dressageTest = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $startTime = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $orderNumber = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $rank = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $numberParticipant = null;

    #[ORM\Column(nullable: true)]
    private ?int $score = null;

    #[ORM\Column(nullable: true)]
    private ?int $scorePercent = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $startDate = null;

    /**
     * @var Collection<int, Protocol>
     */
    #[ORM\OneToMany(targetEntity: Protocol::class, mappedBy: 'competitionEntry')]
    private Collection $protocols;

    public function __construct()
    {
        $this->protocols = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompetitionRegistration(): ?CompetitionRegistration
    {
        return $this->competitionRegistration;
    }

    public function setCompetitionRegistration(?CompetitionRegistration $competitionRegistration): static
    {
        $this->competitionRegistration = $competitionRegistration;

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

    public function getDressageTest(): ?DressageTest
    {
        return $this->dressageTest;
    }

    public function setDressageTest(?DressageTest $dressageTest): static
    {
        $this->dressageTest = $dressageTest;

        return $this;
    }

    public function getStartTime(): ?\DateTime
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTime $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getOrderNumber(): ?int
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(?int $orderNumber): static
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): static
    {
        $this->rank = $rank;

        return $this;
    }

    public function getNumberParticipant(): ?int
    {
        return $this->numberParticipant;
    }

    public function setNumberParticipant(?int $numberParticipant): static
    {
        $this->numberParticipant = $numberParticipant;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getScorePercent(): ?int
    {
        return $this->scorePercent;
    }

    public function setScorePercent(?int $scorePercent): static
    {
        $this->scorePercent = $scorePercent;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTime $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return Collection<int, Protocol>
     */
    public function getProtocols(): Collection
    {
        return $this->protocols;
    }

    public function addProtocol(Protocol $protocol): static
    {
        if (!$this->protocols->contains($protocol)) {
            $this->protocols->add($protocol);
            $protocol->setCompetitionEntry($this);
        }

        return $this;
    }

    public function removeProtocol(Protocol $protocol): static
    {
        if ($this->protocols->removeElement($protocol)) {
            // set the owning side to null (unless already changed)
            if ($protocol->getCompetitionEntry() === $this) {
                $protocol->setCompetitionEntry(null);
            }
        }

        return $this;
    }
}
