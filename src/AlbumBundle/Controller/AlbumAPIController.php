<?php


namespace AlbumBundle\Controller;

use AlbumBundle\Entity\Album;
use AlbumBundle\Entity\Review;
use AlbumBundle\Form\AddAlbumType;
use AlbumBundle\Form\AddReviewFormType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class AlbumAPIController
 *
 * @package AlbumBundle\Controller
 *
 */
class AlbumAPIController extends FOSRestController
{
    /**
     * It retrieves all the albums.
     *
     * @Rest\Get("/albums")
     *
     * @return Response
     */
    public function getAlbumsAction()
    {
        $em = $this->getDoctrine()->getManager();

        $albums = $em->getRepository(Album::class)
            ->findAll();

        return $this->handleView($this->view($albums));
    }

    /**
     * It retrieves user details based on the given id.
     *
     * @Rest\Get("/albums/{slug}")
     *
     * @param int $slug
     *
     * @throws 404 'not found' if the user with the given id is not found.
     *
     * @return JsonResponse|Response
     */
    public function getAlbumAction($slug)
    {
        $em = $this->getDoctrine()->getManager();

        $album = $em->getRepository(Album::class)
            ->find($slug);

        // check if album exists
        if(!$album) {
            return new JsonResponse(['error' => 'Album with identifier [' . $slug .'] was not found!'],
                Response::HTTP_NOT_FOUND);
        }

        return $this->handleView($this->view($album, Response::HTTP_OK));
    }

    /**
     * It returns the reviews for a given album.
     *
     * @Rest\Get("/album/{slug}/reviews")
     *
     * @param int $slug
     *
     * @throws 404 'not found' if there is no album with the given id.
     *
     * @return JsonResponse|Response
     */
    public function getReviewsAction($slug) {

        $em = $this->getDoctrine()->getManager();

        /** @var Album $album */
        $album = $em->getRepository(Album::class)->find($slug);

        // check if the album exists.
        if(!$album) {
            return new JsonResponse(['error' => "Album not found.", Response::HTTP_NOT_FOUND]);
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
     * It returns a review for a given album.
     *
     * @Rest\Get("/albums/{slug}/reviews/{id}")
     *
     * @param int $slug album identifier
     * @param int $id review identifier
     *
     * @throws 404 'not found' if there is no album with the given identifier.
     * @throws 404 'not found' if there is no review with the given identifier.
     *
     * @return JsonResponse|Response
     */
    public function getReviewAction($slug, $id) {

        $em = $this->getDoctrine()->getManager();

        /** @var Album $album */
        $album = $em->getRepository(Album::class)->find($slug);

        // check if the album exists.
        if(!$album) {
            return new JsonResponse(['error' => 'Album with identifier ['. $slug .'] not found!'],
                Response::HTTP_NOT_FOUND);
        }

        /** @var Review $review */
        $review = $album->getReviews()->get($id);

        // check if the review exists.
        if(!$review) {
            return new JsonResponse(['error' => 'Review with identifier ['. $id .'] not found!'],
                Response::HTTP_NOT_FOUND);
        }

        return $this->handleView($this->view($review, Response::HTTP_OK));
    }










//    /**
//     * @Rest\Post("/users/{slug}/album")
//     *
//     * @param Request $request
//     * @return Response
//     */
//    public function postAlbumpostAction(Request $request)
//    {
//        $album = new Album();
//
//        // prepare the form
//        $form = $this->createForm(AddAlbumType::class, $album);
//
//        // check if the content type is json
//        if ($request->getContentType() != 'json') {
//            return $this->handleView($this->view(null, Response::HTTP_BAD_REQUEST));
//        }
//
//        // json_decode the request content and pass it to the form
//        $form->submit(json_decode($request->getContent(), true));
//
//        // check form
//        if ($form->isValid() && $form->isSubmitted()) {
//            $em = $this->getDoctrine()->getManager();
//
//            $em->persist($album);
//            $em->flush();
//
//            return $this->handleView($this->view(null, Response::HTTP_CREATED)->
//            setLocation($this->generateUrl('album_homepage')));
//        }
//        else {
//            return $this->handleView($this->view($form, Response::HTTP_BAD_REQUEST));
//        }
//
//        // create an api form type
//    }
//
//
//    /**
//     * Put("/albums/{$lug}/reviews/{$id}")
//     *
//     * @param Request $request
//     * @param $slug
//     * @param $id
//     * @return Response
//     * @throws \Exception
//     */
//    public function putAlbumReviewsAction(Request $request, $slug, $id)
//    {
//        // check user later
//
//        $em = $this->getDoctrine()->getManager();
//
//        /* @var Album $album */
//        $album = $em->getRepository(Album::class)->find($slug);
//        if(!$album) {
//            return new JsonResponse(['error' => 'Album not found'], Response::HTTP_NOT_FOUND);
//        }
//
//        /* @var Review $review */
//        $review = $em->getRepository(Review::class)->find($id);
//        if(!$review) {
//            return new JsonResponse(['error' => 'Review not found'], Response::HTTP_NOT_FOUND);
//        }
//
//        /* @var Review $updateReview */
//        $updateReview = new Review();
//
//        // prepare form
//        $form = $this->createForm(AddReviewFormType::class, $updateReview);
//
//        // check if the content type is json
//        if ($request->getContentType() != 'json') {
//            return new JsonResponse(['error' => 'Invalid format: JSON expected!'], Response::HTTP_BAD_REQUEST);
//        }
//
//        // json_decode the request content and pass it to the form
//        $form->submit(json_decode($request->getContent(), true));
//
//
//        if ($form->isValid()) {
//            $em = $this->getDoctrine()->getManager();
//
//            /* @var Review $review */
//            $review->setAlbum($album);
//            $review->setTimestamp(new \DateTime());
//            if(!empty($updateReview->getReviewer())) {
//                $review->setReviewer($updateReview->getReviewer());
//            }
//            if(!empty($updateReview->getTitle())) {
//                $review->setTitle($updateReview->getTitle());
//            }
//            if(!empty($updateReview->getReview())) {
//                $review->setReview($updateReview->getReview());
//            }
//            if(!empty($updateReview->getRating())) {
//                $review->setRating($updateReview->getRating());
//            }
//
////            $em->persist($review);
//            $em->flush();
//
//            return $this->handleView($this->view(null, Response::HTTP_CREATED)->
//            setLocation($this->generateUrl('album_homepage')));
//        }
//        else {
//            return $this->handleView($this->view($form, Response::HTTP_BAD_REQUEST));
//        }
//    }
//
//    /**
//     * Delete("/users/{slug}/album/{$album}/reviews/{$id}")
//     *
//     * @param Request $request
//     * @param $slug
//     * @param $id
//     * @return Response
//     * @throws \Exception
//     */
//    public function deleteAlbumReviewsAction(Request $request, $slug, $id) {
//
//        $em = $this->getDoctrine()->getManager();
//
//        /* @var Review $review */
//        $review = $em->getRepository(Review::class)->find($id);
//        if (!$review) {
//            $this->handleView($this->view(null, Response::HTTP_NOT_FOUND));
//        }
//
//        $em->remove($review);
//        $em->flush();
//
//        return $this->handleView($this->view(null, Response::HTTP_OK));
//    }
}