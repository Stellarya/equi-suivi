<?php

namespace App\Entity;

use App\Repository\ProtocolFigureScoreRepository;
use Doctrine\DBAL\Types\Types;
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

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 2, nullable: true)]
    private ?string $score = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $finalScore = null;

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

    public function getScore(): ?string
    {
        return $this->score;
    }

    public function setScore(string $score): static
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

    public function getFinalScore(): ?string
    {
        return $this->finalScore;
    }

    public function setFinalScore(?string $finalScore): static
    {
        $this->finalScore = $finalScore;

        return $this;
    }
}
