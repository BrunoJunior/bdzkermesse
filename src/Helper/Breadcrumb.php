<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 01/08/2018
 * Time: 11:44
 */

namespace App\Helper;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Breadcrumb
{
    /**
     * @var Collection|BreadcrumbLink[]
     */
    private $links;

    /**
     * @var bool
     */
    private $lastActivation = true;

    /**
     * Breadcrumb constructor.
     * @param bool $lastActivation
     */
    public function __construct(bool $lastActivation = true)
    {
        $this->links = new ArrayCollection();
        $this->lastActivation = $lastActivation;
    }

    /**
     * @param bool $lastActivation
     * @return Breadcrumb
     */
    public static function getInstance(bool $lastActivation = true): self
    {
        return new Breadcrumb($lastActivation);
    }

    /**
     * @param BreadcrumbLink $link
     * @return $this
     */
    public function addLink(BreadcrumbLink $link): self
    {
        if ($this->lastActivation) {
            $lastLink = $this->links->last();
            if ($lastLink instanceof BreadcrumbLink) {
                $lastLink->setActive(false);
            }
            $link->setActive();
        }
        $this->links->add($link);
        return $this;
    }

    /**
     * @return Collection
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    /**
     * @param string $name
     * @return Breadcrumb
     */
    public function setActiveLinkByName(string $name): self
    {
        foreach ($this->links as $link) {
            if ($link->getName() === $name) {
                $link->setActive();
                break;
            }
        }
        return $this;
    }
}