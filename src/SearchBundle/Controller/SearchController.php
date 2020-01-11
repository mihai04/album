<?php

namespace SearchBundle\Controller;

use AlbumBundle\Entity\Album;
use Knp\Component\Pager\Paginator;
use ReviewBundle\Entity\Review;
use SearchBundle\Entity\Indexes;
use SearchBundle\Helper\DatabaseHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use UserBundle\Entity\User;

class SearchController extends Controller
{
    /**
     * @param Request $request
     *
     * @return ResponseAlias
     */
    public function indexAction(Request $request)
    {
        $searchTerm = $request->get('search');

        if (!$searchTerm) {
            $this->addFlash('error', "Please provide an input.");
            return $this->redirect($this->generateUrl('album_homepage'));
        }

        $queryResults = $this->getDoctrine()->getRepository(Indexes::class)
            ->getResults($searchTerm);

        if (!$queryResults) {
            $this->addFlash('warning', "There are no results matching your request. Try again.");
            return $this->redirect($this->generateUrl('album_homepage'));
        }

        $users = [];
        $albums = [];
        $ratings = [];

        /** @var Indexes $result */
        foreach ($queryResults as $result) {
            $entity = $this->getDoctrine()
                ->getRepository($result->getEntity())
                ->find($result->getForeignKey());

            $entityId = $entity->getId();
            if ($entity instanceof User && !array_key_exists($entityId, $users)) {
                $users[$entityId] = $entity;
            }

            if ($entity instanceof Album && !array_key_exists($entityId, $albums)) {
                $albums[$entityId] = $entity;
                $reviews = $this->getDoctrine()
                    ->getRepository(Review::class)
                    ->getReviewsByAlbumID($entityId)
                    ->getResult();

                $totalReviews = 0;
                $totalRating = 0;

                /** @var Review $review */
                foreach ($reviews as $review) {
                    $totalReviews++;
                    $totalRating += $review->getRating();
                }

                if ($totalReviews !== 0) {
                    $albumRating = $totalRating / $totalReviews;
                    $ratings[] = $albumRating;
                }
            }
        }

        /** @var Paginator $paginator */
        $paginator = $this->get('knp_paginator');
        $results = $paginator->paginate(array_merge($users, $albums),
            $request->query->getInt('page', 1)
        );

        return $this->render('SearchBundle:Default:index.html.twig', [
                'ratings' => $ratings,
                'results' => $results
            ]
        );


    }
}
