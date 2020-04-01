<?php


namespace AlbumBundle\Controller;

use AlbumBundle\Entity\APIError;
use AlbumBundle\Entity\User;
use AlbumBundle\Exceptions\APIErrorException;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Pagerfanta\Exception\OutOfRangeCurrentPageException as OutOfRangeCurrentPageExceptionAlias;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;

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
     * List all users following a pagination system (only admins).
     *
     * @Rest\Get("/users")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the specified review.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=AlbumBundle\Entity\User::class)
     *     )
     * ),
     *
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     type="integer",
     *     description="The field represents the page number."
     * ),
     *
     * @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     type="integer",
     *     description="The field represents the limit of items per page to be returned."
     * ),
     *
     * @SWG\Response(
     *     response=400,
     *     description="Invalid data given."
     * )
     *
     * @SWG\Response(
     *     response=403,
     *     description="Fobidden action."
     * )
     *
     * @SWG\Tag(name="users"),
     *
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function getUsersAction(Request $request)
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if(!in_array('ROLE_ADMIN', $user->getRoles())) {
            return new JsonResponse([self::ERROR => 'Forbidden action you do not have admin rights.'],
                Response::HTTP_FORBIDDEN);
        }

        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository(User::class)->findAllQueryBuilder();

        try {

            $clientLimit = (int) $request->get('limit');
            $limit = $this->getParameter('albums_limit');
            if (!is_null($clientLimit) && $clientLimit != 0) {
                if (!($clientLimit > 0 && $clientLimit < 101)) {
                    return $this->handleView($this->view([self::ERROR => 'The limit parameter is out of bounds (1-100).'],
                        Response::HTTP_BAD_REQUEST));
                }
                $limit = $clientLimit;
            }

            $clientPage = (int) $request->get('page');
            if (!is_null($clientPage) && $clientPage != 0) {
                if (!($clientPage >= 0)) {
                    return $this->handleView($this->view([self::ERROR => 'The page parameter is out of bonds (<1) .'],
                        Response::HTTP_BAD_REQUEST));
                }
            }

            $paginatedCollection = $this->get('pagination_factory')->createCollection($qb, $request,
                $limit, "api_users_get_users");

        } catch (OutOfRangeCurrentPageExceptionAlias $e) {
            $apiError = new APIError(Response::HTTP_BAD_REQUEST, $e->getMessage());
            throw new APIErrorException($apiError);
        }
        return $this->handleView($this->view($paginatedCollection));
    }

    /**
     * List a user specified by client (only admins).
     *
     * @Rest\Get("/users/{id}")
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
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The field represents the id of a user."
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="User does not exist."
     * ),
     *
     * @SWG\Response(
     *     response=403,
     *     description="Fobidden action."
     * )
     *
     * @SWG\Tag(name="users")
     * @Security(name="Bearer")
     *
     * @param $id
     * @return JsonResponse|Response
     */
    public function getUserAction($id) {

        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if(!in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->handleView($this->view([self::ERROR => 'Forbidden action you do not have admin rights.'],
                Response::HTTP_FORBIDDEN));
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($id);

        // check if user exists.
        if(!$user) {
            return new JsonResponse([self::ERROR => 'User with id [' . $id .'] was not found.'],
                Response::HTTP_NOT_FOUND);
        }

        return $this->handleView($this->view($user, Response::HTTP_OK));
    }
}