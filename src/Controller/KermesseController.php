<?php

namespace App\Controller;

use App\Entity\Kermesse;
use App\Form\KermesseType;
use App\Form\MembresKermesseType;
use App\Helper\HFloat;
use App\Repository\ActiviteRepository;
use App\Repository\RecetteRepository;
use App\Service\ActiviteCardGenerator;
use App\Service\KermesseService;
use App\Service\RecetteRowGenerator;
use App\Service\TicketRowGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class KermesseController extends MyController
{
    /**
     * @Route("/kermesse/new", name="nouvelle_kermesse")
     * @param Request $request
     * @param KermesseService $sKermesse
     * @return Response
     * @throws \App\Exception\ServiceException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function nouvelleKermesse(Request $request, KermesseService $sKermesse): Response
    {
        $kermesse = new Kermesse();
        $kermesse->setEtablissement($this->getUser());
        $form = $this->createForm(KermesseType::class, $kermesse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // On enregistre l'utilisateur dans la base
            $em = $this->getDoctrine()->getManager();
            $em->persist($kermesse);
            $em->flush();
            $sKermesse->setKermesse($kermesse)->gererCaisseCentrale();
            return $this->redirectToRoute('index');
        }
        return $this->render(
            'kermesse/nouvelle.html.twig',
            array('form' => $form->createView(),
                'menu' => $this->getMenu(null, static::MENU_ACCUEIL))
        );
    }

    /**
     * @Route("/kermesse/{id}/dupliquer", name="dupliquer_kermesse")
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @param Request $request
     * @param KermesseService $sKermesse
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @throws \Exception
     */
    public function dupliquerKermesse(Kermesse $kermesse, Request $request, KermesseService $sKermesse, EntityManagerInterface $entityManager): Response
    {
        $alert = null;
        $nouvelleKermesse = new Kermesse();
        $nouvelleKermesse->setEtablissement($this->getUser());
        $nouvelleKermesse->setMontantTicket($kermesse->getMontantTicket());
        $form = $this->createForm(KermesseType::class, $nouvelleKermesse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Débuter une trasaction, tout passe ou rien ne passe
            try {
                $entityManager->beginTransaction();
                $entityManager->persist($nouvelleKermesse);
                $entityManager->flush();
                $sKermesse->setKermesse($nouvelleKermesse)->dupliquerInfos($kermesse);
                $entityManager->commit();
                return $this->redirectToRoute('index');
            } catch (\Exception $exc) {
                $entityManager->rollback();
                throw $exc;
            }
        }
        return $this->render(
            'kermesse/edition.html.twig',
            [
                'form' => $form->createView(),
                'menu' => $this->getMenu(null, static::MENU_ACCUEIL),
                'global_alert' => $alert,
            ]
        );
    }

    /**
     * @Route("/kermesse/{id}/edit", name="editer_kermesse")
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @param Request $request
     * @param KermesseService $sKermesse
     * @return Response
     * @throws \App\Exception\ServiceException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function editerKermesse(Kermesse $kermesse, Request $request, KermesseService $sKermesse): Response
    {
        $form = $this->createForm(KermesseType::class, $kermesse);
        $kermesse->setEtablissement($this->getUser());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
     * @Route("/kermesse/{id}", name="kermesse", requirements={"id"="\d+"})
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @param ActiviteRepository $rActivite
     * @param ActiviteCardGenerator $activiteCardGenerator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Kermesse $kermesse, ActiviteRepository $rActivite, ActiviteCardGenerator $activiteCardGenerator): Response
    {
        $activiteCards = $activiteCardGenerator->generateList($kermesse);
        return $this->render(
            'kermesse/index.html.twig',
            [
                'kermesse' => $kermesse,
                'activiteCards' => $activiteCards,
                'menu' => $this->getMenu($kermesse, static::MENU_ACTIVITES)
            ]
        );
    }

    /**
     * @Route("/kermesse/{id}/membres_actifs", name="membres_actifs")
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @param Request $request
     * @return Response
     */
    public function definirMembresActifs(Kermesse $kermesse, Request $request): Response
    {
        $form = $this->createForm(MembresKermesseType::class, $kermesse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            // On enregistre l'utilisateur dans la base
            $em->persist($kermesse);
            $em->flush();
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
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @param TicketRowGenerator $ticketGenerator
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function listeTickets(Kermesse $kermesse, TicketRowGenerator $ticketGenerator): Response
    {
        return $this->render(
            'kermesse/tickets.html.twig',
            [
                'kermesse' => $kermesse,
                'rows' => $ticketGenerator->generateList($kermesse),
                'menu' => $this->getMenu($kermesse, static::MENU_TICKETS)
            ]
        );
    }

    /**
     * @Route("/kermesse/{id}/recettes", name="liste_recettes")
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @param RecetteRepository $rRecette
     * @param RecetteRowGenerator $rowGenerator
     * @return Response
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function listeRecettes(Kermesse $kermesse, RecetteRepository $rRecette, RecetteRowGenerator $rowGenerator): Response
    {
        $totaux = $rRecette->getTotauxPourKermesse($kermesse);
        $totaux['montant'] = HFloat::getInstance($totaux['montant'] / 100.0)->getMontantFormatFrancais();
        return $this->render(
            'kermesse/recettes.html.twig',
            [
                'kermesse' => $kermesse,
                'recettes' => $rowGenerator->generateList($kermesse),
                'total' => $totaux,
                'menu' => $this->getMenu($kermesse, static::MENU_RECETTES)
            ]
        );
    }
}
