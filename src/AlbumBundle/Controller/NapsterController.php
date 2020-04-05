<?php


namespace AlbumBundle\Controller;


use AlbumBundle\Service\NapsterService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class NapsterController extends Controller
{
    /** @var NapsterService $napsterService */
    private $napsterService;

    /** @var string */
    private $apiKey;

    /**
     * NapsterController constructor.
     * @param NapsterService $napsterService
     */
    public function __construct(NapsterService $napsterService, $apiKey)
    {
        $this->napsterService = $napsterService;
        $this->apiKey = $apiKey;
    }

    /**
     * @return ResponseAlias
     */
    public function viewTopTracksAction() {

        $limit = $this->getParameter('napster_tracks_limit');
        $results = $this->napsterService->getTopTracks($limit, $this->apiKey);

        $tracks = [];
        if (null !== $results && array_key_exists('tracks', $results)) {

            $track = [];

            foreach ($results['tracks'] as $napsterTrack) {

                if (array_key_exists('name', $napsterTrack)) {
                    $track['name'] = $napsterTrack['name'];
                }

                if (array_key_exists('artistName', $napsterTrack)) {
                    $track['artistName'] = $napsterTrack['artistName'];
                 }

                if (array_key_exists('albumName', $napsterTrack)) {
                    $track['albumName'] = $napsterTrack['albumName'];
                }

                if (array_key_exists('previewURL', $napsterTrack)) {
                    $track['previewURL'] = $napsterTrack['previewURL'];
                }

                if (array_key_exists('albumId', $napsterTrack)) {

                    try {
                        $images = $this->get('napster.tracks')->getAlbumImage($napsterTrack['albumId'], $this->apiKey);


                        if (array_key_exists('images', $images)) {

                            if (null !== $images['images'][0] && array_key_exists('url', $images['images'][0])) {
                                $track['albumImage'] = $images['images'][0]['url'];
                            }
                        }
                    } catch (\Exception $e) {

                    }
                }

                if (!empty($track)) {
                    $tracks[] = $track;
                }

            }
        }

        return $this->render('@Album/Default/topTracks.html.twig', ['tracks' => $tracks]);
    }

}