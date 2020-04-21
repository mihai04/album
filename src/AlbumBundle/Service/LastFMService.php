<?php


namespace AlbumBundle\Service;


use AlbumBundle\Controller\LastFMMethod;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LastFMService
 * @package AlbumBundle\Helper
 */
class LastFMService implements APIConsume
{
    /** @const string  */
    const LAST_FM_API = "http://ws.audioscrobbler.com";

    /** @const string  */
    const JSON_FORMAT = "json";

    /** @const string  */
    const API_VERSION = "/2.0/";

    /**
     * @param array $options
     *
     * @return mixed
     */
    private function consumeMusic(array $options) {

        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => self::LAST_FM_API,
            // the default time out
            'timeout' => 2.5,
            'default' => [
                //returns a response even if there is a failure on the server
                'exceptions' => false,
            ]]);

        $response = $client->get(self::API_VERSION, $options);

        $jsonData = $response->getBody()->getContents();

        return json_decode($jsonData, true);
    }

    /**
     * @param string $name
     * @param integer $limit
     *
     * @param $apiKey
     * @return mixed
     */
    public function searchAlbums($name, $limit, $apiKey)
    {
        $options = [
            'query' => [
                'method' => LastFMMethod::SEARCH_ALBUM,
                'api_key' => $apiKey,
                'album' => $name,
                'limit' => $limit,
                'format' => self::JSON_FORMAT
            ]
        ];

        return $this->consumeMusic($options);
    }

    /**
     * @param $albumName
     * @param $artistName
     *
     * @param $apiKey
     * @return mixed
     */
    public function getAlbumInfo($albumName, $artistName, $apiKey)
    {
        $options = [
            'query' => [
                'method' => LastFMMethod::ALBUM_INFO,
                'api_key' => $apiKey,
                'artist' => $artistName,
                'album' => $albumName,
                'format' => self::JSON_FORMAT
            ]
        ];

        return $this->consumeMusic($options);
    }

    /**
     * @param $artistName
     * @param $trackName
     *
     * @param $apiKey
     * @return mixed
     */
    public function getTrackInfo($artistName, $trackName, $apiKey)
    {
        $options = [
            'query' => [
                'method' => LastFMMethod::TRACK_INFO,
                'api_key' => $apiKey,
                'artist' => $artistName,
                'track' => $trackName,
                'format' => self::JSON_FORMAT
            ]
        ];

        return $this->consumeMusic($options);
    }

    /**
     * @param $limit
     * @param $apiKey
     *
     * @return mixed
     */
    public function getTopTracks($limit, $apiKey) {

        $options = [
            'query' => [
                'method' => LastFMMethod::CHART_TOP_TRACKS,
                'api_key' => $apiKey,
                'limit' => $limit,
                'format' => self::JSON_FORMAT
            ]
        ];

        return $this->consumeMusic($options);
    }

    /**
     * @param $artist
     * @param $limit
     * @param $apiKey
     *
     * @return mixed
     */
    public function getSimilar($artist, $limit, $apiKey) {

        $options = [
            'query' => [
                'method' => LastFMMethod::ARTIST_SIMILAR,
                'api_key' => $apiKey,
                'artist' => $artist,
                'limit' => $limit,
                'format' => self::JSON_FORMAT

            ]
        ];

        return $this->consumeMusic($options);
    }
}