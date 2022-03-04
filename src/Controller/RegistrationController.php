<?php

namespace App\Controller;

use App\Enum\InscriptionStatutEnum;
use App\Form\EtablissementType;
use App\Form\ResetPasswordType;
use App\Repository\EtablissementRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    /**
     * @Route("/registration/{id}/reset-password/{key}", name="reset_pwd")
     * @param Request $request
     * @param int $id
     * @param string $key
     * @param EtablissementRepository $rEtab
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     * @throws NonUniqueResultException
     */
    public function resetPassword(Request $request, int $id, string $key, EtablissementRepository $rEtab, UserPasswordEncoderInterface $passwordEncoder): Response {
        $etablissement = $rEtab->findOneByIdAndKey($id, $key);
        if (null === $etablissement) {
            throw new NotFoundHttpException();
        }
        $form = $this->createForm(ResetPasswordType::class, null, [
            'action' => $this->generateUrl('reset_pwd', ['id' => $id, 'key' => $key])
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $etablissement->setPassword($passwordEncoder->encodePassword($etablissement, $form->get('password')->getData()));
            $etablissement->setResetPwdKey(null);
            $em = $this->getDoctrine()->getManager();
            $demandeInsc = $etablissement->getOriginInscription();
            // Lors d'une demande de réinit du mot de passe, si la demande d'inscription est en attente de validation,
            // on considère qu'elle est désormais validée, car le 1er mot de passe étant généré aléatoirement,
            // la 1ère réinitialisation vaut comme validation de l'adresse e-mail
            if (null !== $demandeInsc && $demandeInsc->getState() === InscriptionStatutEnum::A_VALIDER) {
                $demandeInsc->setState(InscriptionStatutEnum::VALIDEE);
                $em->persist($demandeInsc);
            }
            $em->persist($etablissement);
            $em->flush();
            $this->addFlash('success', "Votre mot de passe a été mis à jour !");
            return $this->redirectToRoute('security_login');
        }
        return $this->render(
            'registration/reset_password.html.twig',
            array('form' => $form->createView())
        );
    }
}
