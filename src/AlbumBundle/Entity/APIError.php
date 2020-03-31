<?php


namespace AlbumBundle\Entity;

/**
 * Class APIError
 * @package AlbumBundle\Entity
 */
class APIError
{
    private $statusCode;

    private $errorMessage;

    private $extraData = array();

    /**
     * APIError constructor.
     * @param $statusCode
     * @param $errorMessage
     */
    public function __construct($statusCode, $errorMessage)
    {
        $this->statusCode = $statusCode;
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            $this->extraData,
            array(
                'status' => $this->statusCode,
                'error' => $this->errorMessage,
            )
        );
    }

    /**
     * @param $name
     * @param $value
     */
    public function set($name, $value)
    {
        $this->extraData[$name] = $value;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}