<?php


namespace AlbumBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
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
     * @Rest\Get("/clients")
     */
    public function postClientAction()
    {
        $clientManager = $this->container->get('fos_oauth_server.client_manager.default');
        $publicId = $this->getParameter('client_public_id');
        $client = $clientManager->findClientByPublicId($publicId);

        if(!$client) {
            $client = $clientManager->createClient();
            $client->setRedirectUris(['http://127.0.0.1:8000']);
            $client->setAllowedGrantTypes(['password']);
            $clientManager->updateClient($client);
        }

        return new JsonResponse(
            [
                'client_id'     => $client->getPublicId(),
                'client_secret' => $client->getSecret()
            ],
            Response::HTTP_CREATED
        );
    }
}
