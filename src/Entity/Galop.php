<?php

namespace App\Entity;

use App\Entity\Traits\TableReferenceTrait;
use App\Repository\GalopRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GalopRepository::class)]
class Galop
{
    use TableReferenceTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    /**
     * @var Collection<int, RiderGalop>
     */
    #[ORM\OneToMany(targetEntity: RiderGalop::class, mappedBy: 'galop')]
    private Collection $riderGalops;

    public function __construct()
    {
        $this->riderGalops = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, RiderGalop>
     */
    public function getRiderGalops(): Collection
    {
        return $this->riderGalops;
    }

    public function addRiderGalop(RiderGalop $riderGalop): static
    {
        if (!$this->riderGalops->contains($riderGalop)) {
            $this->riderGalops->add($riderGalop);
            $riderGalop->setGalop($this);
        }

        return $this;
    }

    public function removeRiderGalop(RiderGalop $riderGalop): static
    {
        if ($this->riderGalops->removeElement($riderGalop)) {
            // set the owning side to null (unless already changed)
            if ($riderGalop->getGalop() === $this) {
                $riderGalop->setGalop(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->libelle ?? '';
    }
}
