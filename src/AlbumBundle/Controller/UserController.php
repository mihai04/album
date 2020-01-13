<?php

namespace AlbumBundle\Controller;

use AlbumBundle\Entity\Review;
use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AlbumBundle\Entity\User;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function viewAction(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $query = $entityManager->getRepository(Review::class)
            ->getReviewsByUser($id);

        /** @var Paginator $paginator */
        $paginator = $this->get('knp_paginator');
        $reviews = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1)
        );

        $user = $this->getDoctrine()->getRepository(User::class)
            ->find($id);

        if (!$user) {
            $this->addFlash('warning', 'No user matches your request!');
            return $this->redirect($this->generateUrl('album_homepage'));
        }

        return $this->render(
            'AlbumBundle:Default:viewReviewsByUser.html.twig',
            [
                'fullName' => $user->getFullName(),
                'reviews' => $reviews
            ]
        );
    }
}
