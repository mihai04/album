<?php


namespace AlbumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * Class Track
 *
 * @ORM\Table(name="`track`")
 * @ORM\Entity(repositoryClass="AlbumBundle\Repository\TrackRepository")
 *
 * @package TrackBundle\Entity
 */
class Track
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="track_name", type="string", length=255, nullable=false)
     */
    private $trackName;

    /**
     * @ORM\ManyToOne(targetEntity="AlbumBundle\Entity\Album", inversedBy="albumTracks")
     * @ORM\JoinColumn(name="album", referencedColumnName="id", nullable=false)
     *
     */
    private $album;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTrackName()
    {
        return $this->trackName;
    }

    /**
     * @param string $trackName
     */
    public function setTrackName($trackName)
    {
        $this->trackName = $trackName;
    }

    /**
     * @return Album
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     *
     * @param Album $album
     */
    public function setAlbum(Album $album)
    {
        $this->album = $album;
    }
}
