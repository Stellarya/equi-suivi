<?php

namespace App\Entity;

use App\Repository\HorseCareRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HorseCareRepository::class)]
class HorseCare
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $careDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $notes = null;

    /**
     * @var Collection<int, Horse>
     */
    #[ORM\ManyToMany(targetEntity: Horse::class, inversedBy: 'horseCares')]
    private Collection $horse;

    #[ORM\ManyToOne(inversedBy: 'horseCares')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeCare $typeCare = null;

    #[ORM\ManyToOne(inversedBy: 'horseCares')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AppUser $enteredBy = null;

    public function __construct()
    {
        $this->horse = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCareDate(): ?\DateTime
    {
        return $this->careDate;
    }

    public function setCareDate(\DateTime $careDate): static
    {
        $this->careDate = $careDate;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return Collection<int, Horse>
     */
    public function getHorse(): Collection
    {
        return $this->horse;
    }

    public function addHorse(Horse $horse): static
    {
        if (!$this->horse->contains($horse)) {
            $this->horse->add($horse);
        }

        return $this;
    }

    public function removeHorse(Horse $horse): static
    {
        $this->horse->removeElement($horse);

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

    public function getEnteredBy(): ?AppUser
    {
        return $this->enteredBy;
    }

    public function setEnteredBy(?AppUser $enteredBy): static
    {
        $this->enteredBy = $enteredBy;

        return $this;
    }
}
