<?php

namespace App\Controller;

use App\Entity\Kermesse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IndexController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function kermessesListe()
    {
        $kermesseRepo = $this->getDoctrine()->getRepository(Kermesse::class);
        return $this->render('index/index.html.twig', [
            'kermesses' => $kermesseRepo->findByEtablissementOrderByAnnee($this->getUser()),
            'membres' => $this->getUser()->getMembres()
        ]);
    }
}
