<?php


namespace AlbumBundle\Controller;


use AlbumBundle\Entity\Album;
use AlbumBundle\Entity\APIError;
use AlbumBundle\Entity\Review;
use AlbumBundle\Entity\Track;
use AlbumBundle\Exceptions\APIErrorException;
use Doctrine\ORM\NonUniqueResultException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Pagerfanta\Exception\OutOfRangeCurrentPageException as OutOfRangeCurrentPageExceptionAlias;
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

    /**
     * List all tracks of an album following a pagination system.
     *
     * @Rest\Get("/albums/{slug}/tracks")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns all track records.",
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
     *
     * @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     type="integer",
     *     description="The field represents the limit of results per page."
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Invalid data given."
     * )
     *
     * @SWG\Parameter(
     *     name="slug",
     *     in="path",
     *     type="string",
     *     description="The field represents the id of an album."
     * )
     * @SWG\Tag(name="tracks")
     * @Security(name="OAuth2")
     *
     * @param Request $request
     * @param $slug
     * @return JsonResponse|Response
     */
    public function getTracksAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Album $user */
        $album = $em->getRepository(Album::class)->find($slug);
        // check if album exists.
        if(!$album) {
            return new JsonResponse([self::ERROR => 'Album with id [' . $slug .'] was not found.'],
                Response::HTTP_NOT_FOUND);
        }

        try {
            $clientLimit = (int) $request->get('limit');
            $limit = $this->getParameter('tracks_limit');
            if (!is_null($clientLimit) && $clientLimit != 0) {
                if (!($clientLimit > 0 && $clientLimit < 101)) {
                    return $this->handleView($this->view([self::ERROR => 'The limit parameter is out of bounds (1-100).'],
                        Response::HTTP_BAD_REQUEST));
                }
                $limit = $clientLimit;
            }

            $clientPage = (int) $request->get('page');
            if (!is_null($clientPage) && $clientPage != 0) {
                if (!($clientPage >= 0)) {
                    return $this->handleView($this->view([self::ERROR => 'The page parameter is out of bonds (<1).'],
                        Response::HTTP_BAD_REQUEST));
                }
            }

            $qb = $em->getRepository(Track::class)
                ->getTracksByAlbumID($slug);

            $paginatedCollection = $this->get('pagination_factory')->createCollectionBySlug($qb, $request,
                $limit, "api_tracks_get_album_tracks", $slug);

        } catch (OutOfRangeCurrentPageExceptionAlias $e) {
            $apiError = new APIError(Response::HTTP_BAD_REQUEST, $e->getMessage());
            throw new APIErrorException($apiError);
        }

        return $this->handleView($this->view($paginatedCollection));
    }

    /**
     * List a track for a specified album id.
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
     *     description="Resource does not exist."
     * )
     *
     * @SWG\Parameter(
     *     name="slug",
     *     in="path",
     *     type="string",
     *     description="The field represents the id of an album."
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

        /** @var Album $album */
        $album = $em->getRepository(Album::class)->find($slug);
        // check if album exists.
        if(!$album) {
            return new JsonResponse([self::ERROR => 'Album with id [' . $slug . '] was not found.'],
                Response::HTTP_NOT_FOUND);
        }

        try {
            /** @var Track $track */
            $track = $em->getRepository(Track::class)->getTrack($slug, $id);

            if(!$track) {
                return new JsonResponse([self::ERROR => 'Track with id [' . $id .'] was not found for album with id [' . $slug . '].'],
                    Response::HTTP_NOT_FOUND);
            }

            return $this->handleView($this->view($track, Response::HTTP_OK));

        } catch (NonUniqueResultException $e) {
            return new JsonResponse([self::ERROR => 'Failed to find track with id [' . $id .'] was not found for album with
            id [' . $slug . '].'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}