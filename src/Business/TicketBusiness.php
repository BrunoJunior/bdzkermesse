<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 24/08/2018
 * Time: 16:37
 */

namespace App\Business;

use App\DataTransfer\TicketRow;
use App\Entity\Depense;
use App\Entity\Etablissement;
use App\Entity\Kermesse;
use App\Entity\Remboursement;
use App\Entity\Ticket;
use App\Enum\RemboursementEtatEnum;
use App\Enum\TicketEtatEnum;
use App\Exception\BusinessException;
use App\Service\FileUploader;
use App\Service\TicketRowGenerator;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use SimpleEnum\Exception\UnknownEumException;
use Stringy\Stringy;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

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
     * @throws UnknownEumException
     */
    public function synchroniserEtats(Ticket $ticket, Remboursement $remboursement)
    {
        $ticket->setEtat(RemboursementEtatEnum::getInstance($remboursement->getEtat())->getEtatTicket()->getKey());
        $ticket->setRemboursement($remboursement);
        $this->em->persist($ticket);
    }

    /**
     * @param Kermesse|null $kermesse
     * @param Etablissement $etablissement
     * @return string
     */
    public function getDuplicataDir(?Kermesse $kermesse, Etablissement $etablissement)
    {
        $dir = $kermesse ? $kermesse->getId() : $etablissement->getUsername();
        return $this->container->getParameter('duplicata_dir') . '/' . $dir;
    }

    /**
     * Créer ticket
     * @param Ticket $ticket
     * @throws DBALException
     * @throws UnknownEumException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function creer(Ticket $ticket)
    {
        $file = $ticket->getDuplicata();
        if ($file) {
            $filename = $this->uploader->upload($file, $this->getDuplicataDir($ticket->getKermesse(), $ticket->getEtablissement()));
            $ticket->setDuplicata($filename);
        }
        $this->em->persist($ticket);
        $this->em->flush();

        $this->bMembre->envoyerEmail($ticket->getMembre(), "Kermesse - Dépense créée", 'bdzkermesse@bdesprez.com', 'ticket_cree', [
            'ticket' => $this->generator->generate($ticket, 0, array_map(function (Depense $depense) {
                return $depense->getActivite()->getNom();
            }, $ticket->getDepenses()->toArray()))
        ], $this->bMembre->getGestionnaires($ticket->getEtablissement()));
    }

    /**
     * Modification ticket
     * @param Ticket $ticket
     * @param null|string $duplicataInitial
     */
    public function modifier(Ticket $ticket, ?string $duplicataInitial)
    {
        $dir = $this->getDuplicataDir($ticket->getKermesse(), $ticket->getEtablissement());
        $file = $ticket->getDuplicata();
        if ($file) {
            $filename = $this->uploader->upload($file, $dir);
            $ticket->setDuplicata($filename);
            if ($duplicataInitial) {
                unlink($dir . '/' . $duplicataInitial);
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
     * @throws BusinessException
     */
    public function supprimer(Ticket $ticket)
    {
        if ($ticket->getEtat() > TicketEtatEnum::A_REMBOURSER) {
            throw new BusinessException('Suppression non autorisée');
        }
        $this->supprimerDuplicata($ticket);
        $this->em->remove($ticket);
        $this->em->flush();
    }

    /**
     * Suppression d'un duplicata
     * @param Ticket $ticket
     */
    public function supprimerDuplicata(Ticket $ticket)
    {
        if ($ticket->getDuplicata()) {
            $path = $this->getDuplicataPath($ticket);
            if (file_exists($path)) {
                unlink($this->getDuplicataPath($ticket));
            }
            $ticket->setDuplicata(null);
            $this->em->persist($ticket);
            $this->em->flush();
        }
    }

    /**
     * @param Ticket $ticket
     * @return string
     */
    public function getDuplicataPath(Ticket $ticket)
    {
        $duplicata = $ticket->getDuplicata();
        if ($duplicata instanceof File) {
            return $duplicata->getRealPath();
        }
        return $this->getDuplicataDir($ticket->getKermesse(), $ticket->getEtablissement()) . '/' . $ticket->getDuplicata();
    }

    /**
     * @param Ticket $ticket
     * @return bool
     */
    public function isDuplicataImage(Ticket $ticket)
    {
        $duplicata = $ticket->getDuplicata();
        if (!$duplicata) {
            return false;
        }
        if (!$duplicata instanceof File) {
            $duplicata = new File($this->getDuplicataPath($ticket));
        }
        return Stringy::create($duplicata->getMimeType())->startsWith('image/');
    }

    /**
     * @param Ticket $ticket
     * @return TicketRow
     * @throws DBALException
     * @throws UnknownEumException
     */
    public function getRow(Ticket $ticket)
    {
        return $this->generator->generate($ticket);
    }
}
