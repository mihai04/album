<?php

namespace AlbumBundle\Controller;

use AlbumBundle\Entity\Album;
use AlbumBundle\Entity\AlbumResult;
use AlbumBundle\Helper\AlbumHelper;
use AlbumBundle\Service\LastFMService;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\Material\BarChart;
use Pagerfanta\Exception\Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class LastFMController
 * @package AlbumBundle\Controller
 */
class LastFMController extends Controller
{
    /** @var LastFMService $lastFMService */
    private $lastFMService;

    /** @const string  */
    const INDICES = 'indices';

    /** @const string  */
    const POPULATE_SEARCH_ENTITIES = 'populate:search:entities';

    /** @var string */
    private $apiKey;

    /**
     * LastFMController constructor.
     *
     * @param LastFMService $lastFMService
     * @param $apiKey
     */
    public function __construct(LastFMService $lastFMService, $apiKey)
    {
        $this->lastFMService = $lastFMService;
        $this->apiKey = $apiKey;
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

            $result = $this->lastFMService->getAlbumInfo($albumName, $artistName, $this->apiKey);

            /** @var AlbumResult $albumResult */
            $albumResult = AlbumHelper::processAlbum($result);

            $similar = $this->lastFMService->getSimilar($artistName, $this->getParameter('similar_artist'),
                $this->apiKey);
            $similarArtists = AlbumHelper::processSimilarArtistsResults($similar);

            return $this->render('AlbumBundle:Default:viewSearchedAlbum.html.twig', [
                    'album' => $albumResult->getAlbum(),
                    'tracks' => $albumResult->getTracks(),
                    'similarArtists' => $similarArtists
                ]
            );

        } catch (\Exception $e) {
            return $this->render('AlbumBundle:Default:viewSearchedAlbum.html.twig', [
                    'error' => 'Failed to retrieve data! Error' . $e->getMessage(),
                ]
            );
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    function saveAlbumAction(Request $request) {

        try {

            $albumName = $request->get("album");
            $artistName = $request->get("artist");

            $result = $this->lastFMService->getAlbumInfo($albumName, $artistName, $this->apiKey);

            /** @var AlbumResult $albumResult */
            $albumResult = AlbumHelper::processAlbum($result);

            $em = $this->getDoctrine()->getManager();

            /** @var Album $album */
            $album = $albumResult->getAlbum();

            $em->persist($album);

            $tracks = $albumResult->getTracks();
            foreach ($tracks as $track) {
                $em->persist($track);
            }

            $em->flush();

            $this->updateEntitiesCommand();
            $this->addFlash('success', 'Album '. $album->getTitle() .' was successfully created.');

        }
        catch (\Exception $e) {
            if ($e->getPrevious()->getCode() === '23000') {
                return $this->render('AlbumBundle:Default:index.html.twig',
                    ['error' => 'A valid, unique, non-existing ISRC is required']);
            }
            return $this->render('AlbumBundle:Default:index.html.twig', ['error' => $e->getMessage()]);
        }

        return$this->redirect($this->generateUrl('view_reviews_by_album', array('id' => $album->getId())),
            Response::HTTP_PERMANENTLY_REDIRECT);
    }


    /**
     * @param Request $request
     *
     * @return Response
     */
    public function getAlbumsAction(Request $request) {
        try {

            $searchTerm = $request->get('q');

            $limit = $this->getParameter('search_limit');

            $results = $this->lastFMService->searchAlbums($searchTerm, $limit, $this->apiKey);

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
            return new Response($e->getMessage());
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
            $results = $this->lastFMService->getTopTracks($limit, $this->apiKey);

            $tracks = [];
            if (array_key_exists('tracks', $results)) {

                $track = [];

                for ($i = 0; $i < count($results['tracks']['track']); $i++) {

                    if (array_key_exists('track', $results['tracks'])) {

                        if (!empty($results['tracks']['track'])) {

                            if (!empty($results['tracks']['track'][$i])) {

                                if (!empty($results['tracks']['track'][$i]['name'])) {
                                    $track['name'] = $results['tracks']['track'][$i]['name'];
                                }

                                if (!empty($results['tracks']['track'][$i]['listeners'])) {
                                    $track['listeners'] = number_format($results['tracks']['track'][$i]['listeners']);
                                }

                                if (!empty($results['tracks']['track'][$i]['playcount'])) {
                                    $track['playcount'] = number_format($results['tracks']['track'][$i]['playcount']);
                                }

                                if (array_key_exists('artist', $results['tracks']['track'][$i])) {
                                    if (!empty($results['tracks']['track'][$i]['artist']['name'])) {
                                        $track['name'] .= ' by ' . $results['tracks']['track'][$i]['artist']['name'];
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
                array_pop($chartArray);

                $bar->getData()->setArrayToDataTable($chartArray);
                $bar->getOptions()->setTitle('Trending Tracks:')->setFontSize(26);
                $bar->getOptions()->getHAxis()->setTitle('Trending Tracks based on Play Count.');
                $bar->getOptions()->getHAxis()->setMinValue(0);
                $bar->getOptions()->getVAxis()->setTitle('Tracks and associated artists');
                $bar->getOptions()->setWidth(1140);
                $bar->getOptions()->setHeight(600);

                return $this->render("@Album/Default/event.html.twig", ['bar' => $bar]);
            }
            return $this->render("@Album/Default/event.html.twig", ['error' => 'Failed to retrieve data.']);
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
    private function array_sort($array, $on, $order = SORT_ASC){

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

    /**
     * Generated indices for searching the newly added album.
     */
    public function updateEntitiesCommand() {

        $kernel = $this->get('kernel');
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => '' . self::POPULATE_SEARCH_ENTITIES . '',
            'tableName' => self::INDICES,
        ));

        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL);
        try {
            $output->writeln('<fg=green;options=bold>Generating indexes...');
            $application->run($input, $output);
        } catch (\Exception $e) {
            $output->writeln('<fg=red;options=bold>Command for updating search indices failed!');
        }
    }
}