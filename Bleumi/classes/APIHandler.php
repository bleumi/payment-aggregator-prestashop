<?php

class APIHandler
{
    public static $endpoint_url = 'https://api.bleumi.io/v1/payment/';
    
    public static function getCurlResponse($data, $operation)
    {
        $ch = curl_init(self::$endpoint_url);
        
        $headers = array();
        $headers[] = 'X-Api-Key: ' . Configuration::get('BLEUMI_API_KEY');
        $headers[] = 'Content-Type: application/json';
        
        if ($operation === "POST") {
            $data_to_post = json_encode($data);

            PrestaShopLogger::addLog('[BLEUMI] APIHandler::getCurlResponse: url ' . self::$endpoint_url, 1);
            PrestaShopLogger::addLog('[BLEUMI] APIHandler::getCurlResponse: body ' . $data_to_post, 1);

            curl_setopt($ch, CURLOPT_URL, self::$endpoint_url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_to_post);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch,CURLOPT_TIMEOUT,120);
        } else {
            $url = self::$endpoint_url . $data;
            PrestaShopLogger::addLog('[BLEUMI] APIHandler::getCurlResponse: url ' . $url, 1);

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch,CURLOPT_TIMEOUT,120);
        }
        
        $response = json_decode(curl_exec($ch), true);

        PrestaShopLogger::addLog('[BLEUMI] APIHandler::getCurlResponse: response ' . json_encode($response), 1);

        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_status === 200)
            return $response;
    }
}
