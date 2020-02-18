<?php


namespace AlbumBundle\Controller;

use AlbumBundle\Entity\Album;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class AlbumAPIController
 *
 * @package AlbumBundle\Controller
 */
class AlbumAPIController extends FOSRestController
{
    /**
     * @return Response
     */
    public function getAlbumspostsAction()
    {
        $em = $this->getDoctrine()->getManager();

        $albums = $em->getRepository(Album::class)
            ->findAll();

        return $this->handleView($this->view($albums));
    }

    /**
     * @param $id
     * @return Response
     */
    public function getAlbumpostsAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $album = $em->getRepository(Album::class)
            ->find($id);
        if(!$album) {
            $view = $this->view(null,  404);
        } else {
            $view = $this->view($album);
        }
        return $this->handleView($view);
    }
}