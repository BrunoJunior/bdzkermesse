<?php

namespace App\Service;

use App\Entity\Etablissement;
use App\Enum\InscriptionStatutEnum;
use App\Form\ResetPasswordType;
use App\Repository\EtablissementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Service de reset de password
 * @package App\Service
 * @author bruno <bdesprez@thalassa.fr>
 */
class PasswordResetter {
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var EtablissementRepository
     */
    private $rEtab;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * Dummy constructor
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param FormFactoryInterface $formFactory
     * @param EntityManagerInterface $em
     * @param EtablissementRepository $rEtab
     * @param RouterInterface $router
     */
    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $em,
        EtablissementRepository $rEtab,
        RouterInterface $router
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->formFactory = $formFactory;
        $this->em = $em;
        $this->rEtab = $rEtab;
        $this->router = $router;
    }

    /**
     * @param Request $request
     * @param Etablissement|null $etablissement
     * @param bool $isValidation
     * @return FormInterface|null
     */
    private function gererFormulaire(Request $request, ?Etablissement $etablissement, bool $isValidation = false): ?FormInterface {
        if (null === $etablissement) {
            throw new NotFoundHttpException();
        }
        $id = $isValidation ? $etablissement->getOriginInscription()->getId() : $etablissement->getId();
        $key = $etablissement->getResetPwdKey();
        $routeName = $isValidation ? 'validation_email' : 'reset_pwd';
        $form = $this->formFactory->create(ResetPasswordType::class, null, [
            'action' => $this->router->generate($routeName, ['id' => $id, 'key' => $key]),
            'etablissement' => $etablissement,
            'origine' => $isValidation ? $etablissement->getOriginInscription() : null
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $etablissement->setPassword($this->passwordEncoder->encodePassword($etablissement, $form->get('password')->getData()));
            $etablissement->setResetPwdKey(null);
            $demandeInsc = $etablissement->getOriginInscription();
            if ($isValidation) {
                $demandeInsc->setState(InscriptionStatutEnum::VALIDEE);
                $this->em->persist($demandeInsc);
            }
            $this->em->persist($etablissement);
            $this->em->flush();
            return null;
        }
        return $form;
    }

    /**
     * Méthode pour gérer le formulaire de demande de réinitialisation de mot de passe
     * @param Request $request
     * @param int $id
     * @param string $key
     * @return FormInterface|null
     * @throws NonUniqueResultException
     */
    public function reset(Request $request, int $id, string $key): ?FormInterface {
        $etablissement = $this->rEtab->findOneByIdAndKey($id, $key);
        return $this->gererFormulaire($request, $etablissement);
    }

    /**
     * Méthode pour gérer le formulaire de validation de compte
     * @param Request $request
     * @param int $id
     * @param string $key
     * @return FormInterface|null
     * @throws NonUniqueResultException
     */
    public function validerEmail(Request $request, int $id, string $key): ?FormInterface {
        $etablissement = $this->rEtab->findOneByIdInscriptionAndKey($id, $key);
        return $this->gererFormulaire($request, $etablissement, true);
    }
}
