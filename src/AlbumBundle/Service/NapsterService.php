<?php


namespace AlbumBundle\Service;

use GuzzleHttp\Client;

class NapsterService implements APIConsume
{
    /** @const string  */
    const NAPSTER_API = "https://api.napster.com";

    /** @const string  */
    const API_VERSION = "/v2.2";

    /** @const string  */
    const TOP_TRACKS = "/tracks/top";

    /** @const string  */
    const ALBUMS = '/albums/';

    /** @const string  */
    const IMAGES = '/images';

    /**
     * @param array $options
     *
     * @param $uri
     * @return mixed
     */
    private function consumeMusic(array $options, $uri) {

        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => self::NAPSTER_API,
            // the default time out
            'timeout' => 2.0,
            'default' => [
                // a returns response even if there is a failure on the server
                'exceptions' => false,
            ]]);

        $response = $client->get(self::API_VERSION . $uri, $options);

        $jsonData = $response->getBody()->getContents();

        return json_decode($jsonData, true);
    }

    /**
     * @param $limit
     * @param $apiKey
     * @return mixed
     */
    public function getTopTracks($limit, $apiKey)
    {
        $options = [
            'query' => [
                'apikey' => $apiKey,
                'limit' => $limit,
            ]
        ];

        return $this->consumeMusic($options, self::TOP_TRACKS);
    }

    /**
     * @param $albumId
     * @param $apiKey
     * @return mixed
     */
    public function getAlbumImage($albumId, $apiKey)
    {
        $options = [
            'query' => [
                'apikey' => $apiKey,
            ]
        ];

        return $this->consumeMusic($options, self::ALBUMS . $albumId . self::IMAGES);
    }
}