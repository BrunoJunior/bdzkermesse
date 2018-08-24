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
use Stringy\Stringy;

class MembreBusiness
{
    /**
     * @var RemboursementRepository
     */
    private $rRemboursement;

    /**
     * MembreBusiness constructor.
     * @param RemboursementRepository $rRemboursement
     */
    public function __construct(RemboursementRepository $rRemboursement)
    {
        $this->rRemboursement = $rRemboursement;
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
     * @return ContactDTO
     */
    public function initialiserContact(Membre $membre, ContactDTO $contact):ContactDTO
    {
        return $contact->setMembre($this->getIdentiteEmail($membre))->setDestinataire($membre->getEmail());
    }
}