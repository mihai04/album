<?php


namespace AlbumBundle\Controller;


use AlbumBundle\Entity\Album;
use AlbumBundle\Entity\Review;
use AlbumBundle\Entity\Track;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
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

        $qb = $em->getRepository(Track::class)
            ->getTracks();

        $paginatedCollection = $this->get('pagination_factory')->createCollectionBySlug($qb, $request,
            $this->getParameter('page_limit'), "api_tracks_get_album_tracks", $slug);


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