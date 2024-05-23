<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PricingFormController extends AbstractController
{
    #[Route('/validate-form/pricing', name: 'submit_pricing_form', methods: ['POST'])]
    public function submitForm(Request $request): Response
    {
        // Retrieve form data
        $formData = $request->request->all();

        // Handle file uploads
        $scenarioFile = $request->files->get('scenarioFile');
        $visualIdentityFile = $request->files->get('visualIdentityFile');

        // Check if files were uploaded and serialize them to base64
        $scenarioFileData = $scenarioFile ? base64_encode(file_get_contents($scenarioFile->getPathname())) : null;
        $visualIdentityFileData = $visualIdentityFile ? base64_encode(file_get_contents($visualIdentityFile->getPathname())) : null;

        // Add file data to form data
        $formData['scenarioFileData'] = $scenarioFileData;
        $formData['visualIdentityFileData'] = $visualIdentityFileData;

        // Generate a unique form ID
        $formData['id'] = $formData['subject'] . '_' . date('Y-m-d_H:i:s');
        $formData['type'] = 'pricing';

        // Convert form data to JSON
        $jsonData = json_encode($formData);

        // Generate a unique filename
        $fileName = $formData['subject'] . '_' . $formData['id'] . '.json';

        // Upload JSON file to Nextcloud
        $this->uploadFileToNextcloud($jsonData, $fileName);

        // Return a response
        return $this->redirect('https://e-medya.fr');
    }

    private function uploadFileToNextcloud($data, $fileName): void
    {
        $url = 'https://assets.e-medya.fr/remote.php/dav/files/e-medya/tickets/pricing/' . $fileName;

        $credentials = $_ENV['NEXTCLOUD_LOGIN'] . ':' . $_ENV['NEXTCLOUD_PASSWORD'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_USERPWD, $credentials);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $result = curl_exec($ch);
        curl_close($ch);
    }
}

