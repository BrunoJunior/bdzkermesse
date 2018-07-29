<?php

namespace App\Controller;

use App\Entity\Kermesse;
use App\Form\KermesseType;
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
}
