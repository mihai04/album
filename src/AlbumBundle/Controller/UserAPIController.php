<?php


namespace AlbumBundle\Controller;

use AlbumBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserAPIController
 *
 * @package AlbumBundle\Controller
 */
class UserAPIController extends FOSRestController
{
    /** @const string */
    const ERROR = 'error';

    /**
     * List all users.
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns all users.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\Album::class)
     *     )
     * )
     * @SWG\Tag(name="users")
     * @Security(name="Bearer")
     *
     * @return JsonResponse|Response
     */
    public function getUsersAction()
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository(User::class)
            ->findAll();

        return $this->handleView($this->view($users));
    }

    /**
     * List all users specified by client.
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the specified user.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\User::class)
     *     )
     * )
     * @SWG\Parameter(
     *     name="slug",
     *     in="path",
     *     type="string",
     *     description="The field represents the id of a user."
     * )
     * @SWG\Tag(name="users")
     * @Security(name="Bearer")
     *
     * @param $slug
     * @return JsonResponse|Response
     */
    public function getUserAction($slug) {

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($slug);

        // check if user exists.
        if(!$user) {
            return new JsonResponse([self::ERROR => 'User with id [' . $slug .'] was not found!'],
                Response::HTTP_NOT_FOUND);
        }

        return $this->handleView($this->view($user, Response::HTTP_OK));
    }
}