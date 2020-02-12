<?php

namespace App\Controller;

use App\DataTransfer\ContactDTO;
use App\Entity\InscriptionBenevole;
use App\Service\MailgunSender;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class InscriptionBenevoleController extends MyController
{

    /**
     * @Route("/inscriptions/{id}/annuler", name="annuler_inscription_benevole")
     * @Security("inscriptionBenevole.isProprietaire(user)")
     * @param InscriptionBenevole $inscriptionBenevole
     * @param MailgunSender $sender
     * @return Response
     */
    public function annuler(InscriptionBenevole $inscriptionBenevole, MailgunSender $sender): Response
    {
        $activite = $inscriptionBenevole->getInscription()->getActivite()->getId();
        $mail = $this->preparerEnvoiMailInscription($inscriptionBenevole, $sender, true);
        $em = $this->getDoctrine()->getManager();
        $em->remove($inscriptionBenevole);
        $em->flush();
        $mail['sender']->envoyer($mail['contact']);
        return $this->redirectToRoute('gerer_benevoles', ['id' => $activite]);
    }

    /**
     * @Route("/inscriptions/{id}/valider", name="valider_inscription_benevole")
     * @Security("inscriptionBenevole.isProprietaire(user)")
     * @param InscriptionBenevole $inscriptionBenevole
     * @param MailgunSender $sender
     * @return Response
     */
    public function valider(InscriptionBenevole $inscriptionBenevole, MailgunSender $sender): Response
    {
        $activite = $inscriptionBenevole->getInscription()->getActivite()->getId();
        $mail = $this->preparerEnvoiMailInscription($inscriptionBenevole, $sender, false);
        $em = $this->getDoctrine()->getManager();
        $inscriptionBenevole->setValidee(true);
        $em->flush();
        $mail['sender']->envoyer($mail['contact']);
        return $this->redirectToRoute('gerer_benevoles', ['id' => $activite]);
    }

    /**
     * @param InscriptionBenevole $inscriptionBenevole
     * @param MailgunSender $sender
     * @param bool $refus
     * @return array
     */
    private function preparerEnvoiMailInscription(InscriptionBenevole $inscriptionBenevole, MailgunSender $sender, bool $refus = false): array
    {
        $benevole = $inscriptionBenevole->getBenevole();
        $creneau = $inscriptionBenevole->getInscription();
        $activite = $creneau->getActivite();
        $contact = new ContactDTO();
        $contact->setTitre(
            $refus
                ? "Kermesse - {$activite->getNom()} - Annulation de participation"
                : "Kermesse - {$activite->getNom()} - Validation de participation"
        )->setDestinataire($benevole->getEmail());
        return ["sender" => $sender->setTemplate('insc_validee_refusee')->setTemplateVars([
            'identite' => $benevole->getIdentite(),
            'refus' => $refus,
            'activite' => $activite->getNom(),
            'date' => $activite->getDate(),
            'debut' => $creneau->getDebut(),
            'fin' => $creneau->getFin()
        ]), "contact" => $contact];
    }
}
