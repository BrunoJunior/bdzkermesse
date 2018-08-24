<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 24/08/2018
 * Time: 11:04
 */

namespace App\DataTransfer;


use App\Entity\Membre;

class ContactDTO
{
    /**
     * @var string
     */
    private $membre;

    /**
     * @var string
     */
    private $destinataire;

    /**
     * @var string
     */
    private $titre = '';

    /**
     * @var string
     */
    private $message = '';

    /**
     * @var string
     */
    private $emetteur = '';

    /**
     * @param string $membre
     * @return ContactDTO
     */
    public function setMembre(string $membre): ContactDTO
    {
        $this->membre = $membre;
        return $this;
    }

    /**
     * @param string $destinataire
     * @return ContactDTO
     */
    public function setDestinataire(string $destinataire): ContactDTO
    {
        $this->destinataire = $destinataire;
        return $this;
    }

    /**
     * @return string
     */
    public function getDestinataire(): string
    {
        return $this->destinataire;
    }

    /**
     * @return string
     */
    public function getMembre(): string
    {
        return $this->membre;
    }

    /**
     * @return string
     */
    public function getTitre(): string
    {
        return $this->titre;
    }

    /**
     * @param string $titre
     * @return ContactDTO
     */
    public function setTitre(string $titre): ContactDTO
    {
        $this->titre = $titre;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return ContactDTO
     */
    public function setMessage(string $message): ContactDTO
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmetteur(): string
    {
        return $this->emetteur;
    }

    /**
     * @param string $emetteur
     * @return ContactDTO
     */
    public function setEmetteur(string $emetteur): ContactDTO
    {
        $this->emetteur = $emetteur;
        return $this;
    }

    /**
     * @return array
     */
    public function getMessageVars():array
    {
        return [
            'messsage' => $this->getMessage()
        ];
    }

    /**
     * @param \Swift_Message $message
     */
    public function completerMessage(\Swift_Message $message) {}

}