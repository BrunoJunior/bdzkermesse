<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 24/08/2018
 * Time: 16:37
 */

namespace App\Business;


use App\Entity\Kermesse;
use App\Entity\Remboursement;
use App\Entity\Ticket;
use App\Enum\RemboursementEtatEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TicketBusiness
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * RemboursementBusiness constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container)
    {
        $this->em = $entityManager;
        $this->container = $container;
    }

    /**
     * @param Ticket $ticket
     * @param Remboursement $remboursement
     * @throws \SimpleEnum\Exception\UnknownEumException
     */
    public function synchroniserEtats(Ticket $ticket, Remboursement $remboursement)
    {
        $ticket->setEtat(RemboursementEtatEnum::getInstance($remboursement->getEtat())->getEtatTicket()->getKey());
        $ticket->setRemboursement($remboursement);
        $this->em->persist($ticket);
    }

    /**
     * @param Kermesse $kermesse
     * @return string
     */
    public function getDuplicataDir(Kermesse $kermesse)
    {
        return $this->container->getParameter('duplicata_dir') . '/' . $kermesse->getId();
    }

    /**
     * Suppression d'un ticket
     * @param Ticket $ticket
     */
    public function supprimer(Ticket $ticket)
    {
        if ($ticket->getDuplicata()) {
            unlink($this->getDuplicataDir($ticket->getKermesse()) . '/' . $ticket->getDuplicata());
        }
        $this->em->remove($ticket);
        $this->em->flush();
    }
}