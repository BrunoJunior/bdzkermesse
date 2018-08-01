<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 01/08/2018
 * Time: 14:47
 */

namespace App\Helper;


class MenuLink extends BreadcrumbLink
{
    /**
     * @var Breadcrumb
     */
    private $menu;

    /**
     * @param Breadcrumb $menu
     * @return MenuLink
     */
    public function setMenu(Breadcrumb $menu): self
    {
        $this->menu = $menu;
        return $this;
    }

    /**
     * @return Breadcrumb|null
     */
    public function getMenu(): ?Breadcrumb
    {
        return $this->menu;
    }
}