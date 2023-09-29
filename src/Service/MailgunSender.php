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
use Exception;
use Mailgun\Mailgun;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * EMailSender constructor.
     * @param Mailgun $mailgun
     * @param Environment $twig
     * @param LoggerInterface $logger
     * @param EmailSender $emailSender
     * @param ParameterBagInterface $params
     */
    public function __construct(Mailgun $mailgun, Environment $twig, LoggerInterface $logger, EmailSender $emailSender, ParameterBagInterface $params)
    {
        parent::__construct($twig);
        $this->mailgun = $mailgun;
        $this->logger = $logger;
        $this->emailSender = $emailSender;
        $this->params = $params;
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
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function envoyer(ContactDTO $contact, callable $completer = null): int
    {
        $utilisateur = $this->security->getUser();
        $nom = '';
        if ($utilisateur instanceof Etablissement) {
            $nom = $utilisateur->getNom();
        } elseif ($utilisateur !== null) {
            $nom = $utilisateur->getUsername();
        }

        $this->templateVars['emetteur'] = $contact->getEmetteur();
        $params = [
            'from' => "Kermesse - $nom <noreply@web-project.fr>",
            'to' => $contact->getDestinataire(),
            'subject' => $nom . ' - ' . $contact->getTitre(),
            'html' => $this->render(),
            'text' => $this->render('plain')
        ];
        if (!empty($contact->getCopies())) {
            $params['cc'] = implode(',', $contact->getCopies());
        }

        try {
            $retour = $this->mailgun->messages()->send($this->params->get('mailgun_domain'), $params);
            return $retour->getId() == '' ? 0 : 1;
        } catch (Exception $exception) {
            $this->logger->critical("Erreur lors de l'envoi du mail via mailgun !", ['exception' => $exception]);
            // On essaye avec le mailer standard
            return $this->emailSender->setTemplate($this->template)->setTemplateVars($this->templateVars)->envoyer($contact, $completer);
        }

    }
}
