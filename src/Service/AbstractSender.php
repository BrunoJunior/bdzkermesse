<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 24/08/2018
 * Time: 14:38
 */

namespace App\Service;


use App\DataTransfer\ContactDTO;

abstract class AbstractSender
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;
    /**
     * @var \Twig_Environment
     */
    protected $twig;

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
     * Dans templates/mails, sans les extensions
     * @return string
     */
    abstract protected function getTemplate():string;

    /**
     * @param ContactDTO $contact
     * @return array
     */
    protected function getParametresTemplate(ContactDTO $contact): array
    {
        return [
            'message' => $contact->getMessage()
        ];
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
        return $this->twig->render("mails/" . $this->getTemplate() . ".$format.twig", $this->getParametresTemplate($contact));
    }
}