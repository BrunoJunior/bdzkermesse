<?php

namespace App\Controller;

use App\Entity\Etablissement;
use App\Entity\Membre;
use App\Form\EtablissementType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class AdminController
 * @package App\Controller
 * @Route("/admin")
 */
class AdminController extends MyController
{
    /**
     * @Route("/php", name="infos_php")
     */
    public function getInfo(): Response
    {
        ob_start();
        phpinfo();
        $str = ob_get_contents();
        ob_get_clean();
        return new Response($str);
    }

    /**
     * @Route("/", name="admin")
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'menu' => $this->getMenu(null, static::MENU_ADMIN)
        ]);
    }

    /**
     * @Route("/registration", name="registration")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     */
    public function registration(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $etablissement = new Etablissement();
        $form = $this->createForm(EtablissementType::class, $etablissement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($etablissement, $etablissement->getPassword());
            $etablissement->setPassword($password);
            // Ajout d'un membre par défaut (Membre établissement)
            $partiesNom = explode(' ', $etablissement->getNom());
            $dftMembre = new Membre();
            $dftMembre->setDefaut(true);
            $dftMembre->setEmail("");
            $dftMembre->setPrenom(array_shift($partiesNom));
            $dftMembre->setNom(implode(' ', $partiesNom));
            $etablissement->addMembre($dftMembre);
            // On enregistre l'utilisateur dans la base
            $em = $this->getDoctrine()->getManager();
            $em->persist($dftMembre);
            $em->persist($etablissement);
            $em->flush();
            return $this->redirectToRoute('security_login');
        }
        return $this->render('registration/nouveau.html.twig', [
            'form' => $form->createView(),
            'menu' => $this->getMenu(null, static::MENU_ADMIN)
        ]);
    }
}
