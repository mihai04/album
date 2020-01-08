<?php

namespace SearchBundle\Controller;

use AlbumBundle\Entity\Album;
use Knp\Component\Pager\Paginator;
use ReviewBundle\Entity\Review;
use SearchBundle\Entity\SearchIndex;
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
        $queryResults = $this->getDoctrine()->getRepository(SearchIndex::class)
            ->getSearchResults($searchTerm);

        $users = [];
        $albums = [];
        $ratings = [];

        /** @var SearchIndex $result */
        foreach ($queryResults as $result) {
            $review = $this->getDoctrine()
                ->getRepository($result->getEntity())
                ->find($result->getForeignId());

            if($review instanceof User && !array_key_exists($review->getId(), $users)) {
                $users[$review->getId()] = $review;
            }

            if($review instanceof Album && !array_key_exists($review->getId(), $albums)) {
                $albums[$review->getId()] = $review;
                $albumReviews = $this->getDoctrine()
                    ->getRepository(Review::class)
                    ->getReviewsByAlbumID($review->getId())
                    ->getResult();

                $totalReviews = 0;
                $totalRating = 0;

                /** @var Review $review */
                foreach($albumReviews as $albumReview) {
                    $totalReviews++;
                    $totalRating += $albumReview->getRating();
                }

                if ($totalReviews !== 0) {
                    $albumRating = $totalRating / $totalReviews;
                    $ratings[] = $albumRating;
                }
            }
        }

        /** @var Paginator $paginator */
        $paginator = $this->get('knp_paginator');
        $pagedResults = $paginator->paginate(
            array_merge($users, $albums),
            $request->query->getInt('page', 1)
        );

        return $this->render(
            'SearchBundle:Default:index.html.twig',
            [
                'ratings' => $ratings,
                'results' => $pagedResults
            ]
        );
    }
}
