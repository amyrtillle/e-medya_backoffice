<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetFiles
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
    }
    public function getCloudFiles($path): array
    {

        $url = 'https://assets.e-medya.fr/remote.php/dav/files/e-medya/tickets/' . $path . '/';

        $credentials = $_ENV['NEXTCLOUD_LOGIN'] . ':' . $_ENV['NEXTCLOUD_PASSWORD'];
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PROPFIND');

        curl_setopt($ch, CURLOPT_POSTFIELDS, '<?xml version="1.0" encoding="UTF-8"?>
 <d:propfind xmlns:d="DAV:">
   <d:prop xmlns:oc="http://owncloud.org/ns">
     <d:getlastmodified/>
     <d:resourcetype/>
     <d:displayname />
   </d:prop>
 </d:propfind>');
        curl_setopt($ch, CURLOPT_USERPWD, $credentials);

        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $result = str_replace('d:', '', $result);
        $result = str_replace('oc:', '', $result);
        $xml = simplexml_load_string($result) or die ("Error: Cannot create object");

        $tickets = array();

        foreach ($xml->response as $response) {
            if ($response->propstat->status == 'HTTP/1.1 404 Not Found' || $response->href == '/remote.php/dav/files/e-medya/tickets/contact/' || $response->href == '/remote.php/dav/files/e-medya/tickets/pricing/') {
                continue;
            } else {
                $href = $response->href;
                $getlastmodified = $response->propstat->prop->getlastmodified;
                $getcontenttype = $response->propstat->prop->getcontenttype;
                $fileid = $response->propstat->prop->displayname;
                $tickets[] = array('href' => $href, 'getlastmodified' => $getlastmodified, 'getcontenttype' => $getcontenttype, 'fileid' => $fileid);
            }
        }

        return $tickets;
    }
}