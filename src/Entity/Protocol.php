<?php

namespace App\Entity;

use App\Repository\ProtocolRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProtocolRepository::class)]
class Protocol
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'protocols')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CompetitionEntry $competitionEntry = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $rawText = null;

    #[ORM\Column(nullable: true)]
    private ?int $totalPoints = null;

    #[ORM\Column]
    private ?int $maxPoints = null;

    #[ORM\Column(nullable: true)]
    private ?int $percentage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $generalComment = null;

    /**
     * @var Collection<int, ProtocolFigureScore>
     */
    #[ORM\OneToMany(targetEntity: ProtocolFigureScore::class, mappedBy: 'protocol')]
    private Collection $protocolFigureScores;

    public function __construct()
    {
        $this->protocolFigureScores = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompetitionEntry(): ?CompetitionEntry
    {
        return $this->competitionEntry;
    }

    public function setCompetitionEntry(?CompetitionEntry $competitionEntry): static
    {
        $this->competitionEntry = $competitionEntry;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getRawText(): ?string
    {
        return $this->rawText;
    }

    public function setRawText(string $rawText): static
    {
        $this->rawText = $rawText;

        return $this;
    }

    public function getTotalPoints(): ?int
    {
        return $this->totalPoints;
    }

    public function setTotalPoints(?int $totalPoints): static
    {
        $this->totalPoints = $totalPoints;

        return $this;
    }

    public function getMaxPoints(): ?int
    {
        return $this->maxPoints;
    }

    public function setMaxPoints(int $maxPoints): static
    {
        $this->maxPoints = $maxPoints;

        return $this;
    }

    public function getPercentage(): ?int
    {
        return $this->percentage;
    }

    public function setPercentage(?int $percentage): static
    {
        $this->percentage = $percentage;

        return $this;
    }

    public function getGeneralComment(): ?string
    {
        return $this->generalComment;
    }

    public function setGeneralComment(?string $generalComment): static
    {
        $this->generalComment = $generalComment;

        return $this;
    }

    /**
     * @return Collection<int, ProtocolFigureScore>
     */
    public function getProtocolFigureScores(): Collection
    {
        return $this->protocolFigureScores;
    }

    public function addProtocolFigureScore(ProtocolFigureScore $protocolFigureScore): static
    {
        if (!$this->protocolFigureScores->contains($protocolFigureScore)) {
            $this->protocolFigureScores->add($protocolFigureScore);
            $protocolFigureScore->setProtocol($this);
        }

        return $this;
    }

    public function removeProtocolFigureScore(ProtocolFigureScore $protocolFigureScore): static
    {
        if ($this->protocolFigureScores->removeElement($protocolFigureScore)) {
            // set the owning side to null (unless already changed)
            if ($protocolFigureScore->getProtocol() === $this) {
                $protocolFigureScore->setProtocol(null);
            }
        }

        return $this;
    }
}
