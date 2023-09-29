<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 26/08/2018
 * Time: 13:38
 */

namespace App\Service;

use App\DataTransfer\ContactDTO;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class EmailSender extends AbstractEmailSender
{
    /**
     * @var MailerInterface
     */
    protected $mailer;

    /**
     * ContactSender constructor.
     * @param MailerInterface $mailer
     * @param Environment $twig
     */
    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        parent::__construct($twig);
        $this->mailer = $mailer;
    }

    /**
     * @param ContactDTO $contact
     * @param callable|null $completer
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @return integer
     */
    public function envoyer(ContactDTO $contact, callable $completer = null): int
    {
        $message = (new Email())
            ->subject($contact->getTitre())
            ->from(new Address('noreply@web-project.fr', 'LA kermesse'))
            ->to(new Address($contact->getDestinataire(), $contact->getMembre()))
            ->html($this->render())
            ->text($this->render('plain'));
        if ($completer !== null) {
            $completer($message);
        }
        $this->mailer->send($message);
        return 1;
    }
}
