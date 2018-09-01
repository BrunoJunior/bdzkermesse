<?php

namespace App\Controller;

use App\Repository\KermesseRepository;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends MyController
{
    /**
     * @Route("/", name="index")
     * @param KermesseRepository $rKermesse
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function kermessesListe(KermesseRepository $rKermesse)
    {
        return $this->render('index/index.html.twig', [
            'kermesses' => $rKermesse->findByEtablissementOrderByAnnee($this->getUser()),
            'menu' => $this->getMenu(null, static::MENU_ACCUEIL)
        ]);
    }
}
