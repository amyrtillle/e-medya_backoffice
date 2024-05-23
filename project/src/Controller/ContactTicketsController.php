<?php
// src/Controller/ContactTicketsController.php

namespace App\Controller;

use App\Service\GetFiles;
use App\Service\ArchiveFile;
use App\Service\RemoveFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ContactTicketsController extends AbstractController
{
    #[Route('/tickets/contact', name: 'app_contact_tickets')]
    public function new(GetFiles $getFiles): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $tickets = $getFiles->getCloudFiles('contact');

        return $this->render('tickets/contact.html.twig', [
            'message' => $tickets,
        ]);
    }

    #[Route('/tickets/contact/view/{fileid}', name: 'view_contact_ticket')]
    public function viewContactTicket(string $fileid): Response
    {
        return $this->redirectToRoute('view_ticket', ['type' => 'contact', 'fileId' => $fileid]);
    }

    #[Route('/tickets/contact/archive/{fileid}', name: 'archive_contact_ticket')]
    public function archiveContactTicket(string $fileid, ArchiveFile $archiveFile, Request $request): Response
    {
        $result = $archiveFile->archiveFile($fileid, 'contact');

        if ($result['success']) {
            $this->addFlash('success', 'File archived successfully.');
        } else {
            $this->addFlash('error', $result['message']);
        }

        return $this->redirectToRoute('app_contact_tickets');
    }

    #[Route('/tickets/contact/remove/{fileid}', name: 'remove_contact_ticket')]
    public function removeContactTicket(string $fileid, RemoveFile $removeFile, Request $request): Response
    {
        $result = $removeFile->removeFile($fileid, 'contact');

        if ($result['success']) {
            $this->addFlash('success', 'File removed successfully.');
        } else {
            $this->addFlash('error', $result['message']);
        }

        return $this->redirectToRoute('app_contact_tickets');
    }
}
