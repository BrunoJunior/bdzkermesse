<?php

namespace App\Controller;

use App\Form\EtablissementType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends MyController
{

    /**
     * @Route("/etablissement/edit", name="editer_etablissement")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     */
    public function editer(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $etablissement = $this->getUser();
        $oldPassword = $etablissement->getPassword();
        $form = $this->createForm(EtablissementType::class, $etablissement, ['action' => $this->generateUrl('editer_etablissement')]);
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
            return $this->reponseModal();
        }
        return $this->render(
            'registration/edition_modal.html.twig',
            array('form' => $form->createView())
        );
    }
}
