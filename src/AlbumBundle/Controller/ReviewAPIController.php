<?php


namespace AlbumBundle\Controller;

use AlbumBundle\Entity\APIError;
use AlbumBundle\Entity\Review;
use AlbumBundle\Exceptions\APIErrorException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReviewAPIController extends FOSRestController
{
    /** @const string */
    const ERROR = 'error';

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

            $limit = (null === $clientLimit || $clientLimit > 100) ? $this->getParameter('page_limit') :
                $request->get('default_limit');

            $paginatedCollection = $this->get('pagination_factory')->createCollection($qb->getQuery(), $request,
                $limit, "api_reviews_get_reviews");

        } catch (OutOfRangeCurrentPageException $e) {
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
}