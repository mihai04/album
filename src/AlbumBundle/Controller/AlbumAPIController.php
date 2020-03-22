<?php


namespace AlbumBundle\Controller;

use AlbumBundle\Entity\Album;
use AlbumBundle\Entity\Review;
use AlbumBundle\Entity\Track;
use AlbumBundle\Form\AddAlbumType;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


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

    /** @const string */
    const SUCCESS = 'success';

    /**
     * List all albums.
     *
     * @Route("/api/v1/albums", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Returns all albums.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\Album::class)
     *     )
     * )
     * @SWG\Tag(name="albums")
     * @Security(name="Bearer")
     *
     * @return JsonResponse|Response
     */
    public function getAlbumsAction()
    {
        $em = $this->getDoctrine()->getManager();

        $albums = $em->getRepository(Album::class)
            ->findAll();

        return $this->handleView($this->view($albums));
    }

    /**
     * List an album specified by the user.
     *
     * @Route("/api/v1/albums/{albumId}/", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Returns a specified album",
     *     @SWG\Schema(
     *         type="array",
     *          @Model(type=AlbumBundle\Entity\Album::class)
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Album does not exist!"
     * )
     * @SWG\Parameter(
     *     name="slug",
     *     in="query",
     *     type="string",
     *     description="The field represents the id of an album."
     * )
     * @SWG\Tag(name="albums")
     * @Security(name="Bearer")
     *
     * @param $slug
     * @return JsonResponse|Response
     */
    public function getAlbumAction($slug)
    {
        $em = $this->getDoctrine()->getManager();

        $album = $em->getRepository(Album::class)
            ->find($slug);

        // check if album exists
        if (!$album) {
            return new JsonResponse([self::ERROR => 'Album with identifier [' . $slug . '] was not found!'],
                Response::HTTP_NOT_FOUND);
        }

        return $this->handleView($this->view($album, Response::HTTP_OK));
    }

    /**
     * Create album.
     *
     * @Rest\Post("/albums")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Successfully created the album.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\Album::class)
     *     )
     * )
     * @SWG\Response(
     *     response=409,
     *     description="There is already an album with this ISRC!"
     * )
     * @SWG\Tag(name="albums")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function postAlbumAction(Request $request)
    {
        $album = new Album();

        // prepare the form
        $form = $this->createForm(AddAlbumType::class, $album, ['csrf_protection' => false]);

        // check if the content type is json
        if ($request->getContentType() != 'json') {
            return $this->handleView($this->view(null, Response::HTTP_BAD_REQUEST));
        }

        // json_decode the request content and pass it to the form
        $form->submit(json_decode($request->getContent(), true));

        // check form
        if ($form->isValid() && $form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();

            try {

                $base64_string = $form['image']->getData();
                $pos = strpos($base64_string, ';');
                $type = explode('/', explode(':', substr($base64_string, 0, $pos))[1])[1];
                $fileName = uniqid() . '.' . $type;

                $album->setImage($fileName);
                $tracks = $form['albumTracks']->getData();
                /**
                 * @var Track $track
                 */
                foreach ($tracks as $track) {
                    $track->setAlbum($album);
                }

                $em->persist($album);
                $em->flush();

                $destination = $this->getParameter('uploads_directory');
                $filePath = $destination . '/' . $fileName;
                $this->moveFileToPath($filePath, $base64_string);
            } catch (UniqueConstraintViolationException $e) {

                $message = 'DBALException [%i]: %s' . $e->getMessage();
                return new JsonResponse([self::ERROR => $message, Response::HTTP_CONFLICT]);

            } catch (TableNotFoundException $e) {

                $message = 'ORMException [%i]: %s' . $e->getMessage();
                return new JsonResponse([self::ERROR => $message,
                    Response::HTTP_INTERNAL_SERVER_ERROR]);

            } catch (\Exception $e) {

                $message = 'Exception [%i]: %s' . $e->getMessage();
                return new JsonResponse([self::ERROR => $message,
                    Response::HTTP_INTERNAL_SERVER_ERROR]);

            }

            return $this->handleView($this->view($album, Response::HTTP_CREATED));
        } else {
            return $this->handleView($this->view($form, Response::HTTP_BAD_REQUEST));
        }
    }

    /**
     * Modify an alum for a specified id.
     *
     * @Rest\Put("/albums/{slug}")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Successfully created a review for the specified album.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\Review::class)
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Invalid data given: JSON format required!"
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
     * @SWG\Tag(name="albums")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param $slug
     * @param $id
     * @return JsonResponse|Response
     */
    public function putAlbumsAction(Request $request, $slug, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /* @var Album $album */
        $album = $em->getRepository(Album::class)->find($slug);
        if (!$album) {
            return new JsonResponse([self::ERROR => 'Album not found'], Response::HTTP_NOT_FOUND);
        }

        /* @var Album $updateAlbum */
        $updateReview = new Album();

        // prepare form
        $form = $this->createForm(AddAlbumType::class, $updateAlbum, ['csrf_protection' => false]);

        // check if the content type is json
        if ($request->getContentType() != 'json') {
            return new JsonResponse([self::ERROR => 'Invalid format: JSON expected!'], Response::HTTP_BAD_REQUEST);
        }

        // json_decode the request content and pass it to the form
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) {

            try {

                $em = $this->getDoctrine()->getManager();

                if (!empty($updateReview->getTitle())) {
                    $album->setTitle($updateReview->getTitle());
                }
                if (!empty($updateReview->getArtist())) {
                    $album->setTitle($updateReview->getArtist());
                }
                if (!empty($updateReview->getIsrc())) {
                    $album->setIsrc($updateReview->getIsrc());
                }

                $base64_string = $form['image']->getData();
                $pos = strpos($base64_string, ';');
                $type = explode('/', explode(':', substr($base64_string, 0, $pos))[1])[1];
                $fileName = uniqid() . '.' . $type;

                $album->setImage($fileName);

                if (!empty($updateReview->getSummary())) {
                    $album->setSummary($updateReview->getSummary());
                }
                if (!empty($updateReview->isPublished())) {
                    $album->setIsPublished($updateReview->isPublished());
                }
                $album->setTimestamp(new \DateTime);

                // there is no need to set reviews given the relationship.
                // have a look at the setter for setting reviews using an Album instance.
                if (!empty($updateReview->getReviews())) {
                    $album->setReviews($updateReview->getReviews());
                }

                $tracks = $form['albumTracks']->getData();
                /**
                 * @var Track $track
                 */
                foreach ($tracks as $track) {
                    $track->setAlbum($album);
                }

                // there is no need to set album tracks given the relationship.
                // have a look at the setter for setting reviews using an Album instance.
                if (!empty($updateReview->getAlbumTracks())) {
                    $album->setAlbumTracks($updateReview->getAlbumTracks());
                }

                $em->persist($album);
                $em->flush();

                $destination = $this->getParameter('uploads_directory');
                $filePath = $destination . '/' . $fileName;
                $this->moveFileToPath($filePath, $base64_string);

            } catch (\Exception $e) {
                return new JsonResponse([self::ERROR => 'Failed to modify review for album with identifier [' . $slug . '].',
                    Response::HTTP_INTERNAL_SERVER_ERROR]);
            }

            return $this->handleView($this->view([self::SUCCESS => 'Review with identifier [' . $slug . '] was modified.'],
                Response::HTTP_CREATED)->setLocation($this->generateUrl('album_homepage')));
        } else {
            return $this->handleView($this->view($form, Response::HTTP_BAD_REQUEST));
        }
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
     * Delete album specified by client.
     *
     * @Rest\Delete("/albums/{slug}")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Successfully deleted the specified album.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\Review::class)
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Album no found!"
     * )
     * @SWG\Parameter(
     *     name="slug",
     *     in="path",
     *     type="string",
     *     description="The field represents the id of an album."
     * )
     * @SWG\Tag(name="albums")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param $slug
     * @return JsonResponse|Response
     */
    public function deleteAlbumAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();

        $album = $em->getRepository(Album::class)
            ->find($slug);

        // check if album exists
        if (!$album) {
            return new JsonResponse([self::ERROR => 'Album with identifier [' . $slug . '] was not found!'],
                Response::HTTP_NOT_FOUND);
        }

        try {
            /* @var Review $reviews */
            $reviews = $em->getRepository(Review::class)->getReviewsByAlbumID($slug);

            /** @var Review $review */
            foreach ($reviews as $review) {
                $em->remove($review);
                $em->flush();
            }

            $em->remove($album);
            $em->flush();

        } catch (\Exception $e) {
            return new JsonResponse([self::ERROR => 'Failed to delete review [' . $slug . '] for album with identifier [' . $slug . '].',
                Response::HTTP_INTERNAL_SERVER_ERROR]);
        }

        return $this->handleView($this->view([self::SUCCESS => 'Review with identifier [' . $slug . '] was deleted.'],
            Response::HTTP_OK));
    }
}