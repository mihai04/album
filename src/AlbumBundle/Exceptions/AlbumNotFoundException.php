<?php


namespace AlbumBundle\Exceptions;

/**
 * Class AlbumNotFoundException
 * @package AlbumBundle\Exceptions
 */
class AlbumNotFoundException
{
    /** @const string */
    const ALBUM_EXISTS_EXCEPTION = 'Album Not Found!';

    public function __construct($message = self::ALBUM_EXISTS_EXCEPTION, $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}