<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 24/08/2018
 * Time: 14:00
 */

namespace App\Business;


use App\DataTransfer\ContactDTO;
use App\Entity\Etablissement;
use App\Entity\Membre;
use App\Repository\MembreRepository;
use App\Repository\RemboursementRepository;
use App\Service\MailgunSender;
use Doctrine\ORM\NonUniqueResultException;
use Stringy\Stringy;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MembreBusiness
{
    /**
     * @var RemboursementRepository
     */
    private $rRemboursement;
    /**
     * @var MembreRepository
     */
    private $rMembre;

    /**
     * @var MailgunSender
     */
    private $sender;

    /**
     * MembreBusiness constructor.
     * @param RemboursementRepository $rRemboursement
     * @param MailgunSender $sender
     */
    public function __construct(RemboursementRepository $rRemboursement, MailgunSender $sender, MembreRepository $rMembre)
    {
        $this->rRemboursement = $rRemboursement;
        $this->sender = $sender;
        $this->rMembre = $rMembre;
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
     * @throws NonUniqueResultException
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
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
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
     * @param array $membresCopie
     * @return int
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function envoyerEmail(Membre $membre, string $titre, string $emetteur, string $template, array $templateVars = [], array $membresCopie = []):int
    {
        $contact = $this->initialiserContact($membre, new ContactDTO($membresCopie))->setTitre($titre)->setEmetteur($emetteur);
        return $this->sender->setTemplate($template)->setTemplateVars($templateVars)->envoyer($contact);
    }

    /**
     * La liste des gestionnaires de facture
     * @param Etablissement $etablissement
     * @return Membre[]
     */
    public function getGestionnaires(Etablissement $etablissement)
    {
        return $this->rMembre->findGestionnairesByEtablissement($etablissement);
    }
}
