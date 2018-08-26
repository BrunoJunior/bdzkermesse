<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 24/08/2018
 * Time: 16:37
 */

namespace App\Business;


use App\Entity\Depense;
use App\Entity\Kermesse;
use App\Entity\Remboursement;
use App\Entity\Ticket;
use App\Enum\RemboursementEtatEnum;
use App\Service\FileUploader;
use App\Service\TicketRowGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Stringy\Stringy;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;

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
     * @var FileUploader
     */
    private $uploader;

    /**
     * @var MembreBusiness
     */
    private $bMembre;

    /**
     * @var TicketRowGenerator
     */
    private $generator;

    /**
     * RemboursementBusiness constructor.
     * @param EntityManagerInterface $entityManager
     * @param ContainerInterface $container
     * @param FileUploader $uploader
     * @param MembreBusiness $bMembre
     * @param TicketRowGenerator $generator
     */
    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container, FileUploader $uploader, MembreBusiness $bMembre, TicketRowGenerator $generator)
    {
        $this->em = $entityManager;
        $this->container = $container;
        $this->uploader = $uploader;
        $this->bMembre = $bMembre;
        $this->generator = $generator;
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
     * Créer ticket
     * @param Ticket $ticket
     * @throws \SimpleEnum\Exception\UnknownEumException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function creer(Ticket $ticket)
    {
        $file = $ticket->getDuplicata();
        if ($file) {
            $filename = $this->uploader->upload($file, $this->getDuplicataDir($ticket->getKermesse()));
            $ticket->setDuplicata($filename);
        }
        $this->em->persist($ticket);
        $this->em->flush();

        $this->bMembre->envoyerEmail($ticket->getMembre(), 'BDZKermesse - Dépense créée', 'bdzkermesse@bdesprez.com', 'ticket_cree', [
            'ticket' => $this->generator->generate($ticket, 0, array_map(function (Depense $depense) {
                return $depense->getActivite()->getNom();
            }, $ticket->getDepenses()->toArray()))
        ]);
    }

    /**
     * Modification ticket
     * @param Ticket $ticket
     * @param null|string $duplicataInitial
     */
    public function modifier(Ticket $ticket, ?string $duplicataInitial)
    {
        $kermesse = $ticket->getKermesse();
        $file = $ticket->getDuplicata();
        if ($file) {
            $filename = $this->uploader->upload($file, $this->getDuplicataDir($kermesse));
            $ticket->setDuplicata($filename);
            if ($duplicataInitial) {
                unlink($this->getDuplicataDir($kermesse) . '/' . $duplicataInitial);
            }
        } elseif ($duplicataInitial) {
            $ticket->setDuplicata($duplicataInitial);
        }
        $this->em->persist($ticket);
        $this->em->flush();
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

    /**
     * @param Ticket $ticket
     * @return string
     */
    public function getDuplicataPath(Ticket $ticket)
    {
        return $this->getDuplicataDir($ticket->getKermesse()) . '/' . $ticket->getDuplicata();
    }

    /**
     * @param Ticket $ticket
     * @return bool
     */
    public function isDuplicataImage(Ticket $ticket)
    {
        if (!$ticket->getDuplicata())
        {
            return false;
        }
        return Stringy::create((new File($this->getDuplicataPath($ticket)))->getMimeType())->startsWith('image/');
    }
}