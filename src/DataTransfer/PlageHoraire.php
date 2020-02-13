<?php

namespace App\DataTransfer;

use App\Exception\PlageException;
use DateInterval;
use DateTime;
use DateTimeInterface;

class PlageHoraire
{
    /**
     * @var DateTimeInterface
     */
    protected $debut;

    /**
     * @var DateTimeInterface
     */
    protected $fin;

    /**
     * @return DateTimeInterface
     */
    public function getDebut(): DateTimeInterface
    {
        return $this->debut;
    }

    /**
     * @param DateTimeInterface $debut
     * @return self
     */
    public function setDebut(DateTimeInterface $debut): self
    {
        if ($this->fin !== null && $debut > $this->fin) {
            throw new PlageException("La date de début doit être inférieure à la date de fin");
        }
        $this->debut = $debut;
        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getFin(): DateTimeInterface
    {
        return $this->fin;
    }

    /**
     * @param DateTimeInterface $fin
     * @return self
     */
    public function setFin(DateTimeInterface $fin): self
    {
        if ($this->debut !== null && $fin < $this->debut) {
            throw new PlageException("La date de fin doit être supérieure à la date de début");
        }
        $this->fin = $fin;
        return $this;
    }

    /**
     * @param PlageHoraire $plage
     * @param bool $arrondir
     * @return $this
     */
    protected function recalculerExtremumAvecAutrePlage(PlageHoraire $plage, bool $arrondir = false): self
    {
        return $this->recalculerExtremum($plage->getDebut(), $plage->getFin(), $arrondir);
    }

    /**
     * @param DateTimeInterface|null $debut
     * @param DateTimeInterface|null $fin
     * @param bool $arrondir Arrondir à l'heure ?
     * @return $this
     */
    protected function recalculerExtremum(?DateTimeInterface $debut, ?DateTimeInterface $fin, bool $arrondir = false): self
    {
        if ($this->debut === null || $this->debut > $debut) {
            $newDebut = DateTime::createFromFormat(DateTimeInterface::ATOM, $debut->format(DateTimeInterface::ATOM));
            $minutes = (int) $newDebut->format('i');
            if ($arrondir && $minutes > 30) {// Arrondir à la demi-heure inférieure
                $this->debut = $newDebut->setTime((int) $newDebut->format('G'), 30, 0);
            } elseif ($arrondir && $minutes < 30) {// Arrondir à l'heure inférieure
                $this->debut = $newDebut->setTime((int) $newDebut->format('G'), 0, 0);
            } else {
                $this->debut = $debut;
            }
        }
        if ($this->fin === null || $this->fin < $fin) {
            $newFin = DateTime::createFromFormat(DateTimeInterface::ATOM, $fin->format(DateTimeInterface::ATOM));
            $minutes = (int) $newFin->format('i');
            if ($arrondir && $minutes > 0 && $minutes < 30) { // Arrondir à la demi-heure supérieure
                $this->fin = $newFin->setTime((int) $newFin->format('G'), 30, 0);
            } elseif ($arrondir && $minutes > 30) { // Arrondir à l'heure supérieure
                $this->fin = $newFin->setTime( 1 + (int) $newFin->format('G'), 0, 0);
            } else {
                $this->fin = $fin;
            }
        }
        return $this;
    }

    /**
     * @param DateTimeInterface|null $debut
     * @param DateTimeInterface|null $fin
     * @return bool
     */
    protected function chevauche(?DateTimeInterface $debut, ?DateTimeInterface $fin): bool
    {
        // Avant $this->debut
        if ($this->debut !== null && $fin !== null && $fin <= $this->debut) {
            return false;
        }
        // Après $this->fin
        if ($this->fin !== null && $debut !== null && $debut >= $this->fin) {
            return false;
        }
        // Tous les autres cas = chevauchement
        return true;
    }

    /**
     * @param PlageHoraire $plage
     * @return bool
     */
    protected function chevaucheAutrePlage(PlageHoraire $plage): bool
    {
        return $this->chevauche($plage->getDebut(), $plage->getFin());
    }

    /**
     * Intervale entre début et fin
     * @return DateInterval
     */
    public function calculerIntervale(): DateInterval
    {
        return $this->fin->diff($this->debut);
    }

    /**
     * La taille de la plage horaire en secondes
     * @return int
     */
    public function getTaillePlage(): int
    {
        return $this->fin->getTimestamp() - $this->debut->getTimestamp();
    }
}
