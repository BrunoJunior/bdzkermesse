<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 26/08/2018
 * Time: 13:38
 */

namespace App\Service;


use App\DataTransfer\ContactDTO;

class EmailSender
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
     * @var string
     */
    private $template;

    /**
     * @var array
     */
    private $templateVars = [];

    /**
     * @var \Swift_Message
     */
    private $message;

    /**
     * ContactSender constructor.
     * @param \Swift_Mailer $mailer
     * @param \Twig_Environment $twig
     */
    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    /**
     * @param string $template
     * @return EmailSender
     */
    public function setTemplate(string $template): EmailSender
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @param array $templateVars
     * @return EmailSender
     */
    public function setTemplateVars(array $templateVars): EmailSender
    {
        $this->templateVars = $templateVars;
        return $this;
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
            ->setFrom($contact->getEmetteur())
            ->setTo($contact->getDestinataire())
            ->setBody($this->render(), 'text/html')
            ->addPart($this->render('plain'), 'text/plain');
        if ($completer !== null) {
            $completer($message);
        }
        return $this->mailer->send($message);
    }

    /**
     * @param string $format
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function render($format = 'html'): string
    {
        return $this->twig->render("mails/" . $this->template . ".$format.twig", $this->templateVars);
    }
}