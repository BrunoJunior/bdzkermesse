<?php

namespace App\Controller;

use App\Business\KermesseBusiness;
use App\DataTransfer\ActiviteCard;
use App\DataTransfer\ContactDTO;
use App\DataTransfer\Inscription;
use App\Entity\Activite;
use App\Entity\Benevole;
use App\Form\InscriptionType;
use App\Repository\BenevoleRepository;
use App\Repository\EtablissementRepository;
use App\Repository\InscriptionBenevoleRepository;
use App\Repository\KermesseRepository;
use App\Service\InscriptionBenevole;
use App\Service\MailgunSender;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class BenevoleController extends AbstractController
{

    /**
     * @Route("/benevoles/find/", name="find_benevole", methods={"GET"})
     * @param Request $request
     * @param BenevoleRepository $rBenevole
     * @return Response
     */
    public function getBenevole(Request $request, BenevoleRepository $rBenevole): Response
    {
        $email = $request->get('email');
        if ($email === null) {
            throw new BadRequestHttpException("Veuillez préciser une adresse email !");
        }
        $benevole = $rBenevole->findOneBy(['email' => $email]);
        if ($benevole === null) {
            throw new NotFoundHttpException("Bénévole inconnu !");
        }
        return $this->json([
            'id' => $benevole->getId(),
            'identite' => $benevole->getIdentite(),
            'portable' => $benevole->getPortable(),
            'email' => $benevole->getEmail()
        ]);
    }

    /**
     * @Route("/benevoles/{id<\d+>}/valider/{token}", name="valider_inscription", methods={"GET"})
     * @param Benevole $benevole
     * @param string $token
     * @param InscriptionBenevoleRepository $rInscBenevole
     * @return Response
     */
    public function validerInscription(Benevole $benevole, string $token, InscriptionBenevoleRepository $rInscBenevole): Response
    {
        $inscription = $rInscBenevole->findOneBy(['token' => $token, 'benevole' => $benevole]);
        if ($inscription === null) {
            throw new NotFoundHttpException("Cet email a déjà été validé !");
        }
        // On valide l'inscription
        $inscription->setValidee(true);
        $inscription->setToken(null);
        // L'email du bénévole a été validé au moins une fois
        $benevole->setEmailValide(true);
        $this->getDoctrine()->getManager()->flush();
        return $this->render('benevole/inscription_valide.html.twig', ['inscription' => $inscription]);
    }

    /**
     * @Route("/benevoles/{code}/", name="acces_benevole")
     * @param string $code
     * @param EtablissementRepository $rEtablissement
     * @param KermesseRepository $rKermesse
     * @param KermesseBusiness $mKermesse
     * @return Response
     * @throws NonUniqueResultException
     */
    public function accesBenevoles(string $code, EtablissementRepository $rEtablissement, KermesseRepository $rKermesse, KermesseBusiness $mKermesse): Response
    {
        $etablissement = $rEtablissement->findOneBy(['username' => $code]);
        if ($etablissement === null) {
            throw new NotFoundHttpException("La page demandée n'existe pas !");
        }
        $kermesse = $rKermesse->findCouranteByEtablissement($etablissement);

        return $this->render('benevole/index.html.twig', [
            'etablissement' => $etablissement,
            'kermesse' => $kermesse,
            'activites' => $kermesse->getActivites()->filter(function (Activite $activite) {
                return $activite->getCreneaux()->count() > 0;
            })->map(function (Activite $activite) {
                return new ActiviteCard($activite);
            }),
            'tauxInscription' => $kermesse ? round(100 * $mKermesse->getNbBenevolesInscrits($kermesse) / $mKermesse->getNbBenevolesRequis($kermesse)) : 0,
            'tauxInscriptionEnAttente' => $kermesse ? round(100 * $mKermesse->getNbBenevolesEnAttente($kermesse) / $mKermesse->getNbBenevolesRequis($kermesse)) : 0,
        ]);
    }

    /**
     * @Route("/benevoles/{code}/{id<\d+>}", name="inscription_benevole")
     * @param string $code
     * @param Activite $activite
     * @param Request $request
     * @param EtablissementRepository $rEtablissement
     * @param InscriptionBenevole $inscriptionSrv
     * @param MailgunSender $sender
     * @return Response
     * @throws Exception
     */
    public function inscriptionBenevole(string $code, Activite $activite, Request $request, EtablissementRepository $rEtablissement, InscriptionBenevole $inscriptionSrv, MailgunSender $sender): Response
    {
        $etablissement = $rEtablissement->findOneBy(['username' => $code]);
        if ($etablissement === null || $activite->getEtablissement() === null) {
            throw new NotFoundHttpException("La page demandée n'existe pas !");
        }
        if ($activite->getEtablissement()->getId() !== $etablissement->getId()) {
            throw new UnauthorizedHttpException("Vous n'êtes pas autorisée à voir cela !");
        }
        $inscription = new Inscription();
        $form = $this->createForm(InscriptionType::class, $inscription, [
            'activite' => $activite,
            'findAjax' => $this->generateUrl('find_benevole')
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $inscBenevole = $inscriptionSrv->enregistrer($inscription);
            $contact = (new ContactDTO())
                ->setDestinataire($inscBenevole->getBenevole()->getEmail())
                ->setEmetteur('bdzkermesse@bdesprez.com')
                ->setTitre("Kermesse - Validation inscription");
            $sender
                ->setTemplate('validation_insc')
                ->setTemplateVars(['btnurl' => $this->generateUrl('valider_inscription', [
                    'id' => $inscBenevole->getBenevole()->getId(),
                    'token' => $inscBenevole->getToken()
                ])])
                ->envoyer($contact);
            return $this->render('benevole/inscription_a_valider.html.twig', [
                'etablissement' => $etablissement,
                'activite' => new ActiviteCard($activite)
            ]);
        }
        return $this->render('benevole/inscription.html.twig', [
            'etablissement' => $etablissement,
            'activite' => new ActiviteCard($activite),
            'form' => $form->createView()
        ]);
    }
}
