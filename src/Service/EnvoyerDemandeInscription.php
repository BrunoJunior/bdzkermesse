<?php

namespace App\Service;

use App\DataTransfer\ContactDTO;
use App\DataTransfer\DemandeInscription;
use App\Entity\Inscription;
use App\Enum\InscriptionStatutEnum;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class EnvoyerDemandeInscription
 * @package App\Service
 */
class EnvoyerDemandeInscription
{

    /**
     * @var EmailSender
     */
    private $sender;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * EnvoyerDemandeInscription constructor.
     * @param EmailSender $sender
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     */
    public function __construct(EmailSender $sender, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->sender = $sender;
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @param DemandeInscription $demandeInscription
     * @return int
     */
    public function run(DemandeInscription $demandeInscription): int
    {
        // Enregistrement de la demande
        $inscription = (new Inscription())
            ->setContactEmail($demandeInscription->email)
            ->setContactMobile($demandeInscription->mobile)
            ->setContactName($demandeInscription->contact)
            ->setEtablissementCodePostal($demandeInscription->codePostal)
            ->setEtablissementNom($demandeInscription->etablissement)
            ->setEtablissementVille($demandeInscription->localite)
            ->setRole($demandeInscription->role)
            ->setState(InscriptionStatutEnum::EN_ATTENTE);
        $this->em->persist($inscription);
        $contact = (new ContactDTO())
            ->setTitre("LA Kermesse - Demande d'inscription")
            ->setDestinataire("perso@bdesprez.com");
        try {
            $result = $this->sender
                ->setTemplate('demande_insc')->setTemplateVars(['demande' => $demandeInscription])
                ->envoyer($contact);
            $this->em->flush();
            return $result;
        } catch (\Exception | \Throwable $exception) {
            $this->logger->error("Erreur lors de la demande d'inscription!", ['exception' => $exception]);
            return 0;
        }
    }
}
