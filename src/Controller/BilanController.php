<?php

namespace App\Controller;

use App\DataTransfer\PlageHoraire;
use App\Entity\Etablissement;
use App\Service\BilanGeneratorService;
use DateTimeImmutable;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class BilanController extends MyController
{

    /**
     * @Route("/bilan/{annee<\d+>?}", name="show_bilan")
     * @param int|null $annee
     * @param BilanGeneratorService $generator
     * @return Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function index(?int $annee, BilanGeneratorService $generator): Response
    {
        $etablissement = $this->getUser();
        if (!$etablissement instanceof Etablissement) {
            throw new NotFoundHttpException("La page demandée n'existe pas !");
        }
        $now = new DateTimeImmutable();
        $date = $now;
        if ($annee) {
            $date = $now->setDate($annee, (int) $now->format('n'), (int) $now->format('d'));
        }
        $periode = PlageHoraire::createAnneeScolaire($date);
        return $this->render(
            'bilan/index.html.twig',
            [
                'periode' => $periode,
                'annee' => (int) $date->format('Y'),
                'courante' => $now >= $periode->getDebut() && $now < $periode->getFin(),
                'bilan' => $generator->generer($etablissement, $date),
                'menu' => $this->getMenu(null, self::MENU_BILAN)
            ]
        );
    }
}
