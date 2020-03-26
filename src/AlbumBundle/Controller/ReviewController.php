<?php

namespace AlbumBundle\Controller;

use AlbumBundle\Entity\Album;
use AlbumBundle\Service\LastFMService;
use Exception;
use Knp\Component\Pager\Paginator;
use AlbumBundle\Entity\Review;
use AlbumBundle\Form\AddReviewFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse as RedirectResponseAlias;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AlbumBundle\Entity\Track;
use AlbumBundle\Entity\User;

/**
 * Class ReviewController
 * @package ReviewBundle\Controller
 */
class ReviewController extends Controller
{
    /** @var LastFMService $lastFMService */
    private $lastFMService;

    /**
     * LastFMController constructor.
     *
     * @param LastFMService $lastFMService
     */
    public function __construct(LastFMService $lastFMService)
    {
        $this->lastFMService = $lastFMService;
    }

    /**
     * Add a review to a given album.
     *
     * @param Request $request
     * @param int
     * @return Response
     * @throws Exception
     */
    public function addReviewAction(Request $request, $id)
    {
        $review = new Review();
        $form = $this->createForm(AddReviewFormType::class, $review);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $album = $em->getRepository(Album::class)->find($id);

            if (!$album) {
                $this->addFlash("error", "Failed to find Album!");
            } else {
                try {
                    /** @var User $user */
                    $user = $this->get('security.token_storage')->getToken()->getUser();
                    $review->setReviewer($user);
                    /** @var Album $album */
                    $review->setAlbum($album);
                    $review->setTimestamp(new \DateTime());
                    $em->persist($review);
                    $em->flush();

                    $this->addFlash("success", "Thank you for your review.");
                    return $this->redirect($this->generateUrl('view_review', [
                            'id' => $review->getId()
                        ]
                    ));

                } catch (Exception $e) {
                    $this->addFlash("error", "Failed to persist your review.");
                }
            }
        }

        return $this->render('AlbumBundle:Default:addReview.html.twig', [
            "form" => $form->createView()
        ]);
    }

    /**
     * View reviews by based on album.
     *
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function viewByAlbumAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository(Review::class)
            ->getReviewsByAlbumID($id);
        $album = $em->getRepository(Album::class)
            ->find($id);

        if (!$album) {
            $this->addFlash('warning', 'There is not album that matches your request.');
            return $this->redirect($this->generateUrl('album_homepage'));
        }

        $tracks = $em->getRepository(Track::class)
            ->getTracksByAlbumID($album)
            ->getResult();

        $album->setAlbumTracks($tracks);

        /** @var Paginator $paginator */
        $paginator = $this->get('knp_paginator');
        $reviews = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), $this->getParameter('page_limit')
        );

        $totalReviews = 0;
        $totalRating = 0;
        $albumRating = 0;

        /** @var Review $review */
        foreach ($reviews as $review) {
            $totalReviews++;
            $totalRating += $review->getRating();
        }

        if ($totalReviews !== 0) {
            $albumRating = $totalRating / $totalReviews;
        }

        $similar = $this->lastFMService->getSimilar($album->getArtist(), $this->getParameter('similar_artist'));

        $similarArtists = [];
        if (array_key_exists('similarartists', $similar)) {

            if(array_key_exists('artist', $similar['similarartists'])) {

                $similarArtist = [];
                foreach ($similar['similarartists']['artist'] as $artistEntry) {

                    if (array_key_exists('name', $artistEntry)) {
                        $similarArtist['name'] = $artistEntry['name'];
                    }

                    if (array_key_exists('url', $artistEntry)) {
                        $similarArtist['url'] = $artistEntry['url'];
                    }

                    if (!empty($similarArtist)) {
                        $similarArtists[] = $similarArtist;
                    }
                }
            }
        }

        return $this->render('AlbumBundle:Default:viewByAlbum.html.twig', [
                'album' => $album,
                'reviews' => $reviews,
                'rating' => $albumRating,
                'similarArtists' => $similarArtists
            ]
        );
    }

    /**
     * View one review.
     *
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function viewReviewAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $review = $em->getRepository(Review::class)
            ->find($id);

        if (!$review) {
            $this->addFlash('warning', 'Review not found!');
            return $this->redirect($this->generateUrl('album_homepage'));
        }

        return $this->render('AlbumBundle:Default:viewReview.html.twig', [
                'review' => $review
            ]
        );
    }

    /**
     * Edit an existing review for a given album.
     *
     * @param Request $request
     * @param $id
     * @return RedirectResponseAlias|Response
     */
    public function editReviewAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $review = $em->getRepository(Review::class)
            ->find($id);

        if (!$review) {
            $this->addFlash('warning', 'Review not found!.');
            return $this->redirect($this->generateUrl('album_homepage'));
        }

        if ($review->getReviewer() !== $this->getUser() &&
            !$this->container->get('security.authorization_checker')
                ->isGranted('ROLE_ADMIN'))
        {
            $this->addFlash('error', 'You are not allowed to edit this review as you do not own it!');
            return $this->redirect($this->generateUrl('album_homepage'));
        }
        else {
            $form = $this->createForm(AddReviewFormType::class, $review, [
                'action' => $request->getUri()
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em->flush();

                $this->addFlash("success", "The review was updated.");
                return $this->redirect($this->generateUrl(
                    'view_review',
                    [
                        'id' => $review->getId()
                    ]
                ));
            }

            return $this->render('AlbumBundle:Default:edit.html.twig', [
                'form' => $form->createView(),
                'review' => $review
            ]);
        }
    }

    /**
     * Delete a review for a given album.
     *
     * @param $id
     *
     * @return RedirectResponseAlias
     */
    public function deleteReviewAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        try {
            $review = $em->getRepository(Review::class)
                ->find($id);

            if ($review->getReviewer() !== $this->getUser() &&
                !$this->container->get('security.authorization_checker')
                    ->isGranted('ROLE_ADMIN')) {
                $this->addFlash('error', 'You are not allowed to delete this review as you do not own it!');
                return $this->redirect($this->generateUrl('album_homepage'));
            } else {
                $em->remove($review);
                $em->flush();

                $this->addFlash('success', 'The review was deleted!');
                return $this->redirect($this->generateUrl('album_homepage'));
            }
        } catch (Exception $e) {
            $this->addFlash('error', 'Failed to delete review!');
            return $this->redirect($this->generateUrl('view_reviews_by_album', [
                'id' => $id
            ]));
        }
    }
}
