<?php


namespace AlbumBundle\Controller;


class LastFMMethod
{
    const ALBUM_INFO = 'album.getinfo';
    const SEARCH_ALBUM = 'album.search';
    const TRACK_INFO = 'track.getinfo';

    private $status;

    public function setStatus($status)
    {
        if (!in_array($status, array(self::ALBUM_INFO, self::SEARCH_ALBUM))) {
            throw new \InvalidArgumentException("Invalid status");
        }
        $this->status = $status;
    }
}