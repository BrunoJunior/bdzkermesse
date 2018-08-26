<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 26/08/2018
 * Time: 13:38
 */

namespace App\Service;

use App\DataTransfer\ContactDTO;

class EmailSender extends AbstractEmailSender
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * ContactSender constructor.
     * @param \Swift_Mailer $mailer
     * @param \Twig_Environment $twig
     */
    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig)
    {
        parent::__construct($twig);
        $this->mailer = $mailer;
    }

    /**
     * @param ContactDTO $contact
     * @param callable|null $completer
     * @return int
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function envoyer(ContactDTO $contact, callable $completer = null):int
    {
        $message = (new \Swift_Message($contact->getTitre()))
            ->setFrom('bdzkermesse@bdesprez.com')
            ->setTo($contact->getDestinataire())
            ->setBody($this->render(), 'text/html')
            ->addPart($this->render('plain'), 'text/plain');
        if ($completer !== null) {
            $completer($message);
        }
        return $this->mailer->send($message);
    }
}