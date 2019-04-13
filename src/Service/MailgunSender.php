<?php
/**
 * Created by PhpStorm.
 * User: bdesprez
 * Date: 03/05/18
 * Time: 23:10
 */

namespace App\Service;

use App\DataTransfer\ContactDTO;
use App\Entity\Etablissement;
use Mailgun\Mailgun;
use Symfony\Component\Security\Core\Security;

class MailgunSender extends AbstractEmailSender
{
    /**
     * @var Mailgun
     */
    private $mailgun;

    /**
     * @var Security
     */
    private $security;

    /**
     * EMailSender constructor.
     * @param Mailgun $mailgun
     * @param \Twig_Environment $twig
     */
    public function __construct(Mailgun $mailgun, \Twig_Environment $twig)
    {
        parent::__construct($twig);
        $this->mailgun = $mailgun;
    }

    /**
     * @required
     * @param Security $security
     */
    public function setSecurity(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param ContactDTO $contact
     * @param callable|null $completer
     * @return int
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function envoyer(ContactDTO $contact, callable $completer = null): int
    {
        $utilisateur = $this->security->getUser();
        $nom = $utilisateur->getUsername();
        if ($utilisateur instanceof Etablissement) {
            $nom = $utilisateur->getNom();
        }

        $this->templateVars['emetteur'] = $contact->getEmetteur();
        $retour = $this->mailgun->messages()->send('mb.bdesprez.com', [
            'from' => "Kermesse - $nom <mailgun@bdesprez.com>",
            'to' => $contact->getDestinataire(),
            'cc' => implode(',', $contact->getCopies()),
            'subject' => $nom . ' - ' . $contact->getTitre(),
            'html' => $this->render(),
            'text' => $this->render('plain')
        ]);
        return $retour->getId() == '' ? 0 : 1;
    }
}