<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactFormController extends AbstractController
{
    #[Route('/validate-form/contact', name: 'submit_contact_form', methods: 'POST')]

    public function submitForm(Request $request)
    {
        $formData = $request->request->all();

        $formData['id'] = $formData['subject'] . '_' . date('Y-m-d_H:i:s');

        $formData['type'] = 'contact';

        $jsonData = json_encode($formData, JSON_UNESCAPED_UNICODE);

        $fileName = $formData['subject'] . '_' . $formData['id'] . '.json';


        $url = 'https://assets.e-medya.fr/remote.php/dav/files/e-medya/tickets/contact/' . $fileName;

        $credentials = $_ENV['NEXTCLOUD_LOGIN'] . ':' . $_ENV['NEXTCLOUD_PASSWORD'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_USERPWD, $credentials);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return $this->redirect('https://e-medya.fr');
    }

}