<?php


namespace AlbumBundle\EventListener;


use AlbumBundle\Exceptions\APIErrorException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

/**
 * To handle non-existing routes when accessed by users.
 *
 * Class ExceptionListener
 * @package AlbumBundle\EventListener
 */
class ExceptionListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => 'onKernelException'
        );
    }

    /**
     * The an error is thrown this method will be invoked.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof APIErrorException) {
            $apiProblem = $exception->getApiError();
            $response = new JsonResponse(
                $apiProblem->toArray(),
                $apiProblem->getStatusCode()
            );

            $response->headers->set('Content-Type', 'application/problem+json');

            $event->setResponse($response);

        } else {

            $message = sprintf(
                'Error: %s with code: %s',
                $exception->getMessage(),
                $exception->getCode()
            );

            $response = new Response();
            $response->setContent($message);

            if ($exception instanceof HttpExceptionInterface) {
                $response->setStatusCode($exception->getStatusCode());
                $response->headers->replace($exception->getHeaders());
            } else {
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $event->setResponse($response);
        }
    }
}