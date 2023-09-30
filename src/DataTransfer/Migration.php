<?php

namespace App\DataTransfer;

use App\Entity\InscriptionBenevole;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Migration
{
    /**
     * @var Collection|InscriptionBenevole[]
     */
    private $benevoles;

    public function __construct()
    {
        $this->benevoles = new ArrayCollection();
    }

    /**
     * @return InscriptionBenevole[]|Collection
     */
    public function getBenevoles()
    {
        return $this->benevoles;
    }

    /**
     * @param InscriptionBenevole $benevole
     * @return $this
     */
    public function addBenevole(InscriptionBenevole $benevole): self
    {
        $this->benevoles->add($benevole);
        return $this;
    }

}
