<?php


namespace AlbumBundle\Controller;


use AlbumBundle\Entity\Album;
use AlbumBundle\Entity\Review;
use AlbumBundle\Entity\Track;
use FOS\RestBundle\Controller\Annotations as Rest;
use AlbumBundle\Helper\PaginatedCollection;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TrackAPIController
 *
 * @package AlbumBundle\Controller
 */
class TrackAPIController extends FOSRestController
{
    /** @const string */
    const ERROR = 'error';

    /** @const string */
    const SUCCESS = 'success';

    /**
     * List all tracks of an album following a pagination system.
     *
     * @Rest\Get("/albums/{slug}/tracks")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns tracks.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\Track::class)
     *     )
     * )
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     type="integer",
     *     description="The field represents the page number."
     * )
     * @SWG\Parameter(
     *     name="slug",
     *     in="path",
     *     type="string",
     *     description="The field represents the id of an album."
     * )
     * @SWG\Tag(name="tracks")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param $slug
     * @return JsonResponse|Response
     */
    public function getTracksAction($slug, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Album $user */
        $album = $em->getRepository(Album::class)->find($slug);
        // check if album exists.
        if(!$album) {
            return new JsonResponse([self::ERROR => 'Album with id [' . $slug .'] was not found!'],
                Response::HTTP_NOT_FOUND);
        }

        $qb = $em->getRepository(Review::class)
            ->findAllQueryBuilder();

        $adapter = new DoctrineORMAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);

        $page = $request->query->get('page', 1);
        $pagerfanta->setMaxPerPage($this->getParameter('page_limit'));
        $pagerfanta->setCurrentPage($page);

        $reviews = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $reviews[] = $result;
        }

        $paginatedCollection = new PaginatedCollection($reviews, $pagerfanta->getNbPages());

        $route = "api_tracks_get_album_tracks";

        $routeParams = array();
        $createLinkUrl = function ($slug, $targetPage) use ($route, $routeParams) {
            return $this->generateUrl($route, array_merge($routeParams, array('slug' => $slug, 'page' => $targetPage)));
        };

        $paginatedCollection->addLink('self', $createLinkUrl($slug, $page));
        $paginatedCollection->addLink('first', $createLinkUrl($slug, 1));
        $paginatedCollection->addLink('last', $createLinkUrl($slug, $pagerfanta->getNbPages()));

        if ($pagerfanta->hasNextPage()) {
            $paginatedCollection->addLink('next', $createLinkUrl($slug, $pagerfanta->getNextPage()));
        }
        if ($pagerfanta->hasPreviousPage()) {
            $paginatedCollection->addLink('prev', $createLinkUrl($slug, $pagerfanta->getPreviousPage()));
        }

        return $this->handleView($this->view($paginatedCollection));
    }

    /**
     * List a track specified by client.
     *
     * @Rest\Get("/albums/{slug}/tracks/{id}")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the specified track.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\Track::class)
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Album does not exist!"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The field represents the id of a track."
     * )
     * @SWG\Tag(name="tracks")
     * @Security(name="Bearer")
     *
     * @param $slug
     * @param $id
     * @return JsonResponse|Response
     */
    public function getTrackAction($slug, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Album $user */
        $album = $em->getRepository(Album::class)->find($slug);
        // check if album exists.
        if(!$album) {
            return new JsonResponse([self::ERROR => 'Album with id [' . $slug .'] was not found!'],
                Response::HTTP_NOT_FOUND);
        }

        $track = $em->getRepository(Track::class)->find($id);

        // check if track exists
        if(!$track) {
            return new JsonResponse([self::ERROR => 'Track with id [' . $id .'] was not found for album with id ['.$slug.']!'],
                Response::HTTP_NOT_FOUND);
        }

        return $this->handleView($this->view($track, Response::HTTP_OK));
    }
}