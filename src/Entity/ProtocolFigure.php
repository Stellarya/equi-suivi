<?php

namespace App\Entity;

use App\Repository\ProtocolFigureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProtocolFigureRepository::class)]
class ProtocolFigure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'protocolFigures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DressageTest $dressageTest = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $number = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $directiveIdeas = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 2)]
    private ?string $coefficient = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $technicalNote = null;

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

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getDirectiveIdeas(): ?string
    {
        return $this->directiveIdeas;
    }

    public function setDirectiveIdeas(?string $directiveIdeas): static
    {
        $this->directiveIdeas = $directiveIdeas;

        return $this;
    }

    public function getCoefficient(): ?string
    {
        return $this->coefficient;
    }

    public function setCoefficient(string $coefficient): static
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
