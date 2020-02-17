<?php

namespace App\Service;

use App\DataTransfer\ContactDTO;
use App\DataTransfer\DemandeInscription;
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
     * EnvoyerDemandeInscription constructor.
     * @param EmailSender $sender
     */
    public function __construct(EmailSender $sender)
    {
        $this->sender = $sender;
    }

    /**
     * @param DemandeInscription $demandeInscription
     * @return int
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function run(DemandeInscription $demandeInscription): int
    {
        $contact = (new ContactDTO())->setTitre("LA Kermesse - Demande d'inscription")->setDestinataire("perso@bdesprez.com");
        return $this->sender
            ->setTemplate('demande_insc')->setTemplateVars(['demande' => $demandeInscription])
            ->envoyer($contact);
    }
}
