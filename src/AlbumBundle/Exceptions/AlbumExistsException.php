<?php


namespace AlbumBundle\Exceptions;

use Exception;
use Throwable;

class AlbumExistsException extends Exception implements CustomExceptionInterface
{
    /** @const1 string */
    const ALBUM_EXISTS_EXCEPTION = 'Album Exists';

    public function __construct($message = self::ALBUM_EXISTS_EXCEPTION, $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}