<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 26/08/2018
 * Time: 20:32
 */

namespace App\Service;

use App\DataTransfer\ContactDTO;

abstract class AbstractEmailSender
{

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
    protected $templateVars = [];

    /**
     * ContactSender constructor.
     * @param \Twig_Environment $twig
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param string $template
     * @return static
     */
    public function setTemplate(string $template): self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @param array $templateVars
     * @return static
     */
    public function setTemplateVars(array $templateVars): self
    {
        $this->templateVars = $templateVars;
        return $this;
    }

    /**
     * @param ContactDTO $contact
     * @param callable|null $completer
     * @return int
     */
    abstract public function envoyer(ContactDTO $contact, callable $completer = null):int;

    /**
     * @param string $format
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function render($format = 'html'): string
    {
        return $this->twig->render("mails/" . $this->template . ".$format.twig", $this->templateVars);
    }

}