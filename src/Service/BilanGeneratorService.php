<?php

namespace App\Service;

use App\DataTransfer\BilanDto;
use App\DataTransfer\ILigneBilan;
use App\DataTransfer\LigneBilanSimple;
use App\Entity\Activite;
use App\Entity\Etablissement;
use App\Entity\Kermesse;
use App\Repository\ActiviteRepository;
use App\Repository\KermesseRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Exception;

class BilanGeneratorService
{
    /**
     * @var ActiviteRepository
     */
    private $rActivite;

    /**
     * @var KermesseRepository
     */
    private $rKermesse;

    /**
     * ActiviteCard constructor.
     * @param ActiviteRepository $rActivite
     * @param KermesseRepository $rKermesse
     */
    public function __construct(ActiviteRepository $rActivite, KermesseRepository $rKermesse)
    {
        $this->rActivite = $rActivite;
        $this->rKermesse = $rKermesse;
    }

    /**
     * @param Activite $activite
     * @param int $depense
     * @param int $recette
     * @param int $nbTickets
     * @return ILigneBilan
     */
    private function generateLigne(Activite $activite, int $depense = 0, int $recette = 0, int $nbTickets = 0): ILigneBilan
    {
        $kermesse = $activite->getKermesse();
        $montantTicket = $kermesse ? $kermesse->getMontantTicket() : 0;
        return new LigneBilanSimple($activite->getNom(), ILigneBilan::TYPE_NORMALE, $depense, $recette + ($nbTickets * $montantTicket));
    }

    /**
     * @param BilanDto $bilan
     * @param array|Activite[] $activites
     * @param ArrayCollection $totaux
     * @param int $montantTickets
     * @return BilanDto
     */
    private function completerBilan(BilanDto $bilan, array $activites, ArrayCollection $totaux, int $montantTickets = 0): BilanDto
    {
        $montantTicketsRestants = 0;
        foreach ($activites as $activite) {
            $key = '' . $activite->getId();
            // Caisse centrale - on ne conserve que les dépenses dans le bilan
            // Les recettes sont mises de côtés pour éviter de les compter deux fois
            // (1 fois en caisse centrale et 1 fois en calculant avec les tickets utilisés sur les activités)
            if ($activite->isCaisseCentrale()) {
                $montantTicketsRestants += ($totaux->get($key)['recette'] ?: 0);
                $bilan->addLigne($this->generateLigne($activite, $totaux->get($key)['depense']));
                continue;
            }
            if ($totaux->containsKey($key)) {
                $nbTickets = $totaux->get($key)['nombre_ticket'] ?: 0;
                $montantTicketsRestants -= ($nbTickets * $montantTickets);
                $bilan->addLigne($this->generateLigne($activite, $totaux->get($key)['depense'], $totaux->get($key)['recette'], $nbTickets));
            } else {
                $bilan->addLigne($this->generateLigne($activite));
            }
        }
        if ($montantTicketsRestants !== 0) {
            $bilan->addLigne((new LigneBilanSimple("Tickets non utilisés", ILigneBilan::TYPE_NORMALE, 0, $montantTicketsRestants)));
        }
        return $bilan;
    }

    /**
     * Génération du bilan pour une année spécifiaque
     * @param Etablissement $etablissement
     * @param DateTimeInterface|null $date
     * @return BilanDto
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function generer(Etablissement $etablissement, ?DateTimeInterface $date = null): BilanDto
    {
        $kermesse = $this->rKermesse->findOneByDate($etablissement, $date);
        $bilan = new BilanDto();
        if ($kermesse instanceof Kermesse) {
            $this->completerBilan(
                $bilan,
                $this->rActivite->findByKermesseId($kermesse->getId()),
                $this->rActivite->getTotaux($kermesse), $kermesse->getMontantTicket()
            )->addSousTotal("Kermesse");
        }
        return $this->completerBilan(
            $bilan,
            $this->rActivite->getListeAutres($etablissement, $date),
            $this->rActivite->getTotauxActions($etablissement, $date)
        )->addSousTotal("Actions");
    }
}
