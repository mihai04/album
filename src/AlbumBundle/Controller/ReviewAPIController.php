<?php


namespace AlbumBundle\Controller;

use AlbumBundle\Entity\APIError;
use AlbumBundle\Entity\Review;
use AlbumBundle\Entity\User;
use AlbumBundle\Exceptions\APIErrorException;
use AlbumBundle\Form\AddReviewFormType;
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
 * Class ReviewAPIController
 * @package AlbumBundle\Controller
 */
class ReviewAPIController extends FOSRestController
{
    /** @const string */
    const ERROR = 'error';

    /** @const string */
    const SUCCESS = 'success';

    /**
     * List all reviews following a pagination system.
     *
     * @Rest\Get("/reviews")
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
     *     description="The field represents the limit of items per page to be returned."
     * ),
     *
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     type="string",
     *     description="The field represents the id of an album."
     * ),
     *
     * @SWG\Tag(name="reviews"),
     *
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function getReviewsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository(Review::class)->findAllQueryBuilder();

        try {

            $clientLimit = $request->get('limit');
            $limit = $this->getParameter('reviews_limit');
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

            $paginatedCollection = $this->get('pagination_factory')->createCollection($qb->getQuery(), $request,
                $limit, "api_reviews_get_reviews");

        } catch (OutOfRangeCurrentPageExceptionAlias $e) {
            $apiError = new APIError(Response::HTTP_BAD_REQUEST, $e->getMessage());
            throw new APIErrorException($apiError);
        }
        return $this->handleView($this->view($paginatedCollection));
    }

    /**
     * List a review specified by client.
     *
     * @Rest\Get("/reviews/{id}")
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
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The field represents the id of a review."
     * )
     * @SWG\Tag(name="reviews")
     * @Security(name="Bearer")
     *
     * @param $id
     * @return JsonResponse|Response
     */
    public function getReviewAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $review = $em->getRepository(Review::class)->find($id);

        // check if review exists
        if(!$review) {
            return new JsonResponse([self::ERROR => 'Review with identifier [' . $id .'] was not found!'],
                Response::HTTP_NOT_FOUND);
        }

        return $this->handleView($this->view($review, Response::HTTP_OK));
    }

    /**
     * Modify a review based on id.
     *
     * @Rest\Put("/reviews/{id}")
     *
     * @SWG\Put(
     *     operationId="editReview",
     *     summary="Edit a review based on id.",
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
     *     description="Review does not exist!"
     * ),
     *
     * @SWG\Tag(name="reviews"),
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse|Response
     */
    public function putReviewsAction(Request $request, $id)
    {
        /** @var User $currentUser */
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        /* @var Review $review */
        $review = $em->getRepository(Review::class)->find($id);
        if(!$review) {
            return new JsonResponse([self::ERROR => 'Review with ['.$id.'] was not found.'], Response::HTTP_NOT_FOUND);
        }

        if($review->getReviewer() !== $currentUser && !in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            return new JsonResponse([self::ERROR => 'Forbidden action you are not the owner of this review or you 
            do not have admin rights!'], Response::HTTP_FORBIDDEN);
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
                $review->setTimestamp(new \DateTime());
                $review->setReviewer($currentUser);
                $review->setAlbum($review->getAlbum());

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
                return new JsonResponse([self::ERROR => 'Failed to modify review with id ['. $id .'].',
                    Response::HTTP_INTERNAL_SERVER_ERROR]);
            }

            return $this->handleView($this->view($review, Response::HTTP_CREATED));
        }
        else {
            return $this->handleView($this->view($form, Response::HTTP_BAD_REQUEST));
        }
    }

    /**
     * Delete a review based on id.
     *
     * @Rest\Delete("/reviews/{id}")
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
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The field represents the id of an the review."
     * )
     * @SWG\Tag(name="reviews")
     * @Security(name="Bearer")
     *
     * @param $id
     * @return JsonResponse|Response
     */
    public function deleteReviewsAction($id) {

        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

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
            return new JsonResponse([self::ERROR => 'Failed to delete review ['. $id .'].' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->handleView($this->view([self::SUCCESS => 'Review with identifier ['. $id .'] was deleted.'],
            Response::HTTP_OK));
    }
}