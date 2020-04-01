<?php


namespace AlbumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
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
     * @ORM\Column(name="track_name", type="string", length=255, nullable=false)
     */
    private $trackName;

    /**
     * @var string
     *
     * @ORM\Column(name="duration", type="string", length=255, nullable=true)
     */
    private $duration;

    /**
     * @Serializer\Exclude()
     *
     * @ORM\ManyToOne(targetEntity="AlbumBundle\Entity\Album", inversedBy="albumTracks")
     * @ORM\JoinColumn(name="album", referencedColumnName="id", nullable=false, onDelete="CASCADE")
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

    /**
     * @return string
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param string $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }
}
