<?php


namespace App\Service;


use App\DataTransfer\Inscription;
use App\Entity\Benevole;
use App\Repository\BenevoleRepository;
use App\Repository\InscriptionBenevoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class InscriptionBenevole
{
    /**
     * @var BenevoleRepository
     */
    private $rBenevole;

    /**
     * @var InscriptionBenevoleRepository
     */
    private $rInscBenevole;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * InscriptionBenevole constructor.
     * @param BenevoleRepository $rBenevole
     * @param InscriptionBenevoleRepository $rInscBenevole
     * @param EntityManagerInterface $em
     */
    public function __construct(BenevoleRepository $rBenevole, InscriptionBenevoleRepository $rInscBenevole, EntityManagerInterface $em)
    {
        $this->rBenevole = $rBenevole;
        $this->rInscBenevole = $rInscBenevole;
        $this->em = $em;
    }

    /**
     * Récupération d'un token non utilisé
     * @return string
     * @throws Exception
     */
    private function getUnusedToken(): string
    {
        $token = bin2hex(random_bytes(50));
        $exist = $this->rInscBenevole->findOneBy(['token' => $token]);
        if ($exist) {
            return $this->getUnusedToken();
        }
        return $token;
    }

    /**
     * Enregistrement du formulaire
     * @param Inscription $inscription
     * @return \App\Entity\InscriptionBenevole
     * @throws Exception
     */
    public function enregistrer(Inscription $inscription): \App\Entity\InscriptionBenevole
    {
        // Création / récupération bénévole
        $benevole = $this->rBenevole->findOneBy(['email' => $inscription->getEmail()]);
        if ($benevole === null) {
            $benevole = new Benevole();
            $benevole->setEmail($inscription->getEmail());
            $benevole->setIdentite($inscription->getNom());
            $benevole->setEmailValide(false);
            $this->em->persist($benevole);
        }
        $benevole->setPortable($inscription->getPortable());
        // Création inscription
        $inscriptionBenevole = new \App\Entity\InscriptionBenevole();
        $inscriptionBenevole->setBenevole($benevole);
        $inscriptionBenevole->setToken($this->getUnusedToken());
        $inscriptionBenevole->setValidee(false);
        $inscription->getCreneau()->addInscriptionBenevole($inscriptionBenevole);
        $this->em->persist($inscriptionBenevole);
        $this->em->flush();
        // Le token généré utile pour l'envoi de l'email de validation
        return $inscriptionBenevole;
    }
}
