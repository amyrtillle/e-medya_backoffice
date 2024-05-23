<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TicketsHomeController extends AbstractController
{
    #[Route('/', name: 'app_tickets_home')]
    public function __invoke(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        return $this->render('tickets/types.html.twig');
    }
    #[Route('/tickets/types', name: 'app_tickets_types')]

    public function ticketsTypes(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        return $this->render('tickets/types.html.twig');
    }


}