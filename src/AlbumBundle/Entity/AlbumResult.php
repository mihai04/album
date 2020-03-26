<?php


namespace AlbumBundle\Entity;


class AlbumResult
{
    /** @var Album $album */
    private $album;

    /** @var array $tracks */
    private $tracks;

    /**
     * AlbumResult constructor.
     *
     * @param Album $album
     * @param array $tracks
     */
    public function __construct(Album $album, $tracks)
    {
        $this->album = $album;
        $this->tracks = $tracks;
    }

    /**
     * @return Album
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     * @return array
     */
    public function getTracks()
    {
        return $this->tracks;
    }
}