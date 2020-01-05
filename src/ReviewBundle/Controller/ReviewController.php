<?php

namespace ReviewBundle\Controller;

use AlbumBundle\Entity\Album;
use DateTime;
use ReviewBundle\Entity\Review;
use ReviewBundle\Form\AddReviewFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UserBundle\Entity\User;

class ReviewController extends Controller
{

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function addReviewAction(Request $request)
    {
        $review = new Review();
        $form = $this->createForm(AddReviewFormType::class, $review);
        $form->handleRequest($request);


        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $album = $em->getRepository(Album::class)->find(1);

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
                $review->setTimestamp('date');
                $em->persist($review);
                $em->flush();
            }
        }

        return $this->render('ReviewBundle:Default:index.html.twig', [
            "form" => $form->createView()
        ]);
    }
}
