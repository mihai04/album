<?php


namespace AlbumBundle\Controller;

use AlbumBundle\Entity\Review;
use AlbumBundle\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserAPIController
 *
 * @package AlbumBundle\Controller
 */
class UserAPIController extends FOSRestController
{
    /**
     * It retrieves all user details.
     *
     * @Rest\Get("/users")
     *
     * @return JsonResponse|Response
     */
    public function getUsersAction() {

        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();

        return $this->handleView($this->view($users, Response::HTTP_OK));
    }

    /**
     * It retrieves user details based on the given id.
     *
     * @Rest\Get("/users/{slug}")
     *
     * @param int $slug
     *
     * @throws 404 'not found' if the user with the given id is not found.
     *
     * @return JsonResponse|Response
     */
    public function getUserAction($slug) {

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($slug);

        // check if user exists.
        if(!$user) {
            return new JsonResponse(['error' => 'User with id [' . $slug .'] was not found!'],
                Response::HTTP_NOT_FOUND);
        }

        return $this->handleView($this->view($user, Response::HTTP_OK));
    }

    /**
     * It returns the reviews for a given user.
     *
     * @Rest\Get("/users/{slug}/reviews")
     *
     * @param int $slug
     *
     * @throws 404 'not found' if there is no user with the given identifier.
     *
     * @return JsonResponse|Response
     */
    public function getReviewsAction($slug) {

        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $em->getRepository(User::class)->find($slug);

        // check if the album exists.
        if(!$user) {
            return new JsonResponse(['error' => 'User with identifier ['. $slug .'] not found.'],
                Response::HTTP_NOT_FOUND);
        }

        $reviews = $em->getRepository(Review::class)->getReviewsByAlbumID($slug)
            ->getResult();

        /** @var Review $review */
        foreach ($reviews as $key => $review) {
            $reviews[$key] = $review;
        }

        return $this->handleView($this->view($reviews, Response::HTTP_OK));
    }

    /**
     * It returns a review for a given user.
     *
     * @Rest\Get("/users/{slug}/reviews/{id}")
     *
     * @param int $slug user identifier
     * @param int $id review identifier
     *
     * @throws 404 'not found' if there is no user with the given identifier.
     * @throws 404 'not found' if there is no review with the given identifier.
     *
     * @return JsonResponse|Response
     */
    public function getReviewAction($slug, $id) {

        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $em->getRepository(User::class)->find($slug);

        // check if the album exists.
        if(!$user) {
            return new JsonResponse(['error' => 'User with identifier ['. $slug .'] not found.'],
                Response::HTTP_NOT_FOUND);
        }

        /** @var Review $review */
        $review = $user->getReviews()->get($id);

        // check if the review exist exists.
        if(!$review) {
            return new JsonResponse(['error' => 'Review with identifier ['. $id .'] not found!'],
                Response::HTTP_NOT_FOUND);
        }

        return $this->handleView($this->view($review, Response::HTTP_OK));
    }
}