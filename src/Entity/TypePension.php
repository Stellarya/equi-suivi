<?php

namespace App\Entity;

use App\Entity\Traits\TableReferenceTrait;
use App\Repository\TypePensionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypePensionRepository::class)]
class TypePension
{
    use TableReferenceTrait;
    
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    /**
     * @var Collection<int, Pension>
     */
    #[ORM\OneToMany(targetEntity: Pension::class, mappedBy: 'typePension')]
    private Collection $pensions;

    public function __construct()
    {
        $this->pensions = new ArrayCollection();
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
     * @return Collection<int, Pension>
     */
    public function getPensions(): Collection
    {
        return $this->pensions;
    }

    public function addPension(Pension $pension): static
    {
        if (!$this->pensions->contains($pension)) {
            $this->pensions->add($pension);
            $pension->setTypePension($this);
        }

        return $this;
    }

    public function removePension(Pension $pension): static
    {
        if ($this->pensions->removeElement($pension)) {
            // set the owning side to null (unless already changed)
            if ($pension->getTypePension() === $this) {
                $pension->setTypePension(null);
            }
        }

        return $this;
    }
}
