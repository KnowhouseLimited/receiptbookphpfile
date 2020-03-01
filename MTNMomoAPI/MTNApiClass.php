<?php


class MTNApiClass
{

    public function  createUUID()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public function createAPIUSer($xReference)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://sandbox.momodeveloper.mtn.com/v1_0/apiuser",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\r\n  \"providerCallbackHost\": \"https://webhook.site/1672ce3c-5593-45a7-a247-4970cc0c89ff\"\r\n}",
            CURLOPT_HTTPHEADER => array(
                "X-Reference-Id: $xReference",
                "Ocp-Apim-Subscription-Key: 431be0a47b674d57b7c64f247161b3f2",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public function getCreatedUser($xReference)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://sandbox.momodeveloper.mtn.com/v1_0/apiuser/f5cbb733-47fb-4017-983a-a6cd48c5c01a",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Ocp-Apim-Subscription-Key: 431be0a47b674d57b7c64f247161b3f2"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;

    }

    public function getApiKey($xReference)
    {


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://sandbox.momodeveloper.mtn.com/v1_0/apiuser/$xReference/apikey",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                "Ocp-Apim-Subscription-Key: 431be0a47b674d57b7c64f247161b3f2",
                "Content-Length: 0"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;

    }

    public function generatingApiToken($xReference,$apiKey)
    {

        $username = $xReference;
        $password = $apiKey;
        $auth = $username . ':' . $password;
        $credentials = base64_encode($auth);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://sandbox.momodeveloper.mtn.com/collection/token/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                "Ocp-Apim-Subscription-Key: 431be0a47b674d57b7c64f247161b3f2",
                "Content-Length: 0",
                "Authorization: Basic $credentials"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;

    }

}