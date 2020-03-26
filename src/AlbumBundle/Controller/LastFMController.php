<?php

namespace AlbumBundle\Controller;

use AlbumBundle\Entity\Album;
use AlbumBundle\Entity\Track;
use AlbumBundle\Service\LastFMService;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\Material\BarChart;
use Pagerfanta\Exception\Exception;
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

        /** @var Album $album */
        $album = new Album();
        $tracks = [];

        try {
            $albumName = $request->get("album");
            $artistName = $request->get("artist");

            $result = $this->lastFMService->getAlbumInfo($albumName, $artistName);

            if (array_key_exists('album', $result)) {

                if (array_key_exists('name', $result['album'])) {
                    $album->setTitle($result['album']['name']);
                }

                if (array_key_exists('artist',$result['album'])) {
                    $album->setArtist($result['album']['artist']);
                }

                if (array_key_exists('mbid',$result['album'])) {
                    $album->setIsrc($result['album']['mbid']);
                }

                if (array_key_exists('mbid',$result['album'])) {
                    $album->setUrl($result['album']['url']);
                }

                if (array_key_exists('image', $result['album'])) {
                    if (!empty($result['album']['image'][2])) {
                        if ($result['album']['image'][2]['#text']) {
                            $album->setImage($result['album']['image'][2]['#text']);
                        }
                    }
                }

                if (array_key_exists('wiki', $result['album'])) {

                    if (array_key_exists('published', $result['album']['wiki'])) {
                        $album->setPublished($result['album']['wiki']['published']);
                    }
                }

                if (array_key_exists('playcount',$result['album'])) {
                    $album->setPlaycount($result['album']['playcount']);
                }

                if (array_key_exists('listeners',$result['album'])) {
                    $album->setListeners($result['album']['listeners']);
                }

                if (array_key_exists('listeners',$result['album'])) {
                    $album->setListeners($result['album']['listeners']);
                }

                if (array_key_exists('tracks', $result['album'])) {

                    if (array_key_exists('track', $result['album']['tracks'])) {

                        foreach ($result['album']['tracks']['track'] as $trackEntry) {

                            $track = new Track();

                            if (!empty($trackEntry['name'])) {
                                $track->setTrackName($trackEntry['name']);
                            }

                            if (!empty($trackEntry['duration'])) {

                                $time = $this->getHoursMinutes($trackEntry['duration']);
                                $track->setDuration($time);
                            }

                            if (!empty($track)) {
                                $track->setAlbum($album);
                                $tracks[] = $track;
                            }
                        }
                    }
                }

                if (array_key_exists('tags', $result['album'])) {
                    if (array_key_exists('tag', $result['album']['tags'])) {
                        $tagData = "";
                        foreach ($result['album']['tags']['tag'] as $tag) {
                            if (array_key_exists('name', $tag)) {;
                                $tagData .= $tag['name'] . ', ';
                            }
                        }
                        $replacedTagData = rtrim($tagData, ", ");
                        $album->setTags($replacedTagData);
                    }
                }
            }

            $similar = $this->lastFMService->getSimilar($artistName, $this->getParameter('similar_artist'));

            $similarArtists = [];
            if (array_key_exists('similarartists', $similar)) {

                if(array_key_exists('artist', $similar['similarartists'])) {

                    $similarArtist = [];
                    foreach ($similar['similarartists']['artist'] as $artistEntry) {

                        if (array_key_exists('name', $artistEntry)) {
                            $similarArtist['name'] = $artistEntry['name'];
                        }

                        if (array_key_exists('url', $artistEntry)) {
                            $similarArtist['url'] = $artistEntry['url'];
                        }

                        if (!empty($similarArtist)) {
                            $similarArtists[] = $similarArtist;
                        }
                    }
                }
            }

            return $this->render('AlbumBundle:Default:viewSearchedAlbum.html.twig', [
                    'album' => $album,
                    'tracks' => $tracks,
                    'similarArtists' => $similarArtists
                ]
            );

        } catch (\Exception $e) {
            return $this->render('AlbumBundle:Default:viewSearchedAlbum.html.twig', [
                    'error' => 'Failed to retrieve data!',
                ]
            );
        }
    }

    function getHoursMinutes($seconds, $format = '%02d:%02d') {

        if (empty($seconds) || ! is_numeric($seconds)) {
            return false;
        }

        $minutes = round($seconds / 60);
        $remainMinutes = ($minutes % 60);

        return sprintf($format, $minutes, $remainMinutes);
    }

//try {
//
//$em = $this->getDoctrine()->getManager();
//$em->persist($album);
//$em->flush();
//
//} catch (\Exception $e) {
//    var_dump($e);die;
//}

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function getAlbumsAction(Request $request) {
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

    /**
     * @return Response
     */
    public function getStatsAction()
    {
        $bar = null;
        try {

            $limit = $this->getParameter('trending_tracks_limit');
            $results = $this->lastFMService->getTopTracks($limit);

            $tracks = [];
            if (array_key_exists('tracks', $results)) {

                $track = [];

                for ($x = 0; $x < count($results['tracks']['track']); $x++) {

                    if (array_key_exists('track', $results['tracks'])) {

                        if (!empty($results['tracks']['track'])) {

                            if (!empty($results['tracks']['track'][$x])) {

                                if (!empty($results['tracks']['track'][$x]['name'])) {
                                    $track['name'] = $results['tracks']['track'][$x]['name'];
                                }

                                if (!empty($results['tracks']['track'][$x]['listeners'])) {
                                    $track['listeners'] = number_format($results['tracks']['track'][$x]['listeners']);
                                }

                                if (!empty($results['tracks']['track'][$x]['playcount'])) {
                                    $track['playcount'] = number_format($results['tracks']['track'][$x]['playcount']);
                                }

                                if (array_key_exists('artist', $results['tracks']['track'][$x])) {
                                    if (!empty($results['tracks']['track'][$x]['artist']['name'])) {
                                        $track['name'] .= ' by ' . $results['tracks']['track'][$x]['artist']['name'];
                                    }
                                }
                                if (!empty($track)) {
                                    $tracks[] = $track;
                                }
                            }
                        }
                    }
                }

                $bar = new BarChart();

                $sortAscTracks = $this->array_sort($tracks, 'playcount', SORT_ASC);

                $chartArray = array(['Tracks', 'Listeners', 'Play Count']);
                foreach ($sortAscTracks as $entry) {
                    array_push($chartArray, $entry);
                }

                $bar->getData()->setArrayToDataTable($chartArray);
                $bar->getOptions()->setTitle('Trending Tracks:')->setFontSize(26);
                $bar->getOptions()->getHAxis()->setTitle('Trending Tracks based on Play Count.');
                $bar->getOptions()->getHAxis()->setMinValue(0);
                $bar->getOptions()->getVAxis()->setTitle('Albums');
                $bar->getOptions()->setWidth(1140);
                $bar->getOptions()->setHeight(600);

                return $this->render("@Album/Default/event.html.twig", ['bar' => $bar]);
            }
            return $this->render("@Album/Default/event.html.twig", ['error' => 'Unavailable Data']);
        } catch (Exception $e) {
            return $this->render("@Album/Default/event.html.twig", ['error' => $e->getMessage()]);
        }
    }

    /**
     * @param $array
     * @param $on
     * @param int $order
     * @return array
     */
    private function array_sort($array, $on, $order=SORT_ASC){

        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }
}