<?php

namespace App\Entity;

use App\Repository\HorseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HorseRepository::class)]
class Horse
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $affix = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $birthDate = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $sire = null;

    /**
     * @var Collection<int, Rider>
     */
    #[ORM\ManyToMany(targetEntity: Rider::class, inversedBy: 'horses')]
    private Collection $rider;

    #[ORM\ManyToOne(inversedBy: 'horses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Breed $breed = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Coat $coat = null;

    #[ORM\ManyToOne(inversedBy: 'ownedHorses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AppUser $owner = null;

    public function __construct()
    {
        $this->rider = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAffix(): ?string
    {
        return $this->affix;
    }

    public function setAffix(?string $affix): static
    {
        $this->affix = $affix;

        return $this;
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTime $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getSire(): ?string
    {
        return $this->sire;
    }

    public function setSire(?string $sire): static
    {
        $this->sire = $sire;

        return $this;
    }

    /**
     * @return Collection<int, Rider>
     */
    public function getRider(): Collection
    {
        return $this->rider;
    }

    public function addRider(Rider $rider): static
    {
        if (!$this->rider->contains($rider)) {
            $this->rider->add($rider);
        }

        return $this;
    }

    public function removeRider(Rider $rider): static
    {
        $this->rider->removeElement($rider);

        return $this;
    }

    public function getBreed(): ?Breed
    {
        return $this->breed;
    }

    public function setBreed(?Breed $breed): static
    {
        $this->breed = $breed;

        return $this;
    }

    public function getCoat(): ?Coat
    {
        return $this->coat;
    }

    public function setCoat(?Coat $coat): static
    {
        $this->coat = $coat;

        return $this;
    }

    public function getOwner(): ?AppUser
    {
        return $this->owner;
    }

    public function setOwner(?AppUser $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function __toString(): string
    {
        return trim(sprintf('%s %s', $this->name ?? '', $this->affix ?? ''));
    }
}
