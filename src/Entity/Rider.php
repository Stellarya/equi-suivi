<?php

namespace App\Entity;

use App\Repository\RiderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RiderRepository::class)]
class Rider
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\OneToOne(inversedBy: 'rider')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AppUser $appUser = null;

    /**
     * @var Collection<int, RiderGalop>
     */
    #[ORM\OneToMany(targetEntity: RiderGalop::class, mappedBy: 'rider', orphanRemoval: true)]
    private Collection $galopHistory;

    public function __construct()
    {
        $this->galopHistory = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getAppUser(): ?AppUser
    {
        return $this->appUser;
    }

    public function setAppUser(AppUser $appUser): static
    {
        $this->appUser = $appUser;

        return $this;
    }

    public function getFullName(): string {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    /**
     * @return Collection<int, RiderGalop>
     */
    public function getGalopHistory(): Collection
    {
        return $this->galopHistory;
    }

    public function addGalopHistory(RiderGalop $galopHistory): static
    {
        if (!$this->galopHistory->contains($galopHistory)) {
            $this->galopHistory->add($galopHistory);
            $galopHistory->setRider($this);
        }

        return $this;
    }

    public function removeGalopHistory(RiderGalop $galopHistory): static
    {
        if ($this->galopHistory->removeElement($galopHistory)) {
            // set the owning side to null (unless already changed)
            if ($galopHistory->getRider() === $this) {
                $galopHistory->setRider(null);
            }
        }

        return $this;
    }
}
