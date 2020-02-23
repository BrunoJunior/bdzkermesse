<?php

namespace App\Controller;

use App\Business\MembreBusiness;
use App\DataTransfer\ContactDTO;
use App\DataTransfer\MembreRow;
use App\Entity\Membre;
use App\Form\ContactType;
use App\Form\MembreType;
use App\Repository\MembreRepository;
use App\Repository\RemboursementRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MembreController extends MyController
{
    /**
     * @var MembreBusiness
     */
    private $business;
    /**
     * @var MembreRepository
     */
    private $repo;

    /**
     * MembreController constructor.
     * @param MembreBusiness $business
     * @param MembreRepository $repo
     * @param LoggerInterface $logger
     */
    public function __construct(MembreBusiness $business, MembreRepository $repo, LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->business = $business;
        $this->repo = $repo;
    }

    /**
     * @Route("/membres", name="membres")
     * @param RemboursementRepository $rRemboursement
     * @return Response
     */
    public function index(RemboursementRepository $rRemboursement):Response
    {
        $etablissement = $this->getEtablissement();
        $montantParMembre = $this->repo->getMontantsNonRemboursesParMembre($etablissement);
        $montantEnAttenteParMembre = $this->repo->getMontantsAttenteRemboursementParMembre($etablissement);
        $premiersRbsts = $rRemboursement->findPremierEnAttenteParMembre($etablissement);
        return $this->render('membre/index.html.twig', [
            'membres' => array_map(function (Membre $membre) use($montantParMembre, $montantEnAttenteParMembre, $premiersRbsts) {
                return MembreRow::getInstance($membre)
                    ->setMontantNonRembourse($montantParMembre->get($membre->getId()))
                    ->setMontantAttenteRemboursement($montantEnAttenteParMembre->get($membre->getId()))
                    ->setPremierRemboursementEnAttente($premiersRbsts->get($membre->getId()));
            }, $etablissement->getMembres()->toArray()),
            'menu' => $this->getMenu(null, static::MENU_MEMBRES)
        ]);
    }

    /**
     * @Route("/membres/save/{id<\d+>?}", name="save_membre")
     * @param Request $request
     * @param Membre|null $membre
     * @return Response
     */
    public function saveMembre(Request $request, ?Membre $membre = null): Response
    {
        $membre = $membre ?: new Membre();
        $membre->setEtablissement($this->getEtablissement());
        $form = $this->createForm(MembreType::class, $membre, ['action' => $this->generateUrl('save_membre', ['id' => $membre->getId()])]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // On enregistre l'utilisateur dans la base
            $em = $this->getDoctrine()->getManager();
            $em->persist($membre);
            $em->flush();
            return $this->reponseModal();
        }
        return $this->render('membre/form.html.twig', ['form' => $form->createView(), 'edition' => $membre->getId()]);
    }

    /**
     * @Route("/membres/{id}/contact", name="contacter_membre")
     * @Security("membre.isProprietaire(user)")
     * @param Membre $membre
     * @param Request $request
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function contacterMembre(Membre $membre, Request $request): Response
    {
        $contact = $this->business->initialiserContact($membre, new ContactDTO());
        $form = $this->createForm(ContactType::class, $contact, [
            'action' => $this->generateUrl('contacter_membre', ['id' => $membre->getId()])
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->business->contacter($membre, $contact) === 0) {
                throw new ServiceUnavailableHttpException("Erreur lors de l'envoi du message !");
            }
            return $this->reponseModal('E-mail envoyé !');
        }
        return $this->render('membre/contact_form.html.twig', ['form' => $form->createView()]);
    }
}
