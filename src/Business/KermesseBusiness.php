<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 19/03/2019
 * Time: 16:34
 */

namespace App\Business;

use App\DataTransfer\LigneComptable;
use App\Entity\Kermesse;
use App\Entity\Recette;
use App\Entity\Ticket;
use App\Exception\BusinessException;
use App\Repository\RecetteRepository;
use App\Service\LigneComptableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class KermesseBusiness
 * @package App\Business
 */
class KermesseBusiness
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var RecetteRepository
     */
    private $rRecette;

    /**
     * @var LigneComptableGenerator
     */
    private $lcGenerator;

    /**
     * KermesseBusiness constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container, RecetteRepository $rRecette, LigneComptableGenerator $lcGenerator)
    {
        $this->container = $container;
        $this->rRecette = $rRecette;
        $this->lcGenerator = $lcGenerator;
    }

    /**
     * Génération de l'export comptable d'une kermesse
     * @param Kermesse $kermesse
     * @return string
     * @throws BusinessException
     */
    public function genererExportComptable(Kermesse $kermesse)
    {
        $filename = $this->getExportComptaDir() . '/' . $kermesse->getId() . '.csv';
        $hFile = fopen($filename, 'w');
        if  (!$hFile) {
            throw new BusinessException("Impossible d'écrire le fichier d'export");
        }
        // Récupération des lignes comptables
        $lignesComptables = array_merge(
            array_map(function (Ticket $ticket) {
                return $this->lcGenerator->fromTicket($ticket);
            }, $kermesse->getTickets()->toArray()),
            array_map(function (Recette $recette) {
                return $this->lcGenerator->fromRecette($recette);
            }, $this->rRecette->findByKermesse($kermesse, 'date', true))
        );
        // Tri par date
        usort($lignesComptables, function (LigneComptable $ligne1, LigneComptable $ligne2) {
            return $ligne1->comparerAvec($ligne2);
        });
        // Écriture entête
        fputcsv($hFile, ['Date', 'Libellé', 'Débit', 'Crédit']);
        // Écriture du fichier ligne à ligne
        array_walk($lignesComptables, function (LigneComptable $ligne) use ($hFile) {
            fputcsv($hFile, $ligne->formatToCSV());
        });
        // Fermeture du fichier
        fclose($hFile);
        return $filename;
    }

    /**
     * @param Kermesse $kermesse
     * @return string
     */
    public function getExportComptaDir()
    {
        $repertoire = $this->container->getParameter('exports_dir') . '/compta';
        if (!file_exists($repertoire)) {
            mkdir($repertoire, 0777, true);
        }
        return $repertoire;
    }
}