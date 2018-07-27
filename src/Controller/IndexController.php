<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class IndexController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function index(AuthenticationUtils $helper, AuthorizationCheckerInterface $authChecker)
    {
        if ($authChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->render('index/index.html.twig');
        }
        return $this->redirectToRoute('security_login', [$helper]);
    }
}
