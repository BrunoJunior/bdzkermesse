<?php

namespace App\DataTransfer;

use DateTimeInterface;

class LignePlanning
{
    /**
     * @var DateTimeInterface
     */
    private $date;

    /**
     * @var array|ActivitePlanning[]
     */
    private $activites = [];

    /**
     * @return DateTimeInterface
     */
    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @param DateTimeInterface $date
     * @return LignePlanning
     */
    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return ActivitePlanning[]|array
     */
    public function getActivites()
    {
        return $this->activites;
    }

    /**
     * @param ActivitePlanning $activite
     * @return LignePlanning
     */
    public function addActivite(ActivitePlanning $activite): self
    {
        $this->activites[] = $activite;
        return $this;
    }
}
