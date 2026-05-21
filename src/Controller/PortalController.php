<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PortalController extends AbstractController
{
    #[Route('/portal', name: 'app_portal')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        return $this->render('portal/portal.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}
