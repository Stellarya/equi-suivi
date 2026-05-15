<?php

namespace App\Entity;

use App\Repository\RanchRepository;
use BcMath\Number;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RanchRepository::class)]
class Ranch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\Column(nullable: true)]
    private ?int $phone = null;

    /**
     * @var Collection<int, Horse>
     */
    #[ORM\OneToMany(targetEntity: Horse::class, mappedBy: 'ranch')]
    private Collection $horses;

    /**
     * @var Collection<int, Rider>
     */
    #[ORM\ManyToMany(targetEntity: Rider::class, mappedBy: 'ranch')]
    private Collection $riders;

    public function __construct()
    {
        $this->horses = new ArrayCollection();
        $this->riders = new ArrayCollection();
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPhone(): ?int
    {
        return $this->phone;
    }

    public function setPhone(?int $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return Collection<int, Horse>
     */
    public function getHorses(): Collection
    {
        return $this->horses;
    }

    public function addHorse(Horse $horse): static
    {
        if (!$this->horses->contains($horse)) {
            $this->horses->add($horse);
            $horse->setRanch($this);
        }

        return $this;
    }

    public function removeHorse(Horse $horse): static
    {
        if ($this->horses->removeElement($horse)) {
            // set the owning side to null (unless already changed)
            if ($horse->getRanch() === $this) {
                $horse->setRanch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Rider>
     */
    public function getRiders(): Collection
    {
        return $this->riders;
    }

    public function addRider(Rider $rider): static
    {
        if (!$this->riders->contains($rider)) {
            $this->riders->add($rider);
            $rider->addRanch($this);
        }

        return $this;
    }

    public function removeRider(Rider $rider): static
    {
        if ($this->riders->removeElement($rider)) {
            $rider->removeRanch($this);
        }

        return $this;
    }

}
