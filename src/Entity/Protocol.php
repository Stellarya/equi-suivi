<?php

namespace App\Entity;

use App\Repository\ProtocolRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProtocolRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_protocol_entry_judge', columns: ['competition_entry_id', 'judge_position'])]
class Protocol
{
    public const STATUS_UPLOADED  = 'uploaded';
    public const STATUS_ANALYZING = 'analyzing';
    public const STATUS_ANALYZED  = 'analyzed';
    public const STATUS_FAILED    = 'failed';

    public const STATUS_NEEDS_REVIEW = 'needs_review';

    public const JUDGE_C = 'C';
    public const JUDGE_H = 'H';

    public const JUDGE_POSITION_CHOICES = [
        'Juge en C' => self::JUDGE_C,
        'Juge en H' => self::JUDGE_H
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'protocols')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CompetitionEntry $competitionEntry = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rawText = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2, nullable: true)]
    private ?string $totalPoints = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2, nullable: true)]
    private ?string $maxPoints = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 3, nullable: true)]
    private ?string $percentage = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $generalComment = null;

    #[ORM\Column(length: 2)]
    private ?string $judgePosition = null;

    #[ORM\Column(length: 20, options: ['default' => self::STATUS_UPLOADED])]
    private string $status = self::STATUS_UPLOADED;
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

    public function getJudgePosition(): ?string
    {
        return $this->judgePosition;
    }

    public function setJudgePosition(?string $judgePosition): static
    {
        $this->judgePosition = $judgePosition;

        return $this;
    }

    public function getRawText(): ?string
    {
        return $this->rawText;
    }

    public function setRawText(?string $rawText): static
    {
        $this->rawText = $rawText;

        return $this;
    }

    public function getTotalPoints(): ?string
    {
        return $this->totalPoints;
    }

    public function setTotalPoints(?string $totalPoints): static
    {
        $this->totalPoints = $totalPoints;

        return $this;
    }

    public function getMaxPoints(): ?string
    {
        return $this->maxPoints;
    }

    public function setMaxPoints(?string $maxPoints): static
    {
        $this->maxPoints = $maxPoints;

        return $this;
    }

    public function getPercentage(): ?string
    {
        return $this->percentage;
    }

    public function setPercentage(?string $percentage): static
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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
