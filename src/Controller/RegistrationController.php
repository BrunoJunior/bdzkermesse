<?php

namespace App\Controller;

use App\Form\EtablissementType;
use App\Service\PasswordResetter;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class RegistrationController
 * @package App\Controller
 * @author bruno <bdesprez@thalassa.fr>
 */
class RegistrationController extends MyController
{

    /**
     * @Route("/etablissement/edit", name="editer_etablissement")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
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

    /**
     * @Route("/demande-inscription/{id}/validation/{key}", name="validation_email")
     * @param Request $request
     * @param int $id
     * @param string $key
     * @param PasswordResetter $resetter
     * @return Response
     * @throws NonUniqueResultException
     */
    public function validerEmail(Request $request, int $id, string $key, PasswordResetter $resetter): Response {
        $form = $resetter->validerEmail($request, $id, $key);
        if ($form === null) { // Formulaire null = il a été traité
            $this->addFlash('success', "Votre compte a bien été validé !");
            return $this->redirectToRoute('security_login');
        }
        return $this->render(
            'registration/reset_password.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/etablissement/{id}/reset-password/{key}", name="reset_pwd")
     * @param Request $request
     * @param int $id
     * @param string $key
     * @param PasswordResetter $resetter
     * @return Response
     * @throws NonUniqueResultException
     */
    public function resetPassword(Request $request, int $id, string $key, PasswordResetter $resetter): Response {
        $form = $resetter->reset($request, $id, $key);
        if ($form === null) { // Formulaire null = il a été traité
            $this->addFlash('success', "Votre mot de passe a été mis à jour !");
            return $this->redirectToRoute('security_login');
        }
        return $this->render(
            'registration/reset_password.html.twig',
            array('form' => $form->createView())
        );
    }
}
