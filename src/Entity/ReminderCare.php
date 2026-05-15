<?php

namespace App\Entity;

use App\Repository\ReminderCareRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReminderCareRepository::class)]
class ReminderCare
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $expectedDate = null;

    #[ORM\Column(nullable: true)]
    private ?int $intervalPersonnalValue = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $intervalPersonnalUnit = null;

    #[ORM\ManyToOne(inversedBy: 'reminderCares')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Horse $horse = null;

    #[ORM\ManyToOne(inversedBy: 'reminderCares')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeCare $typeCare = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExpectedDate(): ?\DateTime
    {
        return $this->expectedDate;
    }

    public function setExpectedDate(\DateTime $expectedDate): static
    {
        $this->expectedDate = $expectedDate;

        return $this;
    }

    public function getIntervalPersonnalValue(): ?int
    {
        return $this->intervalPersonnalValue;
    }

    public function setIntervalPersonnalValue(?int $intervalPersonnalValue): static
    {
        $this->intervalPersonnalValue = $intervalPersonnalValue;

        return $this;
    }

    public function getIntervalPersonnalUnit(): ?string
    {
        return $this->intervalPersonnalUnit;
    }

    public function setIntervalPersonnalUnit(?string $intervalPersonnalUnit): static
    {
        $this->intervalPersonnalUnit = $intervalPersonnalUnit;

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

    public function getTypeCare(): ?TypeCare
    {
        return $this->typeCare;
    }

    public function setTypeCare(?TypeCare $typeCare): static
    {
        $this->typeCare = $typeCare;

        return $this;
    }
}
