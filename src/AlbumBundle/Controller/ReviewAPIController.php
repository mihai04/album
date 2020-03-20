<?php


namespace AlbumBundle\Controller;

use AlbumBundle\Entity\Review;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @return Response
     */
    public function getReviewsAction()
    {
        $em = $this->getDoctrine()->getManager();

        $reviews = $em->getRepository(Review::class)
            ->findAll();

        return $this->handleView($this->view($reviews));
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
