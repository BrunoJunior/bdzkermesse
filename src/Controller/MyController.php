<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 01/08/2018
 * Time: 15:32
 */

namespace App\Controller;

use App\Entity\Etablissement;
use App\Entity\Kermesse;
use App\Helper\Breadcrumb;
use App\Helper\MenuLink;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class MyController extends AbstractController
{
    const MENU_ACTIVITES = 'Activités';
    const MENU_TICKETS = 'Dépenses';
    const MENU_RECETTES = 'Recettes';
    const MENU_MEMBRES_ACTIFS = 'Membres actifs';
    const MENU_ACCUEIL = 'Accueil';
    const MENU_MEMBRES = 'Membres';
    const MENU_REMBOURSEMENTS = 'Remboursements';
    const MENU_PLANNING = 'Planning';
    const MENU_ACTIVITES_AUTRES = 'Actions';
    const MENU_BILAN = 'Bilan';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * MyController constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Kermesse $activeKermesse
     * @return MenuLink
     */
    protected function getKermessesMenuLink(?Kermesse $activeKermesse = null): MenuLink
    {
        $etab = $this->getUser();
        if (!$etab instanceof Etablissement)
        {
            throw new NotFoundHttpException("La page demandée n'existe pas !");
        }
        $subMenu = Breadcrumb::getInstance(false);
        $kermesseRepo = $this->getDoctrine()->getRepository(Kermesse::class);
        $kermesses = $kermesseRepo->findByEtablissementOrderByAnnee($etab);
        foreach ($kermesses as $kermesse) {
            $subMenu->addLink(MenuLink::getInstance($kermesse->getTheme() . ' (' . $kermesse->getAnnee() . ')', null, $this->generateUrl('kermesse', ['id' => $kermesse->getId()]))->setActive($activeKermesse !== null && $activeKermesse->getId() === $kermesse->getId()));
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
            ->addLink(MenuLink::getInstance(static::MENU_REMBOURSEMENTS, null, $this->generateUrl('liste_remboursements', ['id' => $kermesse->getId()])))
            ->addLink(MenuLink::getInstance(static::MENU_RECETTES, null, $this->generateUrl('liste_recettes', ['id' => $kermesse->getId()])))
            ->addLink(MenuLink::getInstance(static::MENU_MEMBRES_ACTIFS, null, $this->generateUrl('membres_actifs', ['id' => $kermesse->getId()])))
            ->addLink(MenuLink::getInstance(static::MENU_PLANNING, null, $this->generateUrl('planning_benevoles', ['id' => $kermesse->getId()])))
            ->setActiveLinkByName($activeName);
        return MenuLink::getInstance('Édition ' . $kermesse->getAnnee() , 'tag', '#')
            ->setMenu($subMenu)
            ->setActive($activeName !== '');
    }

    /**
     * @param Kermesse|null $kermesse
     * @param string|null $activeLink
     * @return Breadcrumb
     */
    protected function getMenu(?Kermesse $kermesse = null, string $activeLink = '') {
        $activeKermesse = empty($activeLink) ? $kermesse : null;
        $menu = Breadcrumb::getInstance(false)
            ->addLink(MenuLink::getInstance(static::MENU_ACCUEIL, 'home', $this->generateUrl('index'))->setActive($activeLink === static::MENU_ACCUEIL))
            ->addLink($this->getKermessesMenuLink($activeKermesse))
            ->addLink(MenuLink::getInstance(static::MENU_MEMBRES, 'users', $this->generateUrl('membres'))->setActive($activeLink === static::MENU_MEMBRES));
        if ($kermesse !== null) {
            $menu->addLink($this->getKermesseMenu($kermesse, $activeLink));
        }
        $menu->addLink(MenuLink::getInstance(static::MENU_ACTIVITES_AUTRES, 'stream', $this->generateUrl('lister_actions'))->setActive($activeLink === static::MENU_ACTIVITES_AUTRES));
        $menu->addLink(MenuLink::getInstance(static::MENU_BILAN, 'chart-pie', $this->generateUrl('show_bilan'))->setActive($activeLink === static::MENU_BILAN));
        return $menu;
    }

    /**
     * @return Etablissement
     */
    protected function getEtablissement(): Etablissement
    {
        $etablissement = $this->getUser();
        if (!$etablissement instanceof Etablissement) {
            throw new NotFoundHttpException("La page ne semble pas exister !");
        }
        return $etablissement;
    }
}
