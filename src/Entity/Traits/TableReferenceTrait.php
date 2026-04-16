<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait TableReferenceTrait {
    #[ORM\Column(type:'string', lenght: 50, options: ['default' => '-'])]
    private string $mnemonique = "-";

    #[ORM\Column(name: 'est_actif', type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $estActif = true;

    /**
     * @return string
     */
    public function getMnemonique(): string
    {
        return $this->mnemonique;
    }

    /**
     * @param string $mnemonique
     */
    public function setMnemonique(string $mnemonique): void
    {
        $this->mnemonique = $mnemonique;
    }

    /**
     * @return bool
     */
    public function getEstActif(): bool
    {
        return $this->estActif;
    }

    /**
     * @param bool $estActif
     */
    public function setEstActif(bool $estActif): void
    {
        $this->estActif = $estActif;
    }
}