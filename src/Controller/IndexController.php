<?php

namespace App\Controller;

use App\Entity\Kermesse;
use App\Repository\KermesseRepository;
use App\Service\KermesseCardGenerator;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends MyController
{
    /**
     * @Route("/", name="index")
     * @param KermesseRepository $rKermesse
     * @param KermesseCardGenerator $cardGenerator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function kermessesListe(KermesseRepository $rKermesse, KermesseCardGenerator $cardGenerator)
    {
        return $this->render('index/index.html.twig', [
            'cards' => array_map(function(Kermesse $kermesse) use($cardGenerator) {
                    return $cardGenerator->generate($kermesse);
                }, $rKermesse->findByEtablissementOrderByAnnee($this->getUser())),
            'menu' => $this->getMenu(null, static::MENU_ACCUEIL)
        ]);
    }
}
