<?php

namespace App\Entity;

use App\Repository\HorseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HorseRepository::class)]
class Horse
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_RESTING = 'resting';
    public const STATUS_RETIRED = 'retired';
    public const STATUS_SOLD = 'sold';
    public const STATUS_DECEASED = 'deceased';
    public const STATUS_ARCHIVED = 'archived';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'horse.validation.name_not_blank')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'horse.validation.name_max_length'
    )]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'horse.validation.affix_max_length'
    )]
    private ?string $affix = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $birthDate = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Length(
        max: 20,
        maxMessage: 'horse.validation.sire_max_length'
    )]
    private ?string $sire = null;

    /**
     * @var Collection<int, Rider>
     */
    #[ORM\ManyToMany(targetEntity: Rider::class, inversedBy: 'horses')]
    private Collection $riders;

    #[ORM\ManyToOne(inversedBy: 'horses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'horse.validation.breed_not_null')]
    private ?Breed $breed = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'horse.validation.coat_not_null')]
    private ?Coat $coat = null;

    #[ORM\ManyToOne(inversedBy: 'ownedHorses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AppUser $owner = null;

    #[ORM\Column(length: 30)]
    private ?string $status = self::STATUS_ACTIVE;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoFilename = null;

    /**
     * @var Collection<int, CompetitionRegistration>
     */
    #[ORM\OneToMany(targetEntity: CompetitionRegistration::class, mappedBy: 'horse')]
    private Collection $competitionRegistrations;

    public function __construct()
    {
        $this->riders = new ArrayCollection();
        $this->competitionRegistrations = new ArrayCollection();
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
    public function getRiders(): Collection
    {
        return $this->riders;
    }

    public function addRider(Rider $rider): static
    {
        if (!$this->riders->contains($rider)) {
            $this->riders->add($rider);
        }

        return $this;
    }

    public function removeRider(Rider $rider): static
    {
        $this->riders->removeElement($rider);

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

    public function getStatus(): string {
        return $this->status;
    }

    public function setStatus(string $status): static {
        
        $this->status = $status;
    
        return $this;
    }

    public function isActive(): bool {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function __toString(): string
    {
        return trim(sprintf('%s %s', $this->name ?? '', $this->affix ?? ''));
    }

    public function getPhotoFilename(): ?string
    {
        return $this->photoFilename;
    }

    public function setPhotoFilename(?string $photoFilename): static
    {
        $this->photoFilename = $photoFilename;

        return $this;
    }

    public const STATUS_CHOICES = [
        self::STATUS_ACTIVE,
        self::STATUS_RETIRED,
        self::STATUS_RESTING,
        self::STATUS_SOLD,
        self::STATUS_DECEASED,
        self::STATUS_ARCHIVED
    ];

    /**
     * @return Collection<int, CompetitionRegistration>
     */
    public function getCompetitionRegistrations(): Collection
    {
        return $this->competitionRegistrations;
    }

    public function addCompetitionRegistration(CompetitionRegistration $competitionRegistration): static
    {
        if (!$this->competitionRegistrations->contains($competitionRegistration)) {
            $this->competitionRegistrations->add($competitionRegistration);
            $competitionRegistration->setHorse($this);
        }

        return $this;
    }

    public function removeCompetitionRegistration(CompetitionRegistration $competitionRegistration): static
    {
        if ($this->competitionRegistrations->removeElement($competitionRegistration)) {
            // set the owning side to null (unless already changed)
            if ($competitionRegistration->getHorse() === $this) {
                $competitionRegistration->setHorse(null);
            }
        }

        return $this;
    }
}
