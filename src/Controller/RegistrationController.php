<?php

namespace App\Controller;

use App\Entity\Etablissement;
use App\Entity\Membre;
use App\Form\EtablissementType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends MyController
{
    /**
     * @Route("/registration", name="registration")
     */
    public function index(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $etablissement = new Etablissement();
        $form = $this->createForm(EtablissementType::class, $etablissement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($etablissement, $etablissement->getPassword());
            $etablissement->setPassword($password);
            // Ajout d'un membre par défaut (Membre établissement)
            $partiesNom = explode(' ', $etablissement->getNom());
            $dftMembre = new Membre();
            $dftMembre->setDefaut(true);
            $dftMembre->setEmail("");
            $dftMembre->setPrenom(array_shift($partiesNom));
            $dftMembre->setNom(implode(' ', $partiesNom));
            $etablissement->addMembre($dftMembre);
            // On enregistre l'utilisateur dans la base
            $em = $this->getDoctrine()->getManager();
            $em->persist($dftMembre);
            $em->persist($etablissement);
            $em->flush();
            return $this->redirectToRoute('security_login');
        }
        return $this->render(
            'registration/nouveau.html.twig',
            array('form' => $form->createView())
        );
    }
    /**
     * @Route("/etablissement/edit", name="editer_etablissement")
     */
    public function editer(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $etablissement = $this->getUser();
        $oldPassword = $etablissement->getPassword();
        $form = $this->createForm(EtablissementType::class, $etablissement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($etablissement->getPassword() === '') {
                $password = $oldPassword;
            } else {
                $password = $passwordEncoder->encodePassword($etablissement, $etablissement->getPassword());
            }
            $etablissement->setPassword($password);
            // On enregistre l'utilisateur dans la base
            $em = $this->getDoctrine()->getManager();
            $em->persist($etablissement);
            $em->flush();
            return $this->redirectToRoute('index');
        }
        return $this->render(
            'registration/edition.html.twig',
            array('form' => $form->createView(), 'menu' => $this->getMenu(null, static::MENU_ACCUEIL))
        );
    }
}
