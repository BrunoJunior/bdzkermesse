<?php

namespace App\Controller;

use App\Entity\Kermesse;
use App\Form\KermesseType;
use App\Helper\HFloat;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class KermesseController extends Controller
{
    /**
     * @Route("/kermesse/new", name="nouvelle_kermesse")
     */
    public function nouvelleKermesse(Request $request)
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
            return $this->redirectToRoute('index');
        }
        return $this->render(
            'kermesse/nouvelle.html.twig',
            array('form' => $form->createView())
        );
    }
    /**
     * @Route("/kermesse/{id}/edit", name="editer_kermesse")
     */
    public function editerKermesse(Kermesse $kermesse, Request $request)
    {
        $form = $this->createForm(KermesseType::class, $kermesse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $kermesse->setEtablissement($this->getUser());
            // On enregistre l'utilisateur dans la base
            $em = $this->getDoctrine()->getManager();
            $em->persist($kermesse);
            $em->flush();
            return $this->redirectToRoute('index');
        }
        return $this->render(
            'kermesse/edition.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/kermesse/{id}", name="kermesse")
     */
    public function index(Kermesse $kermesse) {

        $recettesActivites = [];
        foreach ($kermesse->getActivites() as $activite) {
            $recettesActivites[$activite->getId()]['total'] = HFloat::getInstance($activite->getRecetteTotale() / 100.0)->getMontantFormatFrancais();
            $recettesActivites[$activite->getId()]['montant'] = HFloat::getInstance($activite->getMontantRecette() / 100.0)->getMontantFormatFrancais();
            $recettesActivites[$activite->getId()]['depense'] = HFloat::getInstance($activite->getMontantDepense() / 100.0)->getMontantFormatFrancais();
        }

        return $this->render(
            'kermesse/index.html.twig',
            [
                'kermesse' => $kermesse,
                'recettes' => $recettesActivites,
                'montantTicket' => number_format($kermesse->getMontantTicket() / 100.0, 2, ',', '.') . ' €'
            ]
        );
    }
}
