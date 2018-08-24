<?php

namespace App\Controller;

use App\DataTransfer\ContactDTO;
use App\DataTransfer\MembreRow;
use App\Entity\Membre;
use App\Form\ContactType;
use App\Form\MembreType;
use App\Repository\MembreRepository;
use App\Repository\RemboursementRepository;
use App\Service\ContactSender;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class MembreController extends MyController
{
    /**
     * @Route("/membres", name="membres")
     */
    public function index(MembreRepository $rMembre, RemboursementRepository $rRemboursement):Response
    {
        $etablissement = $this->getUser();
        $montantParMembre = $rMembre->getMontantsNonRemboursesParMembre($etablissement);
        $montantEnAttenteParMembre = $rMembre->getMontantsAttenteRemboursementParMembre($etablissement);
        $premiersRbsts = $rRemboursement->findPremierEnAttenteParMembre($etablissement);
        return $this->render('membre/index.html.twig', [
            'membres' => array_map(function (Membre $membre) use($montantParMembre, $montantEnAttenteParMembre, $premiersRbsts) {
                return MembreRow::getInstance($membre)
                    ->setMontantNonRembourse($montantParMembre->get($membre->getId()))
                    ->setMontantAttenteRemboursement($montantEnAttenteParMembre->get($membre->getId()))
                    ->setPremierRemboursementEnAttente($premiersRbsts->get($membre->getId()));
            }, $etablissement->getMembres()->toArray()),
            'menu' => $this->getMenu(null, static::MENU_MEMBRES)
        ]);
    }

    /**
     * @Route("/membres/new", name="nouveau_membre")
     * @param Request $request
     * @return Response
     */
    public function nouveauMembre(Request $request):Response
    {
        $membre = new Membre();
        $membre->setEtablissement($this->getUser());
        $form = $this->createForm(MembreType::class, $membre);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // On enregistre l'utilisateur dans la base
            $em = $this->getDoctrine()->getManager();
            $em->persist($membre);
            $em->flush();
            return $this->redirectToRoute('membres');
        }
        return $this->render(
            'membre/nouveau.html.twig',
            array('form' => $form->createView(),
                'menu' => $this->getMenu(null, static::MENU_MEMBRES))
        );
    }

    /**
     * @Route("/membres/{id}/edit", name="editer_membre")
     * @Security("membre.isProprietaire(user)")
     * @param Membre $membre
     * @param Request $request
     * @return Response
     */
    public function editerMembre(Membre $membre, Request $request):Response
    {
        $form = $this->createForm(MembreType::class, $membre);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // On enregistre l'utilisateur dans la base
            $em = $this->getDoctrine()->getManager();
            $em->persist($membre);
            $em->flush();
            return $this->redirectToRoute('membres');
        }
        return $this->render(
            'membre/edition.html.twig',
            array('form' => $form->createView(),
                'menu' => $this->getMenu(null, static::MENU_MEMBRES))
        );
    }

    /**
     * @Route("/membres/{id}/contact", name="contacter_membre")
     * @Security("membre.isProprietaire(user)")
     * @param Membre $membre
     * @param Request $request
     * @param ContactSender $sender
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function contacterMembre(Membre $membre, Request $request, ContactSender $sender): Response
    {
        $contact = new ContactDTO($membre);
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($sender->envoyer($contact) > 0) {
                $this->addFlash("success", "E-mail envoyé !");
            } else {
                $this->addFlash("danger", "Erreur lors de l'envoi du message !");
            }
            return $this->redirectToRoute('membres');
        }
        return $this->render(
            'membre/contact_form.html.twig',
            array('form' => $form->createView(),
                'menu' => $this->getMenu(null, static::MENU_MEMBRES))
        );
    }
}
