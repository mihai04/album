<?php

namespace AlbumBundle\Controller;

use AlbumBundle\Entity\Album;
use AlbumBundle\Form\AddAlbumType;
use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AlbumController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $query = $entityManager->getRepository(Album::class)->getAlbums();

        /** @var Paginator $paginator */
        $paginator = $this->get('knp_paginator');
        $albums = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), 1
        );

        return $this->render('AlbumBundle:Default:index.html.twig', [
            "albums" => $albums
        ]);
    }

    /**
     * Generates a unique name of uploaded images.
     *
     * @param FormInterface $file
     * @return string
     */
    private static function hashImageName(FormInterface $file)
    {
        return md5(uniqid()).'.'.$file->getData()->guessExtension();
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function addAlbumAction(Request $request)
    {
        $album = new Album();
        $form = $this->createForm(AddAlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $file = $form->get('image');
            $fileName = $this->hashImageName($file);
        }


        return $this->render('AlbumBundle:Default:index.html.twig');
    }
}
