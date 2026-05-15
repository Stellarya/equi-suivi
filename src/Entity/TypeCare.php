<?php

namespace App\Entity;

use App\Entity\Traits\TableReferenceTrait;
use App\Repository\TypeCareRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeCareRepository::class)]
class TypeCare
{
    use TableReferenceTrait;

    public const INTERVAL_UNIT_DAYS = 'days';
    public const INTERVAL_UNIT_WEEKS = 'weeks';
    public const INTERVAL_UNIT_MONTHS = 'months';
    public const INTERVAL_UNIT_YEARS = 'years';

    public const INTERVAL_UNIT_CHOICES = [
        'Jours' => self::INTERVAL_UNIT_DAYS,
        'Semaines' => self::INTERVAL_UNIT_WEEKS,
        'Mois' => self::INTERVAL_UNIT_MONTHS,
        'Années' => self::INTERVAL_UNIT_YEARS,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(nullable: true)]
    private ?int $intervalDefaultValue = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $intervalDefaultUnit = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $conseils = null;

    /**
     * @var Collection<int, HorseCare>
     */
    #[ORM\OneToMany(targetEntity: HorseCare::class, mappedBy: 'typeCare')]
    private Collection $horseCares;

    public function __construct()
    {
        $this->horseCares = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getIntervalDefaultValue(): ?int
    {
        return $this->intervalDefaultValue;
    }

    public function setIntervalDefaultValue(?int $intervalDefaultValue): static
    {
        $this->intervalDefaultValue = $intervalDefaultValue;

        return $this;
    }

    public function getIntervalDefaultUnit(): ?string
    {
        return $this->intervalDefaultUnit;
    }

    public function setIntervalDefaultUnit(?string $intervalDefaultUnit): static
    {
        $this->intervalDefaultUnit = $intervalDefaultUnit;

        return $this;
    }

    public function getConseils(): ?string
    {
        return $this->conseils;
    }

    public function setConseils(?string $conseils): static
    {
        $this->conseils = $conseils;

        return $this;
    }

    public function hasDefaultInterval(): bool
    {
        return $this->intervalDefaultValue !== null
            && $this->intervalDefaultValue > 0
            && $this->intervalDefaultUnit !== null;
    }

    public function getIntervalDefaultLabel(): string
    {
        if (!$this->hasDefaultInterval()) {
            return 'Non défini';
        }

        return match ($this->intervalDefaultUnit) {
            self::INTERVAL_UNIT_DAYS => $this->intervalDefaultValue > 1
                ? sprintf('Tous les %d jours', $this->intervalDefaultValue)
                : 'Tous les jours',

            self::INTERVAL_UNIT_WEEKS => $this->intervalDefaultValue > 1
                ? sprintf('Toutes les %d semaines', $this->intervalDefaultValue)
                : 'Toutes les semaines',

            self::INTERVAL_UNIT_MONTHS => $this->intervalDefaultValue > 1
                ? sprintf('Tous les %d mois', $this->intervalDefaultValue)
                : 'Tous les mois',

            self::INTERVAL_UNIT_YEARS => $this->intervalDefaultValue > 1
                ? sprintf('Tous les %d ans', $this->intervalDefaultValue)
                : 'Tous les ans',

            default => 'Non défini',
        };
    }

    public function buildDateIntervalSpec(): ?string
    {
        if (!$this->hasDefaultInterval()) {
            return null;
        }

        return match ($this->intervalDefaultUnit) {
            self::INTERVAL_UNIT_DAYS => sprintf('P%dD', $this->intervalDefaultValue),
            self::INTERVAL_UNIT_WEEKS => sprintf('P%dW', $this->intervalDefaultValue),
            self::INTERVAL_UNIT_MONTHS => sprintf('P%dM', $this->intervalDefaultValue),
            self::INTERVAL_UNIT_YEARS => sprintf('P%dY', $this->intervalDefaultValue),
            default => null,
        };
    }

    public function getDefaultDateInterval(): ?\DateInterval
    {
        $intervalSpec = $this->buildDateIntervalSpec();

        if ($intervalSpec === null) {
            return null;
        }

        return new \DateInterval($intervalSpec);
    }

    public function calculateNextDueDate(\DateTimeInterface $fromDate): ?\DateTimeImmutable
    {
        $dateInterval = $this->getDefaultDateInterval();

        if ($dateInterval === null) {
            return null;
        }

        return \DateTimeImmutable::createFromInterface($fromDate)->add($dateInterval);
    }

    public function __toString(): string
    {
        return $this->libelle ?? '';
    }

    /**
     * @return Collection<int, HorseCare>
     */
    public function getHorseCares(): Collection
    {
        return $this->horseCares;
    }

    public function addHorseCare(HorseCare $horseCare): static
    {
        if (!$this->horseCares->contains($horseCare)) {
            $this->horseCares->add($horseCare);
            $horseCare->setTypeCare($this);
        }

        return $this;
    }

    public function removeHorseCare(HorseCare $horseCare): static
    {
        if ($this->horseCares->removeElement($horseCare)) {
            // set the owning side to null (unless already changed)
            if ($horseCare->getTypeCare() === $this) {
                $horseCare->setTypeCare(null);
            }
        }

        return $this;
    }
}