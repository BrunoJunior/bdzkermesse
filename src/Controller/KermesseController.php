<?php

namespace App\Controller;

use App\Entity\Kermesse;
use App\Form\KermesseType;
use App\Form\MembresKermesseType;
use App\Helper\Breadcrumb;
use App\Helper\HFloat;
use App\Helper\MenuLink;
use App\Service\KermesseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class KermesseController extends MyController
{

    /**
     * @param Kermesse $kermesse
     * @return Breadcrumb
     */
    private function getMenu(Kermesse $kermesse, string $activeLink = '') {
        $activeKermesse = empty($activeLink) ? $kermesse : null;
        return Breadcrumb::getInstance(false)
            ->addLink(MenuLink::getInstance('Accueil', 'home', $this->generateUrl('index')))
            ->addLink($this->getKermessesMenuLink($activeKermesse))
            ->addLink(MenuLink::getInstance('Membres', 'users', $this->generateUrl('membres')))
            ->addLink($this->getKermesseMenu($kermesse, $activeLink));
    }

    /**
     * @Route("/kermesse/new", name="nouvelle_kermesse")
     */
    public function nouvelleKermesse(Request $request, KermesseService $sKermesse)
    {
        $kermesse = new Kermesse();
        $form = $this->createForm(KermesseType::class, $kermesse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $kermesse->setEtablissement($this->getUser());
            // On enregistre l'utilisateur dans la base
            $em = $this->getDoctrine()->getManager();
            $em->persist($kermesse);
            $em->flush();
            $sKermesse->setKermesse($kermesse)->gererCaisseCentrale();
            return $this->redirectToRoute('index');
        }
        $menu = Breadcrumb::getInstance(false)
            ->addLink(MenuLink::getInstance('Accueil', 'home', $this->generateUrl('index'))->setActive())
            ->addLink($this->getKermessesMenuLink())
            ->addLink(MenuLink::getInstance('Membres', 'users', $this->generateUrl('membres')));
        return $this->render(
            'kermesse/nouvelle.html.twig',
            array('form' => $form->createView(),
                'menu' => $menu)
        );
    }

    /**
     * @Route("/kermesse/{id}/edit", name="editer_kermesse")
     */
    public function editerKermesse(Kermesse $kermesse, Request $request, KermesseService $sKermesse)
    {
        $form = $this->createForm(KermesseType::class, $kermesse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $kermesse->setEtablissement($this->getUser());
            // On enregistre l'utilisateur dans la base
            $em = $this->getDoctrine()->getManager();
            $em->persist($kermesse);
            $em->flush();
            $sKermesse->setKermesse($kermesse)->gererCaisseCentrale();
            return $this->redirectToRoute('index');
        }
        return $this->render(
            'kermesse/edition.html.twig',
            array('form' => $form->createView(),
                'menu' => $this->getMenu($kermesse))
        );
    }

    /**
     * @Route("/kermesse/{id}", name="kermesse")
     */
    public function index(Kermesse $kermesse) {

        $recettesActivites = [];
        foreach ($kermesse->getActivites() as $activite) {
            $recettesActivites[$activite->getId()]['total'] = HFloat::getInstance($activite->getBalance() / 100.0)->getMontantFormatFrancais();
            $recettesActivites[$activite->getId()]['montant'] = HFloat::getInstance($activite->getMontantRecette() / 100.0)->getMontantFormatFrancais();
            $recettesActivites[$activite->getId()]['depense'] = HFloat::getInstance($activite->getMontantDepense() / 100.0)->getMontantFormatFrancais();
        }
        return $this->render(
            'kermesse/index.html.twig',
            [
                'kermesse' => $kermesse,
                'recettes' => $recettesActivites,
                'montantTicket' => number_format($kermesse->getMontantTicket() / 100.0, 2, ',', '.') . ' €',
                'menu' => $this->getMenu($kermesse, static::MENU_ACTIVITES)
            ]
        );
    }

    /**
     * @Route("/kermesse/{id}/membres_actifs", name="membres_actifs")
     */
    public function definirMembresActifs(Kermesse $kermesse, Request $request)
    {
        $form = $this->createForm(MembresKermesseType::class, $kermesse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            // On enregistre l'utilisateur dans la base
            $em->persist($kermesse);
            $em->flush();
            return $this->redirectToRoute('kermesse', ['id' => $kermesse->getId()]);
        }
        return $this->render(
            'kermesse/membres.html.twig',
            [
                'form' => $form->createView(),
                'kermesse' => $kermesse,
                'menu' => $this->getMenu($kermesse, static::MENU_MEMBRES_ACTIFS)
            ]
        );
    }

    /**
     * @Route("/kermesse/{id}/tickets", name="liste_tickets")
     */
    public function listeTickets(Kermesse $kermesse)
    {
        return $this->render(
            'kermesse/tickets.html.twig',
            [
                'kermesse' => $kermesse,
                'menu' => $this->getMenu($kermesse, static::MENU_TICKETS)
            ]
        );
    }

    /**
     * @Route("/kermesse/{id}/recettes", name="liste_recettes")
     */
    public function listeRecettes(Kermesse $kermesse)
    {
        return $this->render(
            'kermesse/recettes.html.twig',
            [
                'kermesse' => $kermesse,
                'total' => HFloat::getInstance($kermesse->getRecetteTotale() / 100.0)->getMontantFormatFrancais(),
                'menu' => $this->getMenu($kermesse, static::MENU_RECETTES)
            ]
        );
    }
}
