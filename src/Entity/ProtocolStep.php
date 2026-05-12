<?php

namespace App\Entity;

use App\Repository\ProtocolStepRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProtocolStepRepository::class)]
class ProtocolStep
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'protocolSteps')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DressageTest $dressageTest = null;

    #[ORM\Column]
    private ?int $ordre = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $marker = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 2, nullable: true)]
    private ?string $coefficient = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $technicalNote = null;

    #[ORM\Column(nullable: true)]
    private ?array $graphData = null;

    #[ORM\Column]
    private ?bool $estActif = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getMarker(): ?string
    {
        return $this->marker;
    }

    public function setMarker(?string $marker): static
    {
        $this->marker = $marker;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCoefficient(): ?string
    {
        return $this->coefficient;
    }

    public function setCoefficient(?string $coefficient): static
    {
        $this->coefficient = $coefficient;

        return $this;
    }

    public function getTechnicalNote(): ?string
    {
        return $this->technicalNote;
    }

    public function setTechnicalNote(?string $technicalNote): static
    {
        $this->technicalNote = $technicalNote;

        return $this;
    }

    public function getGraphData(): ?array
    {
        return $this->graphData;
    }

    public function setGraphData(?array $graphData): static
    {
        $this->graphData = $graphData;

        return $this;
    }

    public function isEstActif(): ?bool
    {
        return $this->estActif;
    }

    public function setEstActif(bool $estActif): static
    {
        $this->estActif = $estActif;

        return $this;
    }
}
