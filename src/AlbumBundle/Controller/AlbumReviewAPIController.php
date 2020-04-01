<?php


namespace AlbumBundle\Controller;

use AlbumBundle\Entity\Album;
use AlbumBundle\Entity\APIError;
use AlbumBundle\Entity\Review;
use AlbumBundle\Entity\User;
use AlbumBundle\Exceptions\APIErrorException;
use AlbumBundle\Form\AddReviewFormType;
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
 * Class AlbumReviewAPIController
 *
 * @package AlbumBundle\Controller
 */
class AlbumReviewAPIController extends FOSRestController
{
    /** @const string */
    const ERROR = 'error';

    /** @const string */
    const SUCCESS = 'success';

    /**
     * List all reviews for a specific album following a pagination system.
     *
     * @Rest\Get("/albums/{slug}/reviews")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the specified review.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\Review::class)
     *     )
     * ),
     *
     * @SWG\Response(
     *     response=404,
     *     description="Review does not exist."
     * ),
     *
     *  @SWG\Response(
     *     response=400,
     *     description="Invalid data given."
     * ),
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
     * @SWG\Parameter(
     *     name="slug",
     *     in="path",
     *     type="string",
     *     description="The field represents the id of an album."
     * ),
     *
     * @SWG\Tag(name="reviews per album"),
     *
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param $slug
     * @return JsonResponse|Response
     */
    public function getReviewsAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();

        $album = $em->getRepository(Album::class)->find($slug);

        if (!$album) {
            return new JsonResponse(['error' => 'Album with id [' . $slug .'] was not found!'],
                Response::HTTP_NOT_FOUND);
        }

        try {


            $clientLimit = (int)$request->get('limit');
            $limit = $this->getParameter('reviews_limit');
            if (!is_null($clientLimit) && $clientLimit != 0) {
                if (!($clientLimit > 0 && $clientLimit < 101)) {
                    return $this->handleView($this->view([self::ERROR => 'The limit parameter is out of bounds (1-100).'],
                        Response::HTTP_BAD_REQUEST));
                }
                $limit = $clientLimit;
            }

            $clientPage = (int)$request->get('page');
            if (!is_null($clientPage) && $clientPage != 0) {
                if (!($clientPage >= 0)) {
                    return $this->handleView($this->view([self::ERROR => 'The page parameter is out of bonds (<1) .'],
                        Response::HTTP_BAD_REQUEST));
                }
            }

            $qb = $em->getRepository(Review::class)
                ->getReviewsByAlbumID($slug);

            $paginatedCollection = $this->get('pagination_factory')->createCollectionBySlug($qb, $request,
                $limit, "api_album_reviews_get_album_reviews", $slug);

        }   catch (OutOfRangeCurrentPageExceptionAlias $e) {
            $apiError = new APIError(Response::HTTP_BAD_REQUEST, $e->getMessage());
            throw new APIErrorException($apiError);
        }
        return $this->handleView($this->view($paginatedCollection));
    }

    /**
     * List a review for an album specified by client.
     *
     * @Rest\Get("/albums/{slug}/reviews/{id}")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the specified review.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\Review::class)
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Resource does not exist!"
     * )
     *
     * @SWG\Parameter(
     *     name="slug",
     *     in="path",
     *     type="string",
     *     description="The field represents the id of an album."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The field represents the id of a review."
     * )
     *
     * @SWG\Tag(name="reviews per album")
     * @Security(name="Bearer")
     *
     * @param $slug
     * @param $id
     * @return JsonResponse|Response
     */
    public function getReviewAction($slug, $id)
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
            $review = $em->getRepository(Review::class)->getReviewByAlbum($slug, $id);
            // check if album exists
            if(!$review) {
                return new JsonResponse([self::ERROR => 'Review with identifier [' . $id .'] was not found!'],
                    Response::HTTP_NOT_FOUND);
            }

            return $this->handleView($this->view($review, Response::HTTP_OK));

        } catch (NonUniqueResultException $e) {
            return new JsonResponse([self::ERROR => 'Failed to find review with id [' . $id .'] was not found for album with
            id [' . $slug . '].'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a review for a specified album id.
     *
     * @Rest\Post("/albums/{slug}/reviews")
     *
     * @SWG\Post(
     *     operationId="createReview",
     *     summary="Create new review entry",
     *     @SWG\Parameter( 
     *          name="slug", 
     *          in="path", 
     *          description="The field represent the album id.", 
     *          required=true, 
     *          type="string" 
     *     ),
     *     @SWG\Parameter(
     *         name="json payload",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="title", type="string", example="My Review Title"), 
     *              @SWG\Property(property="review", type="string", example="My Album Review"),
     *              @SWG\Property(property="rating", type="integer", example=4),
     *           )
     *        )
     *     ),
     * ),

     * @SWG\Response(
     *     response=201,
     *     description="Successfully created a review for the specified album.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\Review::class)
     *     ),
     * ),
     *
     * @SWG\Response(
     *     response=400,
     *     description="Invalid data given: JSON format required!"
     * ),
     *
     * @SWG\Response(
     *     response=404,
     *     description="Album does not exist!"
     * ),
     *
     * @SWG\Tag(name="reviews per album"),
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param $slug
     * @return JsonResponse|Response
     */
    public function postReviewAction(Request $request, $slug) {

        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // the form is valid and hence create a new Review instance and persist it to the database
        $em = $this->getDoctrine()->getManager();

        /** @var Album $album */
        $album = $em->getRepository(Album::class)->find($slug);

        // check if the album exists.
        if(!$album) {
            return new JsonResponse([self::ERROR => 'Album with identifier ['. $slug .'] was not found.'],
                Response::HTTP_NOT_FOUND);
        }

        /** @var Review $review */
        $review = new Review();

        // prepare the form and disable csrf_protection
        $form = $this->createForm(AddReviewFormType::class, $review, ['csrf_protection' => false]);

        // check if the POST data is JSON format
        if ($request->getContentType() !== 'json') {
            return new JsonResponse([self::ERROR => 'Invalid data format, only JSON is accepted!'],
            Response::HTTP_BAD_REQUEST);
        }

        // json decode the request content and pass it to the form
        $form->submit(json_decode($request->getContent(), true));

        // validate POST data against the form requirements
        if (!$form->isValid()) {
            // the form is not valid and thereby return a status code of 400
            return new JsonResponse([self::ERROR => 'Invalid data given! Check the API documentation for parameters 
            constraints.'], Response::HTTP_BAD_REQUEST);
        }

        try {

            $review->setReviewer($user);
            $review->setAlbum($album);
            $review->setTitle($form['title']->getData());
            $review->setTimestamp(new \DateTime());

            $em->persist($review);
            $em->flush();

        } catch (\Exception $e) {

            return new JsonResponse([self::ERROR => 'Failed to store review for album with identifier ['. $slug .'].',
                Response::HTTP_INTERNAL_SERVER_ERROR]);
        }

        return $this->handleView($this->view($review, Response::HTTP_CREATED));
    }

    /**
     * Modify a review for a specified album id.
     *
     * @Rest\Put("/albums/{slug}/reviews/{id}")
     *
     * @SWG\Put(
     *     operationId="editReview",
     *     summary="Edit review.",
     *     @SWG\Parameter( 
     *          name="slug", 
     *          in="path", 
     *          description="The field represent the album id.", 
     *          required=true, 
     *          type="string" 
     *     ),
     *     @SWG\Parameter( 
     *          name="id", 
     *          in="path", 
     *          description="The field represent the review id.", 
     *          required=true, 
     *          type="string" 
     *     ),
     *     @SWG\Parameter(
     *         name="json payload",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="title", type="string", example="My Review"), 
     *              @SWG\Property(property="review", type="string", example="I like Eminem Review"),
     *              @SWG\Property(property="rating", type="integer", example=3),
     *           )
     *        )
     *     ),
     * ),
     *
     * @SWG\Response(
     *     response=200,
     *     description="Successfully created a review for the specified album.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\Review::class)
     *     )
     * ),
     *
     * @SWG\Response(
     *     response=400,
     *     description="Invalid data given: JSON format required!"
     * ),
     *
     * @SWG\Response(
     *     response=403,
     *     description="You are not the owner of this review!"
     * ),
     *
     * @SWG\Response(
     *     response=404,
     *     description="Resource does not exist!"
     * ),
     *
     * @SWG\Tag(name="reviews per album"),
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param $slug
     * @param $id
     * @return JsonResponse|Response
     */
    public function putReviewsAction(Request $request, $slug, $id)
    {
        /** @var User $currentUser */
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        /* @var Album $album */
        $album = $em->getRepository(Album::class)->find($slug);
        if(!$album) {
            return new JsonResponse([self::ERROR => 'Album with identifier [' . $slug .'] was not found!'], Response::HTTP_NOT_FOUND);
        }

        /* @var Review $review */
        $review = $em->getRepository(Review::class)->find($id);
        if(!$review) {
            return new JsonResponse([self::ERROR => 'Review with identifier [' . $id .'] was not found!'], Response::HTTP_NOT_FOUND);
        }

        if($review->getReviewer() !== $currentUser && !in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            return new JsonResponse([self::ERROR => 'Forbidden action you are not the owner of this review or you 
            do not have admin rights!'],
                Response::HTTP_FORBIDDEN);
        }

        /* @var Review $updateReview */
        $updateReview = new Review();

        // prepare form
        $form = $this->createForm(AddReviewFormType::class, $updateReview, ['csrf_protection' => false]);

        // check if the content type is json
        if ($request->getContentType() != 'json') {
            return new JsonResponse([self::ERROR => 'Invalid format: JSON expected!'], Response::HTTP_BAD_REQUEST);
        }

        // json_decode the request content and pass it to the form
        $form->submit(json_decode($request->getContent(), true));

        // validate PUT data against the form requirements
        if (!$form->isValid()) {
            // the form is not valid and thereby return a status code of 400
            return new JsonResponse([self::ERROR => 'Invalid data given! Check the API documentation for parameters 
            constraints.'], Response::HTTP_BAD_REQUEST);
        }

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            try {
                /* @var Review $review */
                $review->setAlbum($album);
                $review->setTimestamp(new \DateTime());
                $review->setReviewer($currentUser);

                if(!empty($updateReview->getTitle())) {
                    $review->setTitle($updateReview->getTitle());
                }
                if(!empty($updateReview->getReview())) {
                    $review->setReview($updateReview->getReview());
                }
                if(!empty($updateReview->getRating())) {
                    $review->setRating($updateReview->getRating());
                }

                $em->persist($review);
                $em->flush();

            } catch (\Exception $e) {
                return new JsonResponse([self::ERROR => 'Failed to modify review for album with identifier ['. $slug .'].',
                    Response::HTTP_INTERNAL_SERVER_ERROR]);
            }

            return $this->handleView($this->view($review, Response::HTTP_CREATED));
        }
        else {
            return $this->handleView($this->view($form, Response::HTTP_BAD_REQUEST));
        }
    }

    /**
     * Delete a review for a specified album id.
     *
     * @Rest\Delete("/albums/{slug}/reviews/{id}")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Successfully deleted the specified review.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\Review::class)
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Review no found for the specified album!"
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
     *     description="The field represents the id of an the review."
     * )
     * @SWG\Tag(name="reviews per album")
     * @Security(name="Bearer")
     *
     * @param $slug
     * @param $id
     * @return JsonResponse|Response
     */
    public function deleteReviewsAction($slug, $id) {

        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        /* @var Album $album */
        $album = $em->getRepository(Album::class)->find($slug);
        if(!$album) {
            return new JsonResponse([self::ERROR => 'Album with identifier [' . $slug .'] was not found!'],
                Response::HTTP_NOT_FOUND);
        }

        /* @var Review $review */
        $review = $em->getRepository(Review::class)->find($id);
        if (!$review) {
            return $this->handleView($this->view([self::ERROR => 'Review with identifier ['. $id .'] was not found.'],
                Response::HTTP_NOT_FOUND));
        }

        if($review->getReviewer() !== $user && !in_array($user, $user->getRoles())) {
            return new JsonResponse([self::ERROR => 'Forbidden action you are not the owner of this review!'],
                Response::HTTP_FORBIDDEN);
        }

        try {

            $em->remove($review);
            $em->flush();

        } catch (\Exception $e) {
            return new JsonResponse([self::ERROR => 'Failed to delete review ['.$id.'] for album with identifier ['. $slug .'].' .
                 $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR]);
        }

        return $this->handleView($this->view([self::SUCCESS => 'Review with identifier ['. $id .'] was deleted.'],
            Response::HTTP_OK));
    }
}
