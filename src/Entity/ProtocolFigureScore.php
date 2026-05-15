<?php

namespace App\Entity;

use App\Repository\ProtocolFigureScoreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProtocolFigureScoreRepository::class)]
class ProtocolFigureScore
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'protocolFigureScores')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Protocol $protocol = null;

    #[ORM\ManyToOne(inversedBy: 'protocolFigureScores')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProtocolFigure $protocolFigure = null;

    #[ORM\Column]
    private ?int $score = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(nullable: true)]
    private ?int $finalScore = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProtocol(): ?Protocol
    {
        return $this->protocol;
    }

    public function setProtocol(?Protocol $protocol): static
    {
        $this->protocol = $protocol;

        return $this;
    }

    public function getProtocolFigure(): ?ProtocolFigure
    {
        return $this->protocolFigure;
    }

    public function setProtocolFigure(?ProtocolFigure $protocolFigure): static
    {
        $this->protocolFigure = $protocolFigure;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

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

    public function getFinalScore(): ?int
    {
        return $this->finalScore;
    }

    public function setFinalScore(?int $finalScore): static
    {
        $this->finalScore = $finalScore;

        return $this;
    }
}
