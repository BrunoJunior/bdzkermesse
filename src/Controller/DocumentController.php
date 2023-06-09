<?php

namespace App\Controller;

use App\DataTransfer\MembreRow;
use App\Entity\Membre;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for documents management space
 * @package App\Controller
 * @author bruno <bdesprez@thalassa.fr>
 * @Route("/documents")
 */
class DocumentController extends MyController
{
    /**
     * @Route("/", name="espace_documentaire")
     * @return Response
     */
    public function index(): Response {
        return $this->render('document/index.html.twig', [
            'menu' => $this->getMenu(null, static::MENU_DOCUMENTS)
        ]);
    }
}