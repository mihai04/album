<?php

namespace SearchBundle\Controller;

use AlbumBundle\Entity\Album;
use Knp\Component\Pager\Paginator;
use AlbumBundle\Entity\Review;
use SearchBundle\Entity\Indices;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use AlbumBundle\Entity\User;

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

        $queryResults = $this->getDoctrine()->getRepository(Indices::class)
            ->getResults($searchTerm);

        if (!$queryResults) {
            $this->addFlash('warning', "There are no results matching your request. Try again.");
            return $this->redirect($this->generateUrl('album_homepage'));
        }

        $users = [];
        $albums = [];
        $ratings = [];

        /** @var Indices $result */
        foreach ($queryResults as $result) {
            $entity = $this->getDoctrine()
                ->getRepository($result->getEntity())
                ->find($result->getForeignKey());

            if ($entity instanceof User && !array_key_exists($entity->getId(), $users)) {
                $users[$entity->getId()] = $entity;
            }

            if ($entity instanceof Album && !array_key_exists($entity->getId(), $albums)) {
                $albums[$entity->getId()] = $entity;
                $reviews = $this->getDoctrine()
                    ->getRepository(Review::class)
                    ->getReviewsByAlbumID($entity->getId())
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
            $request->query->getInt('page', 1), 3
        );

        return $this->render('SearchBundle:Default:index.html.twig', [
                'ratings' => $ratings,
                'results' => $results
            ]
        );
    }

//    /**
//     * @param Request $request
//     *
//     * @return ResponseAlias
//     */
//    public function indexAction(Request $request)
//    {
//        $searchTerm = $request->get('search');
//
//        if (!$searchTerm) {
//            $this->addFlash('error', "Please provide an input.");
//            return $this->redirect($this->generateUrl('album_homepage'));
//        }
//
//        $queryResults = $this->getDoctrine()->getRepository(Indices::class)
//            ->getResults($searchTerm);
//
//        if (!$queryResults) {
//            $this->addFlash('warning', "There are no results matching your request. Try again.");
//            return $this->redirect($this->generateUrl('album_homepage'));
//        }
//
//        $users = [];
//        $albums = [];
//        $ratings = [];
//
//        /** @var Indices $result */
//        foreach ($queryResults as $result) {
//            $entity = $this->getDoctrine()
//                ->getRepository($result->getEntity())
//                ->find($result->getForeignKey());
//
//            if ($entity instanceof User && !array_key_exists($entity->getId(), $users)) {
//                $users[$entity->getId()] = $entity;
//            }
//
//            if ($entity instanceof Album && !array_key_exists($entity->getId(), $albums)) {
//                $albums[$entity->getId()] = $entity;
//                $reviews = $this->getDoctrine()
//                    ->getRepository(Review::class)
//                    ->getReviewsByAlbumID($entity->getId())
//                    ->getResult();
//
//                $totalReviews = 0;
//                $totalRating = 0;
//
//                /** @var Review $review */
//                foreach ($reviews as $review) {
//                    $totalReviews++;
//                    $totalRating += $review->getRating();
//                }
//
//                if ($totalReviews !== 0) {
//                    $albumRating = $totalRating / $totalReviews;
//                    $ratings[] = $albumRating;
//                }
//            }
//        }
//
//        /** @var Paginator $paginator */
//        $paginator = $this->get('knp_paginator');
//        $results = $paginator->paginate(array_merge($users, $albums),
//            $request->query->getInt('page', 1), 3
//        );
//
//        return $this->render('SearchBundle:Default:index.html.twig', [
//                'ratings' => $ratings,
//                'results' => $results
//            ]
//        );
//    }
}
