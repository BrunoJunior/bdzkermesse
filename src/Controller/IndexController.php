<?php

namespace App\Controller;

use App\Entity\Kermesse;
use App\Helper\HFloat;
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
        $kermesses = $kermesseRepo->findByEtablissementOrderByAnnee($this->getUser());
        $montants = [];
        foreach ($kermesses as $kermesse) {
            $montants[$kermesse->getId()]['ticket'] = HFloat::getInstance($kermesse->getMontantTicket() / 100.0)->getMontantFormatFrancais();
            $montants[$kermesse->getId()]['recette'] = HFloat::getInstance($kermesse->getRecetteTotale() / 100.0)->getMontantFormatFrancais();
            $montants[$kermesse->getId()]['depense'] = HFloat::getInstance($kermesse->getDepenseTotale() / 100.0)->getMontantFormatFrancais();
            $montants[$kermesse->getId()]['balance'] = HFloat::getInstance($kermesse->getBalance() / 100.0)->getMontantFormatFrancais();
        }
        return $this->render('index/index.html.twig', [
            'kermesses' => $kermesses,
            'membres' => $this->getUser()->getMembres(),
            'montants' => $montants
        ]);
    }
}
