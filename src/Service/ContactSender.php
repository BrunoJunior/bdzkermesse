<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 24/08/2018
 * Time: 11:32
 */

namespace App\Service;


use App\DataTransfer\ContactDTO;

/**
 * Class ContactSender
 * @package App\Service
 */
class ContactSender
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * ContactSender constructor.
     * @param \Swift_Mailer $mailer
     */
    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    /**
     * @param ContactDTO $contact
     * @return int
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function envoyer(ContactDTO $contact):int
    {
        $message = (new \Swift_Message($contact->getTitre()))
            ->setFrom($contact->getEmetteur())
            ->setTo($contact->getDestinataire())
            ->setBody($this->render($contact), 'text/html')
            ->addPart($this->render($contact, 'plain'), 'text/plain');
        return $this->mailer->send($message);
    }

    /**
     * @param ContactDTO $contact
     * @param string $format
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function render(ContactDTO $contact, $format = 'html'): string
    {
        return $this->twig->render("membre/contact_body.$format.twig", [
            'message' => $contact->getMessage()
        ]);
    }
}