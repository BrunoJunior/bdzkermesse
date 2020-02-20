<?php

namespace App\Controller;

use App\Business\KermesseBusiness;
use App\DataTransfer\ActiviteCard;
use App\DataTransfer\ContactDTO;
use App\DataTransfer\Inscription;
use App\DataTransfer\Migration;
use App\DataTransfer\Planning;
use App\Entity\Activite;
use App\Entity\Benevole;
use App\Entity\Creneau;
use App\Entity\Etablissement;
use App\Form\InscriptionType;
use App\Form\MigrationType;
use App\Repository\BenevoleRepository;
use App\Repository\InscriptionBenevoleRepository;
use App\Repository\KermesseRepository;
use App\Service\InscriptionBenevole;
use App\Service\MailgunSender;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class BenevoleController extends MyController
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
     * @param string $code
     * @return Etablissement
     */
    private function getEtablissementByCode(string $code): Etablissement
    {
        $etablissement = $this->getDoctrine()
            ->getRepository(Etablissement::class)->findOneBy(['username' => $code]);
        if (!$etablissement instanceof Etablissement) {
            throw new NotFoundHttpException("La page demandée n'existe pas !");
        }
        return $etablissement;
    }

    /**
     * @Route("/benevoles/{code<[a-zA-Z0-9_.-]+>}", name="acces_benevole")
     * @param string $code
     * @param KermesseRepository $rKermesse
     * @param KermesseBusiness $mKermesse
     * @return Response
     * @throws NonUniqueResultException
     */
    public function accesBenevoles(string $code, KermesseRepository $rKermesse, KermesseBusiness $mKermesse): Response
    {
        $etablissement = $this->getEtablissementByCode($code);
        $kermesse = $rKermesse->findCouranteByEtablissement($etablissement);

        return $this->render('benevole/index.html.twig', [
            'etablissement' => $etablissement,
            'kermesse' => $kermesse,
            'activites' => $kermesse === null ? [] : $kermesse->getActivites()->filter(function (Activite $activite) {
                return $activite->getCreneaux()->count() > 0;
            })->map(function (Activite $activite) {
                return new ActiviteCard($activite);
            }),
            'tauxInscription' => $kermesse ? round(100 * $mKermesse->getNbBenevolesInscrits($kermesse) / $mKermesse->getNbBenevolesRequis($kermesse)) : 0,
            'tauxInscriptionEnAttente' => $kermesse ? round(100 * $mKermesse->getNbBenevolesEnAttente($kermesse) / $mKermesse->getNbBenevolesRequis($kermesse)) : 0,
        ]);
    }

    /**
     * @Route("/benevoles/{code<[a-zA-Z0-9_.-]+>}/{id<\d+>}/creneau-{idCreneau<\d+>?0}", name="inscription_benevole")
     * @param string $code
     * @param Activite $activite
     * @param int $idCreneau
     * @param Request $request
     * @param InscriptionBenevole $inscriptionSrv
     * @param MailgunSender $sender
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function inscriptionBenevole(
        string $code,
        Activite $activite,
        int $idCreneau,
        Request $request,
        InscriptionBenevole $inscriptionSrv,
        MailgunSender $sender
    ): Response
    {
        $etablissement = $this->getEtablissementByCode($code);
        $creneau = $this->getDoctrine()->getManager()->find(Creneau::class, $idCreneau);
        if ($idCreneau > 0 && ($creneau === null || $creneau->getActivite()->getId() !== $activite->getId())) {
            throw new NotFoundHttpException("La page demandée n'existe pas !");
        }
        if ($activite->getEtablissement()->getId() !== $etablissement->getId()) {
            throw new UnauthorizedHttpException("Vous n'êtes pas autorisée à voir cela !");
        }
        $inscription = new Inscription();
        if ($creneau) {
            $inscription->setCreneau($creneau);
        }
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
                ], UrlGeneratorInterface::ABSOLUTE_URL)])
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


    /**
     * Affichage du planning des bénévoles
     * @Route("/benevoles/{code<[a-zA-Z0-9_.-]+>}/planning", name="benevoles_planning")
     * @param string $code
     * @param KermesseRepository $rKermesse
     * @return Response
     * @throws NonUniqueResultException
     */
    public function showPlanning(string $code, KermesseRepository $rKermesse): Response
    {
        $etablissement = $this->getEtablissementByCode($code);
        $kermesse = $rKermesse->findCouranteByEtablissement($etablissement);
        $planning = Planning::createFromKermesse($kermesse);
        return $this->render('kermesse/planning.html.twig', [
            'planning' => $planning,
            'codeEtablissement' => $code,
            'nbCols' => round($planning->getTaillePlage() / 1800)
        ]);
    }

    /**
     * @param Migration $migration
     * @param string $code
     * @param Creneau $from
     * @param Creneau $to
     * @return FormInterface
     */
    private function initMigration(Migration $migration, string $code, Creneau $from, Creneau $to): FormInterface
    {
        $this->getEtablissementByCode($code);
        if ($from === null || $to === null) {
            throw new NotFoundHttpException("La page demandée n'existe pas !");
        }
        $toBenevoles = function (\App\Entity\InscriptionBenevole $inscriptionBenevole) {
            return $inscriptionBenevole->getBenevole();
        };
        $benevolesTo = $to->getInscriptionBenevoles()->map($toBenevoles);
        return $this->createForm(MigrationType::class, $migration, [
            'transferables' => $from->getInscriptionBenevoles()->filter(function (\App\Entity\InscriptionBenevole $inscriptionBenevole) use ($benevolesTo) {
                return !$benevolesTo->contains($inscriptionBenevole->getBenevole());
            }),
            'action' => $this->generateUrl('demande_migration', ['code' => $code, 'id' => $from->getId(), 'to' => $to->getId()]),
        ]);
    }

    /**
     * @Route("/benevoles/{code<[a-zA-Z0-9_.-]+>}/creneau-{id<\d+>}/migration", name="demande_migration", methods={"GET"})
     * @param string $code
     * @param Creneau $from
     * @param Request $request
     * @return Response
     */
    public function contenuModaleMigration(string $code, Creneau $from, Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $to = $em->find(Creneau::class, $request->get('to'));
        $form = $this->initMigration(new Migration(), $code, $from, $to);
        return $this->render('benevole/modal_migration.html.twig', [
            'from' => $from,
            'to' => $to,
            'form' => $form->createView(),
            'benevolesMax' => $to->getNbBenevolesRecquis() - $to->getInscriptionBenevoles()->count()
        ]);
    }

    /**
     * @Route("/benevoles/{code<[a-zA-Z0-9_.-]+>}/creneau-{id<\d+>}/migration", name="effectuer_migration", methods={"POST"})
     * @param string $code
     * @param Creneau $from
     * @param Request $request
     * @param MailgunSender $sender
     * @return Response
     */
    public function effecuerMigration(string $code, Creneau $from, Request $request, MailgunSender $sender): Response
    {
        $migration = new Migration();
        $em = $this->getDoctrine()->getManager();
        $to = $em->find(Creneau::class, $request->get('to'));
        $form = $this->initMigration($migration, $code, $from, $to);
        $form->handleRequest($request);
        if (!$form->isValid() || !$form->isSubmitted()) {
            $this->addFlash('danger', "Données non valides !");
        } else {
            // Récupération des inscriptions des bénévoles choisis
            foreach ($migration->getBenevoles() as $inscriptionBenevole) {
                $this->logger->debug("Migration #{$inscriptionBenevole->getId()} - {$inscriptionBenevole->getBenevole()->getEmail()}");
                // Les inscriptions sont invalidées (en attente de validation du bénévole)
                $inscriptionBenevole->setValidee(false);
                // Migration des inscriptions vers nouveau créneau
                $inscriptionBenevole->setInscription($to);
                $benevole = $inscriptionBenevole->getBenevole();
                // Envoi de l'email
                try {
                    $sender->setTemplate('validation_migration')->setTemplateVars([
                        'identite' => $benevole->getIdentite(),
                        'id' => $inscriptionBenevole->getId(),
                        'code' => $code,
                        'from' => $from,
                        'to' => $to
                    ])->envoyer((new ContactDTO())
                        ->setTitre("Organisatin kermesse - Changement d'activité")
                        ->setDestinataire($benevole->getEmail()));
                } catch (Exception $exception) {
                    $this->logger->error("Erreur lors de l'envoi de l'email", $exception->getTrace());
                    $em->remove($inscriptionBenevole);
                }
            }
            $em->flush();
            $this->addFlash('success', "Demandes envoyées aux bénévoles concernés");
        }

        return $this->redirectToRoute('benevoles_planning', ['code' => $code]);
    }

    /**
     * @Route("/benevoles/{code<[a-zA-Z0-9_.-]+>}/inscription-{id<\d+>}/{reponse<oui|non>}", name="valider_migration", methods={"GET"})
     * @param string $code
     * @param \App\Entity\InscriptionBenevole $inscription
     * @param string $reponse
     * @return Response
     */
    public function validationMigration(string $code, \App\Entity\InscriptionBenevole $inscription, string $reponse): Response
    {
        $this->getEtablissementByCode($code);
        $benevole = $inscription->getBenevole();
        $em = $this->getDoctrine()->getManager();
        if ($reponse === 'oui') {
            // OK = Mise à jour de l'inscription
            $inscription->setValidee(true);
        } else {
            // Refus = suppression
            $em->remove($inscription);
        }
        $em->flush();
        // Affichage de la page de remerciement
        return $this->render('benevole/migration_valide.html.twig', ['identite' => $benevole->getIdentite(), 'code' => $code]);
    }
}
