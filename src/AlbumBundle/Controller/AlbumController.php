<?php

namespace AlbumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
        return $this->render('AlbumBundle:Default:index.html.twig');
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function addAlbumAction(Request $request)
    {
        return $this->render('AlbumBundle:Default:index.html.twig');
    }
}
