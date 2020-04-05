<?php


namespace AlbumBundle\Service;


use AlbumBundle\Entity\FestivalsLocation;
use GuzzleHttp\Client;

class SkiddleService implements APIConsume
{
    /** @const string  */
    const SKIDDLE_FM_API = "https://www.skiddle.com/";

    /** @const string  */
    const API_VERSION = "api/v1/";

    /** @const string */
    const EVENTS = "/events/search/";

    /** @const description */
    const DEFAULT_DESCRIPTION = 1;

    /**
     * @param array $options
     *
     * @return mixed
     */
    private function consumeSkiddle(array $options) {

        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => self::SKIDDLE_FM_API,
            // the default time out
            'timeout' => 2.0,
            'default' => [
                // a returns response even if there is a failure on the server
                'exceptions' => false,
            ]]);

        $response = $client->get(self::API_VERSION . self::EVENTS, $options);

        $jsonData = $response->getBody()->getContents();

        return json_decode($jsonData, true);
    }

    /**
     * @param FestivalsLocation $festivalsLocation
     * @param $apiKey
     * @return mixed
     */
    public function consumeEvents(FestivalsLocation $festivalsLocation, $apiKey)
    {
        $options = [
            'query' => [
                'api_key' => $apiKey,
                'limit' => $festivalsLocation->getLimit(),
                'eventcode' => $festivalsLocation->getEvent(),
                'description' => self::DEFAULT_DESCRIPTION,
            ]
        ];

        return $this->consumeSkiddle($options);
    }
}