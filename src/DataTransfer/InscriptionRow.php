<?php


namespace App\DataTransfer;

use App\Entity\Inscription;
use App\Enum\InscriptionStatutEnum;
use SimpleEnum\Exception\UnknownEumException;

class InscriptionRow {

    /**
     * @var Inscription
     */
    private $inscription;

    /**
     * @var InscriptionStatutEnum
     */
    private $etatEnum;

    /**
     * @param Inscription $inscription
     * @throws UnknownEumException
     */
    public function __construct(Inscription $inscription) {
        $this->inscription = $inscription;
        $this->etatEnum = InscriptionStatutEnum::getInstance($inscription->getState());
    }

    /**
     * Le code postal
     * @return string
     */
    public function getCp(): string {
        return $this->inscription->getEtablissementCodePostal();
    }

    /**
     * La ville
     * @return string
     */
    public function getVille(): string {
        return $this->inscription->getEtablissementVille();
    }

    /**
     * L'établissement
     * @return string
     */
    public function getEtablissement(): string {
        return $this->inscription->getEtablissementNom();
    }

    /**
     * Le nom du contact
     * @return string
     */
    public function getContact(): string {
        return $this->inscription->getContactName();
    }

    /**
     * E-mail
     * @return string
     */
    public function getEmail(): string {
        return $this->inscription->getContactEmail();
    }

    /**
     * N° de téléphone
     * @return string
     */
    public function getNumero(): string {
        return $this->inscription->getContactMobile();
    }

    /**
     * Le rôle
     * @return string
     */
    public function getRole(): string {
        return $this->inscription->getRole();
    }

    /**
     * Le statut sous forme d'énumération à pastille HTML
     * @return InscriptionStatutEnum
     */
    public function getStatut(): InscriptionStatutEnum {
        return $this->etatEnum;
    }

    /**
     * L'ID de l'entité
     * @return int
     */
    public function getId(): int {
        return $this->inscription->getId();
    }

}
