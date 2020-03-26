<?php


namespace AlbumBundle\Service;


use AlbumBundle\Entity\FestivalsLocation;
use GuzzleHttp\Client;

class SkiddleService
{
    /** @const string  */
    const SKIDDLE_FM_API = "https://www.skiddle.com/";

    /** @const string  */
    const API_VERSION = "api/v1/";

    /** @const string */
    const EVENTS = "/events/search/";

    /** @const string  */
    const SKIDDLE_API_KEY = "bef4d493e49f9bd7ac87100d7b4f4570";

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
     * @return mixed
     */
    public function consumeEvents(FestivalsLocation $festivalsLocation)
    {
        $options = [
            'query' => [
                'api_key' => self::SKIDDLE_API_KEY,
                'limit' => $festivalsLocation->getLimit(),
                'eventcode' => $festivalsLocation->getEvent(),
                'description' => self::DEFAULT_DESCRIPTION,
            ]
        ];

        return $this->consumeSkiddle($options);
    }
}