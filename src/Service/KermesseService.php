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
            $caisseCentrale->setAccepteMonnaie(true);
            $caisseCentrale->setAccepteTickets(false);
            $this->entityManager->persist($caisseCentrale);
        } elseif ($caisseCentrale instanceof Activite && $montantTicket <= 0) {
            $this->entityManager->remove($caisseCentrale);
        }
        $this->entityManager->flush();
        return $this;
    }
}