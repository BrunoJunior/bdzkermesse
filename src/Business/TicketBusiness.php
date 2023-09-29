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
use App\Service\DuplicataDirectoryGenerator;
use App\Service\FileUploader;
use App\Service\TicketRowGenerator;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use SimpleEnum\Exception\UnknownEumException;
use Stringy\Stringy;
use Symfony\Component\HttpFoundation\File\File;

class TicketBusiness
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DuplicataDirectoryGenerator
     */
    private $duplicataDirGen;

    /**
     * RemboursementBusiness constructor.
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $uploader
     * @param MembreBusiness $bMembre
     * @param TicketRowGenerator $generator
     * @param LoggerInterface $logger
     * @param DuplicataDirectoryGenerator $duplicataDirGen
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FileUploader $uploader,
        MembreBusiness $bMembre,
        TicketRowGenerator $generator,
        LoggerInterface $logger,
        DuplicataDirectoryGenerator $duplicataDirGen
    ) {
        $this->em = $entityManager;
        $this->uploader = $uploader;
        $this->bMembre = $bMembre;
        $this->generator = $generator;
        $this->logger = $logger;
        $this->duplicataDirGen = $duplicataDirGen;
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
        return $this->duplicataDirGen->getDuplicataDir($kermesse, $etablissement);
    }

    /**
     * Créer ticket
     * @param Ticket $ticket
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

        try {
            $this->bMembre->envoyerEmail($ticket->getMembre(), "Kermesse - Dépense créée", 'bdzkermesse@bdesprez.com', 'ticket_cree', [
                'ticket' => $this->generator->generate($ticket, 0, array_map(function (Depense $depense) {
                    return $depense->getActivite()->getNom();
                }, $ticket->getDepenses()->toArray()))
            ], $this->bMembre->getGestionnaires($ticket->getEtablissement()));
        } catch (Exception $exception) {
            $this->logger->warning("Erreur lors de l'envoi à " . $ticket->getMembre()->getEmail() . " de l'email de création de dépense !", $exception->getTrace());
        }
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
        return $this->duplicataDirGen->getDuplicataPath($ticket);
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
