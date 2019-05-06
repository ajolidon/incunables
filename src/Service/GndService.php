<?php

namespace App\Service;


use GuzzleHttp\Client;

class GndService
{
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function get(string $gnd)
    {
        try {
            $response = $this->client->get('https://lobid.org/gnd/' . $gnd . '.json');
        }catch(\Exception $e){
            return [];
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getByKey(string $gnd, string $key, $default = null)
    {
        $result = $this->get($gnd);
        if(isset($result[$key])){
            return $result[$key];
        }

        return $default;
    }
}