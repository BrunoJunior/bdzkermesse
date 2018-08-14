<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 30/07/2018
 * Time: 11:26
 */

namespace App\Service;


use App\Entity\Activite;
use App\Entity\Kermesse;
use App\Exception\ServiceException;
use App\Repository\ActiviteRepository;
use Doctrine\ORM\EntityManagerInterface;

class KermesseService
{
    /**
     * @var ActiviteRepository
     */
    private $rActivite;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Kermesse
     */
    private $kermesse;

    /**
     * KermesseService constructor.
     * @param ActiviteRepository $rActivite
     */
    public function __construct(ActiviteRepository $rActivite, EntityManagerInterface $entityManager)
    {
        $this->rActivite = $rActivite;
        $this->entityManager = $entityManager;
    }

    /**
     * @param Kermesse $kermesse
     * @return KermesseService
     */
    public function setKermesse(Kermesse $kermesse): self
    {
        $this->kermesse = $kermesse;
        return $this;
    }

    /**
     * Création automatique de la caisse centrale si le montant du ticket est défini
     * Si pas de montant de ticket suppression de la caisse centrale si existante
     * @return KermesseService
     * @throws ServiceException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function gererCaisseCentrale(): self
    {
        if (!$this->kermesse instanceof Kermesse) {
            throw new ServiceException('Kermesse non précisée !');
        }
        $caisseCentrale = $this->rActivite->findCaisseCentrale($this->kermesse);
        $montantTicket = $this->kermesse->getMontantTicket();
        if ($caisseCentrale === null && $montantTicket > 0) {
            $caisseCentrale = new Activite();
            $caisseCentrale->setNom(Activite::NOM_CAISSE_CENT);
            $caisseCentrale->setKermesse($this->kermesse);
            $caisseCentrale->setCaisseCentrale(true);
            $this->entityManager->persist($caisseCentrale);
        } elseif ($caisseCentrale instanceof Activite && $montantTicket <= 0) {
            $this->entityManager->remove($caisseCentrale);
            foreach ($this->kermesse->getActivites() as $activite) {
                $activite->setAccepteSeulementMonnaie();
                $this->entityManager->persist($activite);
            }
        }
        $this->entityManager->flush();
        return $this;
    }

    /**
     * Récupération des infos de la kermesse d'origine
     * @param Kermesse $origine
     * @return KermesseService
     * @throws ServiceException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function dupliquerInfos(Kermesse $origine): self
    {
        // Gestion caisse centrale
        $this->gererCaisseCentrale();
        // Init des mêmes membres actifs
        foreach ($origine->getMembres() as $membreActif) {
            $this->kermesse->addMembre($membreActif);
        }
        $this->entityManager->persist($this->kermesse);
        // Init des mêmes activités (sauf caisse centrale, gérée précédemment)
        foreach ($origine->getActivites() as $activite) {
            if (!$activite->isCaisseCentrale()) {
                $nouvelleActivite = clone $activite;
                $nouvelleActivite->setKermesse($this->kermesse);
                $this->entityManager->persist($nouvelleActivite);
            }
        }
        $this->entityManager->flush();
        return $this;
    }
}