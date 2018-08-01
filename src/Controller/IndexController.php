<?php

namespace App\Controller;

use App\Entity\Kermesse;
use App\Helper\Breadcrumb;
use App\Helper\HFloat;
use App\Helper\MenuLink;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends MyController
{
    /**
     * @Route("/", name="index")
     */
    public function kermessesListe()
    {
        $menu = Breadcrumb::getInstance(false)
            ->addLink(MenuLink::getInstance('Accueil', 'home', $this->generateUrl('index'))->setActive())
            ->addLink($this->getKermessesMenuLink())
            ->addLink(MenuLink::getInstance('Membres', 'users', $this->generateUrl('membres')));

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
            'montants' => $montants,
            'menu' => $menu
        ]);
    }
}
