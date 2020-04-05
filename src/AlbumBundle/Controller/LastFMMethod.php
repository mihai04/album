<?php


namespace AlbumBundle\Controller;

/**
 * Class LastFMMethod
 * @package AlbumBundle\Controller
 */
abstract class LastFMMethod
{
    const ALBUM_INFO = 'album.getinfo';
    const SEARCH_ALBUM = 'album.search';
    const TRACK_INFO = 'track.getinfo';
    const CHART_TOP_TRACKS = 'chart.gettoptracks';
    const ARTIST_SIMILAR = 'artist.getSimilar';

    private $status;

    public function setStatus($status)
    {
        if (!in_array($status, array(self::ALBUM_INFO, self::SEARCH_ALBUM))) {
            throw new \InvalidArgumentException("Invalid status");
        }
        $this->status = $status;
    }
}