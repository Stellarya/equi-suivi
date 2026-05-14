<?php

namespace App\Entity;

use App\Repository\ProtocolMovementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProtocolMovementRepository::class)]
class ProtocolMovement
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'protocolMovements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProtocolFigure $protocolFigure = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $ordre = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $marker = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?array $graphData = null;

    #[ORM\Column(name: 'est_actif', type: 'boolean', nullable: false, options: ['default' => true])]
    private ?bool $estActif = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProtocolFigure(): ?ProtocolFigure
    {
        return $this->protocolFigure;
    }

    public function setProtocolFigure(?ProtocolFigure $protocolFigure): static
    {
        $this->protocolFigure = $protocolFigure;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getMarker(): ?string
    {
        return $this->marker;
    }

    public function setMarker(?string $marker): static
    {
        $this->marker = $marker;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getGraphData(): ?array
    {
        return $this->graphData;
    }

    public function setGraphData(?array $graphData): static
    {
        $this->graphData = $graphData;

        return $this;
    }

    public function isEstActif(): ?bool
    {
        return $this->estActif;
    }

    public function setEstActif(bool $estActif): static
    {
        $this->estActif = $estActif;

        return $this;
    }

     public function __toString(): string
    {
        return sprintf(
            '%s. %s - %s',
            $this->ordre ?? '',
            $this->marker ?? '',
            mb_strimwidth($this->description ?? '', 0, 60, '...')
        );
    }
}
