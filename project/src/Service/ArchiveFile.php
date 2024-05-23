<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ArchiveFile
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
    }
    public function archiveFile($id, $path): array
    {
        $url = 'https://assets.e-medya.fr/remote.php/dav/files/e-medya/tickets/' . $path . '/' . $id;
        $moveUrl = 'https://assets.e-medya.fr/remote.php/dav/files/e-medya/tickets/archives/' . $path . '/' . 'archived_' . $id;
        $credentials = $_ENV['NEXTCLOUD_LOGIN'] . ':' . $_ENV['NEXTCLOUD_PASSWORD'];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'MOVE');
        curl_setopt($ch, CURLOPT_POSTFIELDS, '');
        curl_setopt($ch, CURLOPT_USERPWD, $credentials);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Destination: ' . $moveUrl));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 201) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Error archiving file'];
        }
    }

}