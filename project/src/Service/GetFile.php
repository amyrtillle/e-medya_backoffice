<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class GetFile
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
    }
    public function getCloudFile($path, $fileid): array
    {
        $url = 'https://assets.e-medya.fr/remote.php/dav/files/e-medya/tickets/' . $path . '/' . $fileid;

        $credentials = $_ENV['NEXTCLOUD_LOGIN'] . ':' . $_ENV['NEXTCLOUD_PASSWORD'];
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_USERPWD, $credentials);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            // Handle cURL error
            throw new \RuntimeException('cURL error: ' . curl_error($ch));
        }
        curl_close($ch);

        // Check if cURL response is empty or not successful
        if (!$result) {
            throw new \RuntimeException('Failed to retrieve file from remote server.');
        }

        $result = json_decode($result, true);

        if ($result === null || !is_array($result)) {
            throw new \RuntimeException('Failed to decode JSON response.');
        }

        return $result;
    }


}

