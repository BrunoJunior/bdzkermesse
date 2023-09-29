<?php

namespace App\Service;

use App\Entity\Etablissement;
use App\Entity\Kermesse;
use App\Entity\Ticket;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;

class DuplicataDirectoryGenerator
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * DuplicataDirectoryGenerator constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
}
