<?php
// src/Controller/TicketController.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\GetFile;
use App\Service\ArchiveFile;
use App\Service\RemoveFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class TicketController extends AbstractController
{
    #[Route('/ticket/view/{type}/{fileId}', name: 'view_ticket')]
    public function viewTicket(string $type, string $fileId, GetFile $getFile): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $ticket = $getFile->getCloudFile($type, $fileId);

        return $this->render('tickets/view.html.twig', [
            'ticketData' => $ticket,
        ]);
    }

    #[Route('/tickets/{type}/archive/{fileid}', name: 'archive_ticket')]
    public function archiveTicket(string $type, string $fileid, ArchiveFile $archiveFile, Request $request): Response
    {
        $result = $archiveFile->archiveFile($fileid, $type);

        if ($result['success']) {
            $this->addFlash('success', 'File archived successfully.');
        } else {
            $this->addFlash('error', $result['message']);
        }

        return $this->redirectToRoute('app_' . $type . '_tickets');

    }

    #[Route('/tickets/{type}/remove/{fileid}', name: 'remove_ticket')]
    public function removeTicket(string $type, string $fileid, RemoveFile $removeFile, Request $request): Response
    {
        $result = $removeFile->removeFile($fileid, $type);

        if ($result['success']) {
            $this->addFlash('success', 'File removed successfully.');
        } else {
            $this->addFlash('error', $result['message']);
        }

        return $this->redirectToRoute('app_' . $type . '_tickets');

    }

    #[Route('/tickets/{type}/download/{fileId}', name: 'download_ticket')]
    public function downloadTicket(string $type, string $fileId, GetFile $getFile): Response
    {
        $ticket = $getFile->getCloudFile($type, $fileId);

        if (isset($ticket['scenarioFileData'])) {
            $decodedData = base64_decode($ticket['scenarioFileData']);
            $tempFilePath = tempnam(sys_get_temp_dir(), 'ticket_');
            file_put_contents($tempFilePath, $decodedData);

            $response = new BinaryFileResponse($tempFilePath);
            $response->headers->set('Content-Type', 'application/pdf');
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'ticket.pdf'
            );

            return $response;
        }

        // Handle if file data not found or not in PDF format
        throw $this->createNotFoundException('File not found or not in PDF format.');
    }
}
