<?php


namespace AlbumBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ClientCreatorController
 *
 * @package AlbumBundle\Controller
 */
class ClientCreatorController extends Controller
{
    /**
     * Retrieve a client.
     *
     * @Rest\Get("/clients")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns a client id and a client secret to be posted to /oauth/v2/token for token generation.",
     *     @SWG\Schema(
     *         @SWG\Items(
     *          @SWG\Property(type="string",property="client_id",description="client id"),
     *          @SWG\Property(type="string",property="client_secret",description="client secret"),
     *          )
     *      )
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Failed to generate client id and client secret values!"
     * )
     *
     * @SWG\Tag(name="clients")
     * @Security(name="Bearer")
     *
     * @return JsonResponse|Response
     */
    public function getClientsAction()
    {
        try {
            $clientManager = $this->container->get('fos_oauth_server.client_manager.default');
            $publicId = $this->getParameter('client_public_id');
            $client = $clientManager->findClientByPublicId($publicId);

            if (!$client) {
                $client = $clientManager->createClient();
                $client->setRedirectUris(['http://127.0.0.1:8000']);
                $client->setAllowedGrantTypes(['password']);
                $clientManager->updateClient($client);
            }

            return new JsonResponse(
                [
                    'client_id' => $client->getPublicId(),
                    'client_secret' => $client->getSecret()
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to retrieve oauth credentials.' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
