<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 22/08/2018
 * Time: 13:51
 */

namespace App\Enum;

/**
 * Trait PastilleEnumTrait
 * Ajouter la possibilté de transformer la donnée en pastille HTML
 * @package App\Enum
 */
trait PastilleEnumTrait
{
    /**
     * @var string
     */
    protected $iconeClasse;

    /**
     * @return string
     */
    public function getPastille(): string
    {
        return "<i class=\"fas fa-circle $this->iconeClasse\"></i>";
    }

    /**
     * @param string $icone
     * @return TicketEtatEnum
     */
    protected function setPastilleClasse(string $classe): self
    {
        $this->iconeClasse = $classe;
        return $this;
    }
}