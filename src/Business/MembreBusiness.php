<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 24/08/2018
 * Time: 14:00
 */

namespace App\Business;


use App\DataTransfer\ContactDTO;
use App\Entity\Membre;
use App\Entity\Remboursement;
use App\Repository\RemboursementRepository;
use App\Service\EmailSender;
use Stringy\Stringy;

class MembreBusiness
{
    /**
     * @var RemboursementRepository
     */
    private $rRemboursement;

    /**
     * @var EmailSender
     */
    private $sender;

    /**
     * MembreBusiness constructor.
     * @param RemboursementRepository $rRemboursement
     * @param EmailSender $sender
     */
    public function __construct(RemboursementRepository $rRemboursement, EmailSender $sender)
    {
        $this->rRemboursement = $rRemboursement;
        $this->sender = $sender;
    }

    /**
     * @param Membre $membre
     * @return string
     */
    public function getIdentite(Membre $membre):string
    {
        return $membre->getPrenom() . ' ' . $membre->getNom();
    }

    /**
     * @param Membre $membre
     * @return string
     */
    public function getIdentiteEmail(Membre $membre):string
    {
        return $this->getIdentite($membre) . '[' . $membre->getEmail() . ']';
    }

    /**
     * @param string $chaine
     * @return string
     */
    private function getPremiereLettreMajuscule(string $chaine):string
    {
        return (string) Stringy::create($chaine)->first(1)->toUpperCase();
    }

    /**
     * @param Membre $membre
     * @return string
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getProchainNumeroSuivi(Membre $membre):string
    {
        $nbRbsts = $this->rRemboursement->countRemboursementsMembres($membre);
        return $this->getPremiereLettreMajuscule($membre->getPrenom()) . $this->getPremiereLettreMajuscule($membre->getNom()) . ($nbRbsts + 1);
    }

    /**
     * @param Membre $membre
     * @param ContactDTO $contact
     * @return ContactDTO
     */
    public function initialiserContact(Membre $membre, ContactDTO $contact):ContactDTO
    {
        return $contact->setMembre($this->getIdentiteEmail($membre))->setDestinataire($membre->getEmail());
    }

    /**
     * @param Membre $membre
     * @param ContactDTO $contact
     * @return int
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function contacter(Membre $membre, ContactDTO $contact):int
    {
        return $this->envoyerEmail($membre, $contact->getTitre(), $contact->getEmetteur(), 'contact', ['message' => $contact->getMessage()]);
    }

    /**
     * @param Membre $membre
     * @param string $titre
     * @param string $emetteur
     * @param string $template
     * @param array $templateVars
     * @return int
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function envoyerEmail(Membre $membre, string $titre, string $emetteur, string $template, array $templateVars = []):int
    {
        $contact = $this->initialiserContact($membre, new ContactDTO())->setTitre($titre)->setEmetteur($emetteur);
        return $this->sender->setTemplate($template)->setTemplateVars($templateVars)->envoyer($contact);
    }
}