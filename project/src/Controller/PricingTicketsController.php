<?php
namespace App\Controller;

use App\Service\GetFiles;
use App\Service\ArchiveFile;
use App\Service\RemoveFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PricingTicketsController extends AbstractController
{
    #[Route('/tickets/pricing', name: 'app_pricing_tickets')]
    public function new(GetFiles $getFiles): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $tickets = $getFiles->getCloudFiles('pricing');

        return $this->render('tickets/pricing.html.twig', [
            'message' => $tickets,
        ]);
    }

    #[Route('/tickets/pricing/view/{fileid}', name: 'view_pricing_ticket')]
    public function viewPricingTicket(string $fileid): RedirectResponse
    {
        return $this->redirectToRoute('view_ticket', ['type' => 'pricing', 'fileId' => $fileid]);
    }

    #[Route('/tickets/pricing/archive/{fileid}', name: 'archive_ticket')]
    public function archiveTicket(string $fileid, ArchiveFile $archiveFile, Request $request): Response
    {
        $result = $archiveFile->archiveFile($fileid, 'pricing');

        if ($result['success']) {
            $this->addFlash('success', 'File archived successfully.');
        } else {
            $this->addFlash('error', $result['message']);
        }

        return $this->redirectToRoute('app_pricing_tickets');
    }

    #[Route('/tickets/pricing/remove/{fileid}', name: 'remove_ticket')]
    public function removeTicket(string $path, string $fileid, RemoveFile $removeFile, Request $request): Response
    {
        $result = $removeFile->removeFile($path, $fileid);

        if ($result['success']) {
            $this->addFlash('success', 'File removed successfully.');
        } else {
            $this->addFlash('error', $result['message']);
        }

        return $this->redirectToRoute('app_pricing_tickets');
    }
}