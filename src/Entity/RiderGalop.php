<?php

namespace App\Entity;

use App\Repository\RiderGalopRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RiderGalopRepository::class)]
class RiderGalop
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'galopHistory')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rider $rider = null;

    #[ORM\ManyToOne(inversedBy: 'riderGalops')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Galop $galop = null;

    #[ORM\Column(nullable: true)]
    private ?int $obtainedYear = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRider(): ?Rider
    {
        return $this->rider;
    }

    public function setRider(?Rider $rider): static
    {
        $this->rider = $rider;

        return $this;
    }

    public function getGalop(): ?Galop
    {
        return $this->galop;
    }

    public function setGalop(?Galop $galop): static
    {
        $this->galop = $galop;

        return $this;
    }

    public function getObtainedYear(): ?int
    {
        return $this->obtainedYear;
    }

    public function setObtainedYear(?int $obtainedYear): static
    {
        $this->obtainedYear = $obtainedYear;

        return $this;
    }

    public function __toString(): string
    {
        $galopLabel = $this->galop?->getLibelle() ?? 'Galop non renseigné';

        if ($this->obtainedYear !== null) {
            return sprintf('%s - $s', $galopLabel, $this->obtainedYear);
        }

        return $galopLabel;
    }
}
