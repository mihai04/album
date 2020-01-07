<?php

namespace ReviewBundle\Controller;

use AlbumBundle\Entity\Album;
use Knp\Component\Pager\Paginator;
use ReviewBundle\Entity\Review;
use ReviewBundle\Form\AddReviewFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse as RedirectResponseAlias;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UserBundle\Entity\User;

class ReviewController extends Controller
{
    /**
     * @param Request $request
     * @param int
     * @return Response
     * @throws \Exception
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
            }
            else {
                /** @var User $user */
                $user = $this->get('security.token_storage')->getToken()->getUser();
                $review->setReviewer($user);
                /** @var Album $album */
                $review->setAlbum($album);
                $review->setTitle($album->getTitle());
                $review->setTimestamp(new \DateTime());
                $em->persist($review);
                $em->flush();
            }
        }

        return $this->render('ReviewBundle:Default:index.html.twig', [
            "form" => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function viewByAlbumAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository(Review::class)
            ->getReviewsByAlbumID($id);
        $album =  $em->getRepository(Album::class)
            ->find($id);

        /** @var Paginator $paginator */
        $paginator = $this->get('knp_paginator');
        $reviews = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),5
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

        return $this->render('@Review/Default/viewByAlbum.html.twig', [
               'album' => $album,
               'reviews' => $reviews,
               'rating' => $albumRating
           ]
        );
    }

    public function editReviewAction(Request $request, $id) {
        // CHECK TRANSACTION
        // https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/transactions-and-concurrency.html
        $em = $this->getDoctrine()->getManager();
        $review = $em->getRepository(Review::class)
            ->find($id);

        if ($review->getReviewer() !== $this->getUser()
            && !$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(AddReviewFormType::class,  $review, [
            'action' => $request->getUri()
        ]);

        $form->handleRequest($request);

        if ($form->isValid() &&  $form->isSubmitted()) {
            $em->flush();
            return $this->redirect($this->generateUrl('view_reviews_by_album', [
                'id' => $review->getId()
            ]));
        }

        return $this->render('ReviewBundle:Default:edit.html.twig', [
            'form' => $form->createView(),
            'review' => $review
        ]);
    }

    /**
     * @param $id
     *
     * @return RedirectResponseAlias
     */
    public function deleteReviewAction($id) {
        $em = $this->getDoctrine()->getManager();
        $review = $em->getRepository(Review::class)
            ->find($id);

        $em->remove($review);
        $em->flush();

        // TO DO: add message + also check errors
        return $this->redirect($this->generateUrl('homepage'));
    }
}
