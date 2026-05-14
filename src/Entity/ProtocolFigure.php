<?php

namespace App\Entity;

use App\Repository\ProtocolFigureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProtocolFigureRepository::class)]
class ProtocolFigure
{
    public const SECTION_TECHNICAL = 'technical';
    public const SECTION_COLLECTIVE = 'collective';
    public const SECTION_ARTISTIC = 'artistic';

     public const SECTION_CHOICES = [
        'Figure technique' => self::SECTION_TECHNICAL,
        'Note d\'ensemble' => self::SECTION_COLLECTIVE,
        'Note artistique' => self::SECTION_ARTISTIC,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
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

    #[ORM\Column(name: 'est_actif', type: 'boolean', nullable: false, options: ['default' => true])]
    private ?bool $estActif = true;

    #[ORM\Column(length: 30)]
    private ?string $section = self::SECTION_TECHNICAL;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $ordre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $label = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $maxPoints = null;

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

     public function getSection(): ?string
     {
         return $this->section;
     }

     public function setSection(?string $section): static
     {
         $this->section = $section;

         return $this;
     }

     public function getOrdre(): ?int
     {
         return $this->ordre;
     }

     public function setOrdre(?int $ordre): static
     {
         $this->ordre = $ordre;

         return $this;
     }

     public function getLabel(): ?string
     {
         return $this->label;
     }

     public function setLabel(?string $label): static
     {
         $this->label = $label;

         return $this;
     }

     public function getMaxPoints(): ?int
     {
         return $this->maxPoints;
     }

     public function setMaxPoints(?int $maxPoints): static
     {
         $this->maxPoints = $maxPoints;

         return $this;
     }

      public function __toString(): string
    {
        $testLabel = $this->dressageTest?->getLibelle() ?? 'Reprise inconnue';
        $number = $this->number !== null ? sprintf('Figure %d', $this->number) : 'Ligne sans numéro';
        $label = $this->label !== null ? sprintf(' - %s', $this->label) : '';


        return sprintf('%s - %s%s', $testLabel, $number, $label);
    }
}
