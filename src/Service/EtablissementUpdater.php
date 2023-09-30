<?php

namespace App\Service;

use App\Entity\Etablissement;
use App\Form\EtablissementType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class EtablissementUpdater
 * @package App\Service
 * @author bruno <bdesprez@thalassa.fr>
 */
class EtablissementUpdater {

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * Dummy constructor
     * @param EntityManagerInterface $em
     * @param RouterInterface $router
     * @param FormFactoryInterface $formFactory
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(EntityManagerInterface $em, RouterInterface $router, FormFactoryInterface $formFactory, UserPasswordEncoderInterface $passwordEncoder) {
        $this->formFactory = $formFactory;
        $this->em = $em;
        $this->router = $router;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * Traiter la demande de modification de mise à jour (get + post)
     * @param Request $request
     * @param Etablissement $etablissement
     * @param string|null $action
     * @return FormInterface|null
     */
    public function traiterDemande(Request $request, Etablissement $etablissement, ?string $action = null): ?FormInterface {
        $oldPassword = $etablissement->getPassword();
        $form = $this->formFactory->create(
            EtablissementType::class,
            $etablissement,
            [
                'action' => $action ?: $this->router->generate('admin_update_etab', ['id' => $etablissement->getId()]),
                'isAdmin' => $action === null
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($etablissement->getPassword() === '') {
                $password = $oldPassword;
            } else {
                $password = $this->passwordEncoder->encodePassword($etablissement, $etablissement->getPassword());
            }
            $etablissement->setPassword($password);
            // On enregistre l'utilisateur dans la base
            $this->em->persist($etablissement);
            $this->em->flush();
            return null;
        }
        return $form;
    }
}
