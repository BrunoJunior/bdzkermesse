<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 26/08/2018
 * Time: 13:38
 */

namespace App\Service;

use App\DataTransfer\ContactDTO;
use Swift_Mailer;
use Swift_Message;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class EmailSender extends AbstractEmailSender
{
    /**
     * @var Swift_Mailer
     */
    protected $mailer;

    /**
     * ContactSender constructor.
     * @param Swift_Mailer $mailer
     * @param Environment $twig
     */
    public function __construct(Swift_Mailer $mailer, Environment $twig)
    {
        parent::__construct($twig);
        $this->mailer = $mailer;
    }

    /**
     * @param ContactDTO $contact
     * @param callable|null $completer
     * @return int
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function envoyer(ContactDTO $contact, callable $completer = null):int
    {
        $message = (new Swift_Message($contact->getTitre()))
            ->setFrom('noreply@web-project.fr')
            ->setTo($contact->getDestinataire())
            ->setBody($this->render(), 'text/html')
            ->addPart($this->render('plain'), 'text/plain');
        if ($completer !== null) {
            $completer($message);
        }
        return $this->mailer->send($message);
    }
}
