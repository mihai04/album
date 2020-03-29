<?php


namespace AlbumBundle\Exceptions;

use AlbumBundle\Entity\APIError;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class APIErrorException
 * @package AlbumBundle\Exceptions
 */
class APIErrorException extends HttpException
{
    private $apiError;

    private $statusCode;

    /**
     * APIErrorException constructor.
     * @param APIError $apiError
     * @param \Exception|null $previous
     * @param array $headers
     * @param int $code
     */
    public function __construct(APIError $apiError, \Exception $previous = null, array $headers = [], $code = 0)
    {
        $this->apiError= $apiError;
        $statusCode = $apiError->getStatusCode();
        $message = $apiError->getErrorMessage();

        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    /**
     * @return APIError
     */
    public function getApiError()
    {
        return $this->apiError;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}