<?php

namespace AlbumBundle\Controller;

use AlbumBundle\Service\LastFMService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class LastFMController
 * @package AlbumBundle\Controller
 */
class LastFMController extends Controller
{
    /** @var LastFMService $lastFMService */
    private $lastFMService;

    /**
     * LastFMController constructor.
     *
     * @param LastFMService $lastFMService
     */
    public function __construct(LastFMService $lastFMService)
    {
        $this->lastFMService = $lastFMService;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function getAlbumInfoAction(Request $request) {

        try {
            $albumName = $request->get("album");
            $artistName = $request->get("artist");

            $result = $this->lastFMService->getAlbumInfo($albumName, $artistName);

            $album = [];
            if (!empty($result['album'])) {

                if (!empty($result['album']['name'])) {
                    $album['name'] = $result['album']['name'];
                }

                if (!empty($result['album']['artist'])) {
                    $album['artist'] = $result['album']['artist'];
                }

                if (!empty($result['album']['mbid'])) {
                    $album['mbid'] = $result['album']['mbid'];
                }

                if (!empty($result['album']['image']) && !empty($result['album']['image'][2])) {
                    $album['image'] = $result['album']['image'][2]['#text'];
                }

                var_dump($album);die;

            }

            return new Response(json_encode(null));

        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage(), null, $e->getCode());
        }
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function getTopAlbumsAction(Request $request) {
        try {
            $searchTerm = $request->get('q');
            $limit = $this->getParameter('search_limit');

            $results = $this->lastFMService->searchAlbums($searchTerm, $limit);

            if (array_key_exists("albummatches", $results['results'])) {

                if (array_key_exists("album", $results['results']["albummatches"])) {

                    $albums = [];
                    foreach ($results['results']["albummatches"]["album"] as $albumMatch) {

                        $album = [];

                        if(array_key_exists("artist", $albumMatch)) {
                            $album['artist'] = $albumMatch['artist'];

                            if(array_key_exists('name', $albumMatch)) {
                                $album['name'] = $albumMatch['name'];
                            }
                        }

                        if (!empty($album)) {
                            $albums[] = $album;
                        }
                    }
                    return new Response(json_encode($albums));
                }
            }
            return new Response(null);
        }
        catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage(), null, $e->getCode());
        }
    }
}