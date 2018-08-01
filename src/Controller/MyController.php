<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 01/08/2018
 * Time: 15:32
 */

namespace App\Controller;


use App\Entity\Kermesse;
use App\Helper\Breadcrumb;
use App\Helper\MenuLink;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class MyController extends Controller
{
    const MENU_ACTIVITES = 'Activités';
    const MENU_TICKETS = 'Tickets';
    const MENU_RECETTES = 'Recettes';
    const MENU_MEMBRES_ACTIFS = 'Membres actifs';

    /**
     * @param Kermesse $activeKermesse
     * @return MenuLink
     */
    protected function getKermessesMenuLink(?Kermesse $activeKermesse = null): MenuLink
    {
        $subMenu = Breadcrumb::getInstance(false);
        $kermesseRepo = $this->getDoctrine()->getRepository(Kermesse::class);
        $kermesses = $kermesseRepo->findByEtablissementOrderByAnnee($this->getUser());
        foreach ($kermesses as $kermesse) {
            $subMenu->addLink(MenuLink::getInstance($kermesse->getTheme(), null, $this->generateUrl('kermesse', ['id' => $kermesse->getId()]))->setActive($activeKermesse !== null && $activeKermesse->getId() === $kermesse->getId()));
        }
        return MenuLink::getInstance('Kermesses', 'theater-masks', '#')
            ->setMenu($subMenu)
            ->setActive($activeKermesse !== null);
    }

    /**
     * @param Kermesse $kermesse
     * @param string $activeName
     * @return MenuLink
     */
    protected function getKermesseMenu(Kermesse $kermesse, string $activeName = '') {
        $subMenu = Breadcrumb::getInstance(false)
            ->addLink(MenuLink::getInstance(static::MENU_ACTIVITES, null, $this->generateUrl('kermesse', ['id' => $kermesse->getId()])))
            ->addLink(MenuLink::getInstance(static::MENU_TICKETS, null, $this->generateUrl('liste_tickets', ['id' => $kermesse->getId()])))
            ->addLink(MenuLink::getInstance(static::MENU_RECETTES, null, $this->generateUrl('liste_recettes', ['id' => $kermesse->getId()])))
            ->addLink(MenuLink::getInstance(static::MENU_MEMBRES_ACTIFS, null, $this->generateUrl('membres_actifs', ['id' => $kermesse->getId()])))
            ->setActiveLinkByName($activeName);
        return MenuLink::getInstance($kermesse->getTheme(), 'tag', '#')
            ->setMenu($subMenu)
            ->setActive($activeName !== '');
    }
}