<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Entity\Kermesse;
use App\Entity\Recette;
use App\Form\RecetteType;
use App\Repository\ActiviteRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     * @param Request $request
     * @param string $action
     * @param Recette $recette
     * @param Kermesse|null $kermesse
     * @param Activite|null $activite
     * @return RedirectResponse|Response
     */
    private function saveRecette(Request $request, string $action, ?Recette $recette, ?Kermesse $kermesse = null, Activite $activite = null): Response
    {
        $recette = $recette ?: new Recette();
        $activite = $recette->getActivite() ?: $activite;
        if ($activite) {
            $recette->setActivite($activite);
            $kermesse = $activite->getKermesse() ?: $kermesse;
        }
        $form = $this->createForm(RecetteType::class, $recette, [
            'action' => $action,
            'kermesse' => $kermesse,
            'activite' => $activite,
            'acceptent_tickets' => $this->rActivite->getListeIdAccepteTickets($kermesse)
        ]);
        $form->handleRequest($request);
        $recette->setReportStock($recette->isReportStock() ?: false);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($recette);
            $em->flush();
            return $this->reponseModal("Recette enregistrée avec succès !");
        }
        return $this->render(
            'recette/form.html.twig',
            ['form' => $form->createView(), 'kermesse' => $kermesse, 'activite' => $activite]
        );
    }

    /**
     * @Route("/kermesses/{id}/recettes/new", name="nouvelle_recette")
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function nouvelleRecette(Kermesse $kermesse, Request $request)
    {
        return $this->saveRecette($request, $this->generateUrl('nouvelle_recette', ['id' => $kermesse->getId()]), null, $kermesse);
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
        return $this->saveRecette(
            $request,
            $this->generateUrl('nouvelle_recette_activite', ['id' => $activite->getId()]),
            null,
            $activite->getKermesse(),
            $activite
        );
    }

    /**
     * @Route("/recettes/{id}/edit", name="editer_recette")
     * @Security("recette.isProprietaire(user)")
     * @param Recette $recette
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function editerRecette(Recette $recette, Request $request)
    {
        return $this->saveRecette(
            $request,
            $this->generateUrl('editer_recette', ['id' => $recette->getId()]),
            $recette
        );
    }

    /**
     * @Route("/recettes/{id}/supprimer", name="supprimer_recette")
     * @Security("recette.isProprietaire(user)")
     * @param Recette $recette
     * @return Response
     */
    public function supprimerRecette(Recette $recette)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($recette);
        $em->flush();
        return $this->reponseModal('Recette supprimée !');
    }
}
