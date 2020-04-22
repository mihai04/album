<?php


namespace AlbumBundle\Controller;

use AlbumBundle\Entity\Album;
use AlbumBundle\Entity\APIError;
use AlbumBundle\Entity\Review;
use AlbumBundle\Entity\Track;
use AlbumBundle\Entity\User;
use AlbumBundle\Exceptions\APIErrorException;
use AlbumBundle\Form\AddAPIAlbumType;
use AlbumBundle\Service\LastFMService;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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
 * Class AlbumAPIController
 *
 * @package AlbumBundle\Controller
 *
 */
class AlbumAPIController extends FOSRestController
{
    /** @const string */
    const ERROR = 'error';

    /** @var string */
    const TRACK_TIME_FORMAT = '%02d:%02d';

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
     * List all albums.
     *
     * @Rest\Get("/albums")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns all albums.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\Album::class)
     *     )
     * )
     *
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     type="integer",
     *     description="The field represents the page number."
     * ),
     *
     * @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     type="integer",
     *     description="The field represents the limit of results per page."
     * ),
     *
     * @SWG\Response(
     *     response=400,
     *     description="Invalid data given."
     * )
     *
     * @SWG\Tag(name="albums")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function getAlbumsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository(Album::class)
            ->findAllQueryBuilder();

        try {
            $clientLimit = (int) $request->get('limit');
            $limit = $this->getParameter('albums_limit');
            if (!is_null($clientLimit) && $clientLimit != 0) {
                if (!($clientLimit > 0 && $clientLimit < 101)) {
                    return $this->handleView($this->view([self::ERROR => 'The limit parameter is out of bounds (1-100).'],
                        Response::HTTP_BAD_REQUEST));
                }
                $limit = $clientLimit;
            }

            $clientPage = (int) $request->get('page');
            if (!is_null($clientPage)) {
                if (!($clientPage >= 0)) {
                    return $this->handleView($this->view([self::ERROR => 'The page parameter is out of bonds (<1) .'],
                        Response::HTTP_BAD_REQUEST));
                }
            }

            $paginatedCollection = $this->get('pagination_factory')->createCollection($qb, $request,
                $limit, "api_albums_get_albums");

        } catch (OutOfRangeCurrentPageExceptionAlias $e) {
            $apiError = new APIError(Response::HTTP_BAD_REQUEST, $e->getMessage());
            throw new APIErrorException($apiError);
        }
        return $this->handleView($this->view($paginatedCollection));
    }

    /**
     * List an album specified by the user.
     *
     * @Rest\Get("/albums/{id}")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Successfully updated the specified album.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\Album::class)
     *     )
     * ),
     *
     * @SWG\Response(
     *     response=404,
     *     description="Album does not exist."
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The field represents the id of an album."
     * )
     *
     * @SWG\Tag(name="albums")
     * @Security(name="Bearer")
     *
     * @param $id
     * @return JsonResponse|Response
     */
    public function getAlbumAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $album = $em->getRepository(Album::class)
            ->find($id);

        // check if album exists
        if (!$album) {
            return new JsonResponse([self::ERROR => 'Album with identifier [' . $id . '] was not found.'],
                Response::HTTP_NOT_FOUND);
        }

        return $this->handleView($this->view($album, Response::HTTP_OK));
    }

    /**
     * Create album.
     *
     * @Rest\Post("/albums")
     *
     * @SWG\Post(
     *     operationId="addAbum",
     *     summary="Add Album.",
     *     @SWG\Parameter(
     *         name="json payload",
     *         in="body",
     *         required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="title", type="string", example="My Album"), 
     *         @SWG\Property(property="artist", type="string", example="My album artist"),
     *         @SWG\Property(property="isrc", type="string", example="UK-A00-00-00000"),
     *         @SWG\Property(property="image", type="string", example="image base 64 encoded"),
     *         @SWG\Property(property="summary", type="string", example="My album is amazing"),
     *         @SWG\Property(property="listeners", type="string", example="1000"),
     *         @SWG\Property(property="playcount", type="string", example="1000"),
     *         @SWG\Property(property="published", type="string", example="22 03 2019"),
     *         @SWG\Property(property="url", type="string", example="url to artist"),
     *         @SWG\Property(property="tags", type="string", example="disco, etno"),
     *         @SWG\Property(property="albumTracks", type="array",
     *              @SWG\Items(type="object",
     *              @SWG\Property(property="trackName", type="string", example="track name"), 
     *              @SWG\Property(property="duration", type="string", example="duration in ms"),
     *              ),
     *             )
     *          )
     *        )
     *     ),
     * ),
     *
     * @SWG\Response(
     *     response=201,
     *     description="Successfully created a review for the specified album.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\Album::class)
     *     )
     * ),
     *
     *@SWG\Response(
     *     response=400,
     *     description="Invalid data given: JSON format required."
     * )
     *
     * @SWG\Response(
     *     response=409,
     *     description="There is already an album with this ISRC."
     * )
     *
     * @SWG\Tag(name="albums")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function postAlbumsAction(Request $request)
    {
        /** @var Album $album */
        $album = new Album();

        // prepare the form
        $form = $this->createForm(AddAPIAlbumType::class, $album, ['csrf_protection' => false]);

        // check if the content type is json
        if ($request->getContentType() != 'json') {
            return $this->handleView($this->view([self::ERROR => 'Invalid data format, only JSON is accepted!'],
            Response::HTTP_BAD_REQUEST));
        }

        // json_decode the request content and pass it to the form
        $form->submit(json_decode($request->getContent(), true));

        // check form
        if ($form->isValid() && $form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();

            try {

                try {

                    $base64_string = $form['image']->getData();
                    $fileName = $this->processImage($base64_string);

                    $album->setImage($fileName);

                } catch (\Exception $e) {
                    return new JsonResponse([self::ERROR => 'Invalid based 64 encoded image. Add [data:image/jpeg;base64,]'],
                        Response::HTTP_BAD_REQUEST);
                }

                $trackResults = $form['albumTracks']->getData();
                /** @var Track $track */
                foreach ($trackResults as $track) {

                    $seconds = $track->getDuration() / 1000;
                    $minutes = round($seconds / 60);
                    $remainMinutes = ($minutes % 60);

                    $track->setDuration((sprintf(self::TRACK_TIME_FORMAT, $minutes, $remainMinutes)));


                    $album->addAlbumTracks($track);
                }

                $album->setTimestamp(new \DateTime);

                $em->persist($album);
                $em->flush();

                $destination = $this->getParameter('uploads_directory');
                $filePath = $destination . '/' . $fileName;
                $this->moveFileToPath($filePath, $base64_string);

            } catch (UniqueConstraintViolationException $e) {

                $message = 'DBALException [%i]: %s' . $e->getMessage();
                return new JsonResponse([self::ERROR => $message], Response::HTTP_CONFLICT);

            } catch (TableNotFoundException $e) {

                $message = 'ORMException [%i]: %s' . $e->getMessage();
                return new JsonResponse([self::ERROR => $message],
                    Response::HTTP_INTERNAL_SERVER_ERROR);

            } catch (\Exception $e) {

                $message = 'Exception [%i]: %s' . $e->getMessage();
                return new JsonResponse([self::ERROR => $message],
                    Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return $this->handleView($this->view($album, Response::HTTP_CREATED));
//            return $this->handleView($this->view($album, Response::HTTP_CREATED)->setLocation(
//                $this->generateUrl('view_reviews_by_album', ['id' => $album->getId()])
//            ));

        } else {
            return $this->handleView($this->view($form, Response::HTTP_BAD_REQUEST));
        }
    }

    /**
     * Modify an album for a specified id (only admins allowed).
     *
     * @Rest\Put("/albums/{id}")
     *
     * @SWG\Put(
     *     operationId="editAlbum",
     *     summary="Edit album (only admins).",
     *     @SWG\Parameter( 
     *          name="id", 
     *          in="path", 
     *          description="The field represent the album id.", 
     *          required=true, 
     *          type="string" 
     *     ),
     *     @SWG\Parameter(
     *         name="json payload",
     *         in="body",
     *         required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="title", type="string", example="My Album"), 
     *         @SWG\Property(property="artist", type="string", example="My album artist"),
     *         @SWG\Property(property="isrc", type="string", example="UK-A00-00-00000"),
     *         @SWG\Property(property="image", type="string", example="image base 64 encoded"),
     *         @SWG\Property(property="summary", type="string", example="My album is amazing"),
     *         @SWG\Property(property="listeners", type="string", example="1000"),
     *         @SWG\Property(property="playcount", type="string", example="1000"),
     *         @SWG\Property(property="url", type="string", example="external url image"),
     *         @SWG\Property(property="tags", type="string", example="disco, etno"),
     *         @SWG\Property(property="albumTracks", type="array",
     *              @SWG\Items(type="object",
     *              @SWG\Property(property="trackName", type="string", example="track name"), 
     *              @SWG\Property(property="duration", type="string", example="duration in ms"),
     *              ),
     *             )
     *          )
     *        )
     *     ),
     * ),
     *
     * @SWG\Response(
     *     response=200,
     *     description="Successfully updated the specified album.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\Album::class)
     *     )
     * ),
     *
     * @SWG\Response(
     *     response=400,
     *     description="Invalid data given: JSON format required!"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Album does not exist."
     * )
     *
     * @SWG\Response(
     *     response=403,
     *     description="Fobidden action."
     * )
     *
     * @SWG\Tag(name="albums")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse|Response
     */
    public function putAlbumsAction(Request $request, $id)
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if(!in_array('ROLE_ADMIN', $user->getRoles())) {
            return new JsonResponse([self::ERROR => 'Forbidden action you do not have admin rights.'],
                Response::HTTP_FORBIDDEN);
        }

        $em = $this->getDoctrine()->getManager();

        /* @var Album $album */
        $album = $em->getRepository(Album::class)->find($id);
        if (!$album) {
            return new JsonResponse([self::ERROR => 'Album with identifier [' . $id . '] not found.'], Response::HTTP_NOT_FOUND);
        }

        /* @var Album $updateAlbum */
        $updateAlbum = new Album();

        // prepare form
        $form = $this->createForm(AddAPIAlbumType::class, $updateAlbum, ['csrf_protection' => false]);

        // check if the content type is json
        if ($request->getContentType() != 'json') {
            return new JsonResponse([self::ERROR => 'Invalid format: JSON expected!'], Response::HTTP_BAD_REQUEST);
        }

        // json_decode the request content and pass it to the form
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) {

            try {

                $em = $this->getDoctrine()->getManager();

                if (!empty($updateAlbum->getTitle())) {
                    $album->setTitle($updateAlbum->getTitle());
                }
                if (!empty($updateAlbum->getArtist())) {
                    $album->setArtist($updateAlbum->getArtist());
                }
                if (!empty($updateAlbum->getIsrc())) {
                    $album->setIsrc($updateAlbum->getIsrc());
                }

                try {
                    $base64_string = $form['image']->getData();
                    $fileName = $this->processImage($base64_string);

                    $album->setImage($fileName);

                } catch (\Exception $e) {
                    return new JsonResponse([self::ERROR => 'Invalid based 64 encoded image. Add 
                    [data:image/jpeg;base64,]'], Response::HTTP_BAD_REQUEST);
                }

                if (!empty($updateAlbum->getSummary())) {
                    $album->setSummary($updateAlbum->getSummary());
                }

                if (!empty($updateAlbum->getPublished())) {
                    $album->setPublished($updateAlbum->getPublished());
                }

                if (!empty($updateAlbum->getListeners())) {
                    $album->setListeners($updateAlbum->getListeners());
                }

                if (!empty($updateAlbum->getPlaycount())) {
                    $album->setPlaycount($updateAlbum->getPlaycount());
                }

                $album->setTimestamp(new \DateTime);

                /** @var Track $track */
                foreach ($album->getAlbumTracks() as $track) {
                    $album->removeAlbum($track);
                }

                $tracks = $form['albumTracks']->getData();
                /** @var Track $track */
                foreach ($tracks as $track) {

                    try {
                        $seconds = $track->getDuration() / 1000;
                        $minutes = round($seconds / 60);
                        $remainMinutes = ($minutes % 60);

                        $track->setDuration((sprintf(self::TRACK_TIME_FORMAT, $minutes, $remainMinutes)));

                        $album->addAlbumTracks($track);
                    } catch (\Exception $e) {
                        // fail silently if duration is not valid
                    }
                }

                $em->persist($album);
                $em->flush();

                $destination = $this->getParameter('uploads_directory');
                $filePath = $destination . '/' . $fileName;
                $this->moveFileToPath($filePath, $base64_string);

            } catch (\Exception $e) {
                return new JsonResponse([self::ERROR => 'Failed to modify album with identifier [' . $id . '].' .
                    $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return $this->handleView($this->view($album, Response::HTTP_OK));
        } else {
            return $this->handleView($this->view($form, Response::HTTP_BAD_REQUEST));
        }
    }

    /**
     * @param string $base64_string
     * @return string
     */
    private function processImage($base64_string) {

        $pos = strpos($base64_string, ';');
        $type = explode('/', explode(':', substr($base64_string, 0, $pos))[1])[1];

        return uniqid() . '.' . $type;
    }

    /**
     * Persisting uploaded file.
     *
     * @param $filePath
     * @param $base64_string
     */
    private function moveFileToPath($filePath, $base64_string)
    {
        $file = fopen($filePath, "w+");

        $data = explode(',', $base64_string);

        fwrite($file, base64_decode($data[1]));

        chmod($filePath, 0777);

        fclose($file);
    }

    /**
     * Delete album specified by client (only admins allowed).
     *
     * @Rest\Delete("/albums/{id}")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Successfully deleted the specified album.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\Album::class)
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Album no found!"
     * )
     *
     * @SWG\Response(
     *     response=403,
     *     description="Fobidden action."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The field represents the id of an album."
     * )
     * @SWG\Tag(name="albums")
     * @Security(name="Bearer")
     *
     * @param $id
     * @return JsonResponse|Response
     */
    public function deleteAlbumsAction($id)
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $album = $em->getRepository(Album::class)
            ->find($id);

        // check if album exists
        if (!$album) {
            return new JsonResponse([self::ERROR => 'Album with identifier [' . $id . '] was not found.'],
                Response::HTTP_NOT_FOUND);
        }

        if(!in_array('ROLE_ADMIN', $user->getRoles())) {
            return new JsonResponse([self::ERROR => 'Forbidden action you do not have admin rights.'],
                Response::HTTP_FORBIDDEN);
        }

        try {
            /* @var Review $reviews */
            $reviews = $em->getRepository(Review::class)->getReviewsByAlbumID($id);

            /** @var Review $review */
            foreach ($reviews as $review) {
                $em->remove($review);
                $em->flush();
            }

            $em->remove($album);
            $em->flush();

        } catch (\Exception $e) {
            return new JsonResponse([self::ERROR => 'Failed to remove album with id [' . $id . ']',
                Response::HTTP_INTERNAL_SERVER_ERROR]);
        }

        return $this->handleView($this->view(['success' => 'Successfully removed album with id [' . $id . '].'],
            Response::HTTP_OK));
    }
}