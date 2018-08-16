<?php

namespace App\Controller;

use App\DataTransfer\KermesseCard;
use App\Entity\Kermesse;
use App\Repository\KermesseRepository;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends MyController
{
    /**
     * @Route("/", name="index")
     * @param KermesseRepository $rKermesse
     * @param KermesseCard $cardService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function kermessesListe(KermesseRepository $rKermesse, KermesseCard $cardService)
    {
        return $this->render('index/index.html.twig', [
            'cards' => array_map(function(Kermesse $kermesse) use($cardService) {
                    return $cardService->generer($kermesse);
                }, $rKermesse->findByEtablissementOrderByAnnee($this->getUser())),
            'menu' => $this->getMenu(null, static::MENU_ACCUEIL)
        ]);
    }
}
