<?php

namespace App\Entity;

use App\Repository\PensionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PensionRepository::class)]
class Pension
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pensions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ranch $ranch = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $entryDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $endDate = null;

    #[ORM\OneToOne(inversedBy: 'pension', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Horse $horse = null;

    #[ORM\ManyToOne(inversedBy: 'pensions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypePension $typePension = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRanch(): ?Ranch
    {
        return $this->ranch;
    }

    public function setRanch(?Ranch $ranch): static
    {
        $this->ranch = $ranch;

        return $this;
    }

    public function getEntryDate(): ?\DateTime
    {
        return $this->entryDate;
    }

    public function setEntryDate(\DateTime $entryDate): static
    {
        $this->entryDate = $entryDate;

        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getHorse(): ?Horse
    {
        return $this->horse;
    }

    public function setHorse(Horse $horse): static
    {
        $this->horse = $horse;

        return $this;
    }

    public function getTypePension(): ?TypePension
    {
        return $this->typePension;
    }

    public function setTypePension(?TypePension $typePension): static
    {
        $this->typePension = $typePension;

        return $this;
    }
}
