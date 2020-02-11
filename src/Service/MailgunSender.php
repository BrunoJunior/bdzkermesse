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
     * dev / prod
     * @var string
     */
    private $environment;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * EMailSender constructor.
     * @param Mailgun $mailgun
     * @param Environment $twig
     * @param KernelInterface $kernel
     * @param LoggerInterface $logger
     * @param EmailSender $emailSender
     */
    public function __construct(Mailgun $mailgun, Environment $twig, KernelInterface $kernel, LoggerInterface $logger, EmailSender $emailSender)
    {
        parent::__construct($twig);
        $this->mailgun = $mailgun;
        $this->environment = $kernel->getEnvironment();
        $this->logger = $logger;
        $this->emailSender = $emailSender;
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
            'from' => "Kermesse - $nom <mailgun@bdesprez.com>",
            'to' => $contact->getDestinataire(),
            'subject' => $nom . ' - ' . $contact->getTitre(),
            'html' => $this->render(),
            'text' => $this->render('plain')
        ];
        if (!empty($contact->getCopies())) {
            $params['cc'] = implode(',', $contact->getCopies());
        }

        try {
            $retour = $this->mailgun->messages()->send('mb.bdesprez.com', $params);
            return $retour->getId() == '' ? 0 : 1;
        } catch (Exception $exception) {
            $this->logger->critical("Erreur lors de l'envoi du mail via mailgun !", $exception->getTrace());
            // On essaye avec le mailer standard
            return $this->emailSender->setTemplate($this->template)->setTemplateVars($this->templateVars)->envoyer($contact, $completer);
        }

    }
}
