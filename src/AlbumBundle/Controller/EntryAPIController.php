<?php


namespace AlbumBundle\Controller;


use AlbumBundle\Entity\Review;
use AlbumBundle\Entity\User;
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


class EntryAPIController extends FOSRestController
{
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
     * @SWG\Parameter(
     *     name="slug",
     *     in="query",
     *     type="string",
     *     description="The field represents the id of a user."
     * )
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

        $qb = $em->getRepository(Review::class)
            ->getReviewsByUser($slug);

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

        $route = "api_entries_get_user_entries";

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
     * @Rest\Get("/users/{slug}/entries/{id}")
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
     *     in="query",
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

        $review = $em->getRepository(Review::class)->find($id);

        // check if album exists
        if(!$review) {
            return new JsonResponse(['error' => 'Review with identifier [' . $id .'] was not found!'],
                Response::HTTP_NOT_FOUND);
        }

        return $this->handleView($this->view($review, Response::HTTP_OK));
    }
}