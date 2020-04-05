<?php


namespace AlbumBundle\Controller;


use AlbumBundle\Entity\APIError;
use AlbumBundle\Entity\Review;
use AlbumBundle\Entity\User;
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


class EntryAPIController extends FOSRestController
{
    /** @const string */
    const ERROR = 'error';

    /**
     * List all reviews following a pagination system.
     *
     * @Rest\Get("/users/{slug}/entries")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the specified review.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\Review::class)
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
     * @SWG\Parameter(
     *     name="slug",
     *     in="path",
     *     type="string",
     *     description="The field represents the id of an user."
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Invalid data given."
     * )
     *
     * @SWG\Tag(name="entries per user")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param $slug
     * @return JsonResponse|Response
     */
    public function getEntriesAction($slug, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $em->getRepository(User::class)->find($slug);
        // check if user exists.
        if(!$user) {
            return new JsonResponse(['error' => 'User with id [' . $slug .'] was not found!'],
                Response::HTTP_NOT_FOUND);
        }

        try {
        $clientLimit = (int) $request->get('limit');
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

        $qb = $em->getRepository(Review::class)->getReviewsByUser($slug);

        $paginatedCollection = $this->get('pagination_factory')
            ->createCollectionBySlug($qb, $request, $limit, "api_entries_get_user_entries", $slug);

        } catch (OutOfRangeCurrentPageExceptionAlias $e) {
            $apiError = new APIError(Response::HTTP_BAD_REQUEST, $e->getMessage());
            throw new APIErrorException($apiError);
        }

        return $this->handleView($this->view($paginatedCollection));
    }

    /**
     * List a review specified by client.
     *
     * @Rest\Get("/users/{slug}/entries/{id}")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the specified review.",
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Review does not exist!"
     * )
     * @SWG\Parameter(
     *     name="slug",
     *     in="path",
     *     type="string",
     *     description="The field represents the id of a user."
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The field represents the id of a review."
     * )
     * @SWG\Tag(name="entries per user")
     * @Security(name="Bearer")
     *
     * @param $slug
     * @param $id
     * @return JsonResponse|Response
     */
    public function getEntryAction($slug, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->find($slug);

        // check if user exists.
        if(!$user) {
            return new JsonResponse(['error' => 'User with id [' . $slug .'] was not found!'],
                Response::HTTP_NOT_FOUND);
        }

        try {
            /** @var Review $track */
            $review = $em->getRepository(Review::class)->getReview($slug, $id);

            // check if review exists
            if(!$review) {
                return new JsonResponse([self::ERROR => 'Failed to find review with id [' . $id .'] was not found for user with id [' . $slug . '].'],
                    Response::HTTP_NOT_FOUND);
            }

            return $this->handleView($this->view($review, Response::HTTP_OK));

        } catch (NonUniqueResultException $e) {
            return new JsonResponse([self::ERROR => 'Failed to find review with id [' . $id .'] was not found for user with
            id [' . $slug . '].'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}