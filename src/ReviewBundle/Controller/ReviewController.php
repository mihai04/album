<?php

namespace ReviewBundle\Controller;

use ReviewBundle\Entity\Review;
use ReviewBundle\Form\AddReviewFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ReviewController extends Controller
{
    public function addReviewAction(Request $request)
    {
        $album = new Review();
        $form = $this->createForm(AddReviewFormType::class, $album);
        $form->handleRequest($request);

        return $this->render('ReviewBundle:Default:index.html.twig');
    }
}
