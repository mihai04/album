<?php


namespace AlbumBundle\Controller;

use AlbumBundle\Entity\Album;
use AlbumBundle\Entity\Review;
use AlbumBundle\Entity\User;
use AlbumBundle\Form\AddReviewFormType;
use AlbumBundle\Helper\PaginatedCollection;
use FOS\RestBundle\Controller\Annotations as Rest;
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
 * Class ReviewAPIController
 *
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
     * @Rest\Get("/albums/{slug}/reviews")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the specified review.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\Review::class)
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
     *     description="The field represents the id of a user."
     * )
     * @SWG\Tag(name="reviews per album")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param $slug
     * @return JsonResponse|Response
     */
    public function getReviewsAction($slug, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
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

        $route = "api_reviews_get_album_reviews";

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
     * List a review specified by client.
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
     *     description="Review does not exist!"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The field represents the id of a review."
     * )
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

        $review = $em->getRepository(Review::class)->find($id);

        // check if album exists
        if(!$review) {
            return new JsonResponse([self::ERROR => 'Review with identifier [' . $id .'] was not found!'],
                Response::HTTP_NOT_FOUND);
        }

        return $this->handleView($this->view($review, Response::HTTP_OK));
    }

    /**
     * Create a review for a specified album id.
     *
     * @Rest\Post("/albums/{slug}/reviews")
     *
     * @SWG\Response(
     *     response=201,
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
     * @SWG\Tag(name="reviews per album")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param $slug
     * @return JsonResponse|Response
     */
    public function postReviewAction(Request $request, $slug) {

        // get user for oauth

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
            return new JsonResponse([self::ERROR => 'Invalid data given!'],
                Response::HTTP_BAD_REQUEST);
        }

        // the form is valid and hence create a new Review instance and persist it to the database
        $em = $this->getDoctrine()->getManager();

        /** @var Album $album */
        $album = $em->getRepository(Album::class)->find($slug);

        // check if the album exists.
        if(!$album) {
            return new JsonResponse([self::ERROR => 'Album with identifier ['. $slug .'] not found.',
                Response::HTTP_NOT_FOUND]);
        }

        try {

            /** @var User $user */
            $user = $em->getRepository(User::class)->find(11);

            // check if user exists.
            if(!$user) {
                return new JsonResponse([self::ERROR => 'User with id [' . 11 .'] was not found!'],
                    Response::HTTP_NOT_FOUND);
            }

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

        return $this->handleView($this->view(null, Response::HTTP_CREATED)->setLocation(
            $this->generateUrl('api_reviews_get_album_review', ['slug' => $slug, 'id' => $review->getId()])));
    }


    /**
     * Modify a review for a specified album id.
     *
     * @Rest\Put("/albums/{slug}/reviews{id}")
     *
     * @SWG\Response(
     *     response=201,
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
     * @SWG\Tag(name="reviews per album")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param $slug
     * @param $id
     * @return JsonResponse|Response
     */
    public function putReviewsAction(Request $request, $slug, $id)
    {
        // check user later

        $em = $this->getDoctrine()->getManager();

        /* @var Album $album */
        $album = $em->getRepository(Album::class)->find($slug);
        if(!$album) {
            return new JsonResponse([self::ERROR => 'Album not found'], Response::HTTP_NOT_FOUND);
        }

        /* @var Review $review */
        $review = $em->getRepository(Review::class)->find($id);
        if(!$review) {
            return new JsonResponse([self::ERROR => 'Review not found'], Response::HTTP_NOT_FOUND);
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

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            try {
                /* @var Review $review */
                $review->setAlbum($album);
                $review->setTimestamp(new \DateTime());
                if(!empty($updateReview->getReviewer())) {
                    $review->setReviewer($updateReview->getReviewer());
                }
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

            return $this->handleView($this->view([self::SUCCESS => 'Review with identifier ['. $slug .'] was modified.'],
            Response::HTTP_CREATED)->setLocation($this->generateUrl('album_homepage')));
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
     * @param Request $request
     * @param $slug
     * @param $id
     * @return JsonResponse|Response
     */
    public function deleteReviewsAction(Request $request, $slug, $id) {

        $em = $this->getDoctrine()->getManager();

        /* @var Review $review */
        $review = $em->getRepository(Review::class)->find($id);
        if (!$review) {
            return $this->handleView($this->view([self::ERROR => 'Review with identifier ['. $id .'] was not found.'],
                Response::HTTP_NOT_FOUND));
        }

        try {

            $em->remove($review);
            $em->flush();

        } catch (\Exception $e) {
            return new JsonResponse([self::ERROR => 'Failed to delete review ['.$id.'] for album with identifier ['. $slug .'].',
                Response::HTTP_INTERNAL_SERVER_ERROR]);
        }

        return $this->handleView($this->view([self::SUCCESS => 'Review with identifier ['. $slug .'] was deleted.'],
            Response::HTTP_OK));
    }
}
