<?php


namespace AlbumBundle\Controller;

use AlbumBundle\Entity\Review;
use AlbumBundle\Helper\PaginatedCollection;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
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
    /**
     * It retrieves all the albums.
     *
     * @Rest\Get("/reviews")
     *
     * @param Request $request
     * @return Response
     */
    public function getReviewsAction(Request $request)
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

        $route = "reviews_get_reviews_reviews";

        $routeParams = array();
        $createLinkUrl = function ($targetPage) use ($route, $routeParams) {
            return $this->generateUrl($route, array_merge($routeParams, array('page' => $targetPage)));
        };

        $paginatedCollection->addLink('self', $createLinkUrl($page));
        $paginatedCollection->addLink('first', $createLinkUrl(1));
        $paginatedCollection->addLink('last', $createLinkUrl($pagerfanta->getNbPages()));

        if ($pagerfanta->hasNextPage()) {
            $paginatedCollection->addLink('next', $createLinkUrl($pagerfanta->getNextPage()));
        }
        if ($pagerfanta->hasPreviousPage()) {
            $paginatedCollection->addLink('prev', $createLinkUrl($pagerfanta->getPreviousPage()));
        }

        return $this->handleView($this->view($paginatedCollection));
    }

    /**
     * It retrieves review details based on the given identifier.
     *
     * @Rest\Get("/reviews/{slug}")
     *
     * @param int $slug
     *
     * @throws 404 'not found' if the review with the given identifier was not found.
     *
     * @return JsonResponse|Response
     */
    public function getReviewAction($slug)
    {
        $em = $this->getDoctrine()->getManager();

        $review = $em->getRepository(Review::class)->find($slug);

        // check if album exists
        if(!$review) {
            return new JsonResponse(['error' => 'Review with identifier [' . $slug .'] was not found!'],
                Response::HTTP_NOT_FOUND);
        }

        return $this->handleView($this->view($review, Response::HTTP_OK));
    }



//    public function postReviewAction(Request $request, $slug) {
//        $review = new Review();
//
//        // prepare the form
//        $form = $this->createForm(AddReviewFormType::class, $review);
//
//        // check if the POST data is JSON format
//        if ($request->getContentType() !== 'json') {
//            return new JsonResponse(['error' => 'Invalid data format, only JSON is accepted!'],
//            Response::HTTP_BAD_REQUEST);
//        }
//
//        // json decode the request content and pass it to the form
//        $form->submit(json_decode($request->getContent(), true));
//
//        // validate POST data against the form requirements
//        if (!$form->isValid()) {
//            // the form is not valid and thereby return a status code of 400
//            return new JsonResponse(['error' => 'Invalid data given!'],
//                Response::HTTP_BAD_REQUEST);
//        }
//
//        // the form is valid and hence create a new Review instance and persist it to the database
//        $em = $this->getDoctrine()->getManager();
//
//        /** @var Album $album */
//        $album = $em->getRepository(Album::class)->find($slug);
//
//        // check if the album exists.
//        if(!$album) {
//            return new JsonResponse(['error' => 'Album with identifier ['. $slug .'] not found.',
//                Response::HTTP_NOT_FOUND]);
//        }
//
//        $review->setAlbum($album);
//        $review->setTitle();
//
//    }



}
