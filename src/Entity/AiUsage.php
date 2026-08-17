<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AiUsageRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: AiUsageRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_ai_usage_day', columns: ['usage_date'])]
class AiUsage
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $usageDate = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $callCount = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsageDate(): ?\DateTimeImmutable
    {
        return $this->usageDate;
    }

    public function setUsageDate(\DateTimeImmutable $usageDate): static
    {
        $this->usageDate = $usageDate;

        return $this;
    }

    public function getCallCount(): int
    {
        return $this->callCount;
    }

    public function setCallCount(int $callCount): static
    {
        $this->callCount = $callCount;

        return $this;
    }
}