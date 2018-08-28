<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Entity\Kermesse;
use App\Entity\Recette;
use App\Form\RecetteType;
use App\Repository\ActiviteRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class RecetteController extends MyController
{

    /**
     * @var ActiviteRepository
     */
    private $rActivite;

    /**
     * RecetteController constructor.
     * @param LoggerInterface $logger
     * @param ActiviteRepository $rActivite
     */
    public function __construct(LoggerInterface $logger, ActiviteRepository $rActivite)
    {
        parent::__construct($logger);
        $this->rActivite = $rActivite;
    }

    /**
     * @param Recette $recette
     * @param Request $request
     * @param Kermesse $kermesse
     * @param Activite|null $activite
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    private function traiterNouvelleRecette(Recette $recette, Request $request, Kermesse $kermesse, Activite $activite = null):Response
    {
        $form = $this->createForm(RecetteType::class, $recette, [
            'kermesse' => $kermesse,
            'activite' => $activite,
            'acceptent_tickets' => $this->rActivite->getListeIdAccepteTickets($kermesse)
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($recette);
            $em->flush();
            $this->addFlash("success", "Recette enregistrée avec succès !");
            $route = $activite === null ? 'liste_recettes' : 'kermesse';
            return $this->redirectToRoute($route, ['id' => $kermesse->getId()]);
        }
        return $this->render(
            'recette/nouvelle.html.twig',
            [
                'form' => $form->createView(),
                'menu' => $this->getMenu($kermesse, $activite === null ? static::MENU_RECETTES : static::MENU_ACTIVITES)
            ]
        );
    }

    /**
     * @Route("/kermesses/{id}/recettes/new", name="nouvelle_recette")
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function nouvelleRecette(Kermesse $kermesse, Request $request)
    {
        $recette = new Recette();
        return $this->traiterNouvelleRecette($recette, $request, $kermesse);
    }

    /**
     * @Route("/activites/{id}/recette/new", name="nouvelle_recette_activite")
     * @Security("activite.isProprietaire(user)")
     * @param Activite $activite
     * @param Request $request
     * @return Response
     */
    public function nouvelleRecetteActivite(Activite $activite, Request $request):Response
    {
        $recette = new Recette();
        $recette->setActivite($activite);
        return $this->traiterNouvelleRecette($recette, $request, $activite->getKermesse(), $activite);
    }

    /**
     * @Route("/recettes/{id}/edit", name="editer_recette")
     * @Security("recette.isProprietaire(user)")
     * @param Recette $recette
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editerRecette(Recette $recette, Request $request)
    {
        $kermesse = $recette->getActivite()->getKermesse();
        $form = $this->createForm(RecetteType::class, $recette, [
            'kermesse' => $recette->getActivite()->getKermesse(),
            'acceptent_tickets' => $this->rActivite->getListeIdAccepteTickets($kermesse)
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($recette);
            $em->flush();
            $this->addFlash("success", "Recette enregistrée avec succès !");
            return $this->redirectToRoute('liste_recettes', ['id' => $kermesse->getId()]);
        }
        return $this->render(
            'recette/edition.html.twig',
            [
                'form' => $form->createView(),
                'menu' => $this->getMenu($kermesse, static::MENU_RECETTES)
            ]
        );
    }

    /**
     * @Route("/recettes/{id}/supprimer", name="supprimer_recette")
     * @Security("recette.isProprietaire(user)")
     * @param Recette $recette
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function supprimerRecette(Recette $recette)
    {
        $kermesse = $recette->getActivite()->getKermesse();
        $em = $this->getDoctrine()->getManager();
        $em->remove($recette);
        $em->flush();
        $this->addFlash("success", "Recette suprimmée !");
        return $this->redirectToRoute('liste_recettes', ['id' => $kermesse->getId()]);
    }
}
